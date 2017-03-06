<?php
/**
 * Class App
 *
 * @package Raichu\Foundation
 */

namespace Raichu\Foundation;

/**
 * 框架应用类，继承了Container容器类
 * 用于设置项目根目录，加载config配置项，模块分发，处理请求
 * 
 * @package Raichu\Foundation
 */
class App extends Container
{
    /**
     * @var string $_basedir 项目根目录 
     */
    protected $_basedir;

    /**
     * @var string $_configdir 配置文件目录
     */
    protected $_configdir;

    /**
     * @var array $_module_enabled 启用的模块
     */
    protected $_module_enabled = [];

    /**
     * 构造函数，设置项目根目录、配置文件目录，注册自动加载方法
     *
     * @param  string $basedir 项目根目录
     * @return void
     */
    public function __construct($basedir)
    {
        $this->_basedir = $basedir;
        $this->_configdir = $this->_basedir.'/Config';

        // register autoload
        $user_loader = new Loader($basedir);
        spl_autoload_register([$user_loader, 'autoload']);

        $this->singleton('request', Request::class);
        $this->singleton('response', Response::class);
    }

    /**
     * 配置加载函数，根绝参数名读取配置文件
     *
     * @param  string $key 配置项文件名（不包含后缀）
     * @return array
     */
    public function loadConfig($key)
    {
        if (!isset($this->$key)) {
            $this->$key = include $this->_configdir.'/'.$key.'.php';
        }

        return $this->$key;
    }

    /**
     * 模块分发，根据请求地址前缀来分发到模块
     *
     * @param  string $prefix 请求地址前缀
     * @param  string $name   模块名
     * @return void
     */
    public function dispatch($prefix, $name)
    {
        $this->_module_enabled[$prefix] = ucfirst($name);
    }

    /**
     * 处理请求，加载路由文件并执行请求方法
     *
     * @return void
     */
    public function handle()
    {
        $request = $this->make('request');
        $response = $this->make('response');

        // 如果命中模块，则将项目根目录更改为模块目录
        $path = $request->getPath();
        if ($this->_module_enabled) {
            foreach ($this->_module_enabled as $prefix => $module_name) {
                if (strpos($path, $prefix) === 0) {
                    $this->_basedir .= '/Modules/'.$module_name;
                }
            }
        }

        // 加载路由
        $router = include $this->_basedir.'/router.php';
        // 处理路由
        $resolved = $router->handle($request);
        if (!$resolved) {
            $response->abort(404);
        }
        if (is_array($resolved)) {
            list($controller, $action, $parameters) = $resolved;
            if (!class_exists($controller)) {
                $response->abort(404);
            }

            $obj = new $controller($this);
            if (!method_exists($obj, $action)) {
                $response->abort(404);
            }

            call_user_func_array([$obj, $action], $parameters);
            $response->response();
        }
    }
}
