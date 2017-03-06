<?php
/**
 * Class Router
 *
 * @package Raichu\Routing
 */

namespace Raichu\Routing;

/**
 * 路由类
 * 实现了自动路由以及正则路由
 * 
 * @package Raichu\Routing
 */
class Router
{
    /**
     * @var string $_prefix 路由前缀
     */
    protected $_prefix;

    /**
     * @var \Closure $_rule 自动路由处理方法
     */
    protected $_rule;

    /**
     * @var array $routes 正则路由对象数组
     */
    protected static $routes = array();

    /**
     * @var string $_module 所在的模块名
     */
    protected $_module = '';

    /**
     * 设置一个全局的前缀
     *
     * @param string $prefix URL前缀
     * @param string $module 设置模块名
     */
    public function prefix($prefix, $module = '')
    {
        $this->_prefix = $prefix;
        if ($module) {
            $this->_module = ucfirst($module);
        }
    }

    /**
     * 设置正则路由
     *
     * @param  string          $pattern 正则表达式
     * @param  \Closure|string $use     匿名方法或者类的处理方法
     * @return Route
     */
    public function match($pattern, $use)
    {
        if ($this->_prefix) {
            $pattern = $this->_prefix.$pattern;
        }
        
        $use = is_string($use) ? $this->_module.'\\Controllers\\'.$use : $use;

        $route = new Route($pattern, $use);
        static::$routes[] = $route;

        return $route;
    }

    /**
     * 设置自动路由处理方法
     *
     * @param  \Closure $rule 匿名函数，参数为\Raichu\Foundation\Request对象
     * @return Route
     */
    public function setDefault($rule)
    {
        $this->_rule = $rule;

        return $this;
    }

    /**
     * 路由处理
     *
     * @param  \Raichu\Foundation\Request $request http请求对象
     * @return bool|array
     */
    public function handle($request)
    {
        // regex router
        if (static::$routes) {
            foreach (static::$routes as $route) {
                if ($resolved = $route->match($request)) {
                    return $resolved;
                }
            }
        }

        // default router ( e.g. /:prefix?/:controller_name/:id? )
        $prefix = $this->_prefix;
        $rule = $this->_rule;

        if ($rule === null) {
            $rule = function ($request) use ($prefix) {
                $path = $request->getPath();
                if ($prefix && strpos($path, $prefix) === 0) {
                    $path = substr($path, strlen($prefix));
                }

                $path = trim($path, '/');
                $block = $path ? explode('/', trim($path, '/')) : [];

                $name = $action = 'index';
                $parameters = [];
                $count = count($block);
                if ($count == 1) {
                    $name = $block[0];
                }
                if ($count > 1) {
                    list($name, $action) = $block;
                    if (is_numeric($action)) {
                        $parameters[] = $action;
                        $action = 'handle';
                    }
                }
                $directory = $this->_module."\\Controllers\\";

                return array($directory.ucfirst($name).'Controller', $action, $parameters);
            };
        }
        if (is_callable($rule)) {
            $resolved = call_user_func($rule, $request);
            return $resolved;
        }

        return false;
    }
}
