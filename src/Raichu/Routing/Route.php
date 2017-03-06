<?php
/**
 * Class Route
 *
 * @package Raichu\Routing
 */

namespace Raichu\Routing;

/**
 * 正则路由类
 * 用于设置正则路由，并提供匹配方法
 * 
 * @package Raichu\Routing
 */
class Route
{
    /**
     * @var array $_options 路由基础数据
     */
    protected $_options = [
        'alias' => false,
        'pattern' => '',
        'methods' => [],
        'use' => null,
        'middleware' => [],
    ];

    /**
     * 构造函数，设置regex pattern，使用的方法
     *
     * @param  string          $pattern 正则
     * @param  \Closure|string $use     处理方法
     * @return void
     */
    public function __construct($pattern, $use)
    {
        $this->_options['pattern'] = $pattern;
        $this->_options['use'] = $use;
    }

    /**
     * 给路由设置别名
     *
     * @param  string $name 别名
     * @return Route
     */
    public function alias($name)
    {
        $this->_options['alias'] = $name;

        return $this;
    }

    /**
     * 给路由设置请求方法限制
     *
     * @param  array $methods 请求方法，e.g ['get', 'post']
     * @return Route
     */
    public function method($methods)
    {
        $this->_options['methods'] = array_map('strtoupper', $methods);

        return $this;
    }

    /**
     * 给路由设置middleware
     *
     * @param  \Closure|string $concrete 匿名函数或者类和方法集合
     * @return Route
     */
    public function middleware($concrete)
    {
        $this->_options['middleware'] = $concrete;

        return $this;
    }

    /**
     * 检查路由是否匹配
     *
     * @param  \Raichu\Foundation\Request $request 请求对象
     * @return bool|array
     */
    public function match($request)
    {
        $route = $this->_options;

        $pattern = $route['pattern'];
        $path = $request->getPath();
        $method = $request->getMethod();

        if ($route['methods'] && !in_array($method, $route['methods'])) {
            return false;
        }

        if (preg_match_all('#^'.$pattern.'$#', $path, $matches, PREG_OFFSET_CAPTURE)) {
            $matches = array_slice($matches, 1);
            $params = array_map(
                function ($match, $index) use ($matches) {
                    $next = $index + 1;
                    if (isset($matches[$next]) && isset($matches[$next][0]) && is_array($matches[$next][0])) {
                        return trim(substr($match[0][0], 0, $matches[$next][0][1] - $match[0][1]), '/');
                    } else {
                        return isset($match[0][0]) ? trim($match[0][0], '/') : null;
                    }
                }, $matches, array_keys($matches)
            );

            // handle middleware
            try {
                if ($route['middleware']) {
                    $middleware = $route['middleware'];
                    if (is_string($middleware)) {
                        if (strpos($middleware, '@')) {
                            list($classname, $action) = explode('@', $middleware);
                            (new $classname)->$action($params);
                        }
                        if (strpos($middleware, '::')) {
                            list($classname, $action) = explode('::', $middleware);
                            $classname::$action($params);
                        }
                    }
                    if ($middleware instanceof \Closure) {
                        call_user_func_array($middleware, $params);
                    }
                }
            } catch (\Exception $e) {
                // Log error
            }

            // handle user func
            if ($route['use'] instanceof \Closure) {
                call_user_func_array($route['use'], $params);
                return true;
            }

            if (is_string($route['use']) && strpos($route['use'], '@')) {
                $use = explode('@', $route['use']);
                return [$use[0], $use[1], $params];
            }
        }

        return false;
    }
}