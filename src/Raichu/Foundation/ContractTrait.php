<?php
/**
 * Trait ContractTrait
 *
 * @package Raichu\Foundation
 */

namespace Raichu\Foundation;

/**
 * Container容器类构造的对象，其Class应遵循此合约
 * 通过遵循此合约，可以将容器对象注入到类的成员变量中，从而注册到整个处理流中
 * 
 * @package Raichu\Foundation
 */
trait ContractTrait
{
    /**
     * @var App $_app 框架App对象
     */
    protected $_app;

    /**
     * @var array $_autoload 需要自动加载的类
     */
    protected $_autoload = [];

    /**
     * @var array $_autoload 需要以单例模式自动加载的类
     */
    protected $_singleton = [];

    /**
     * 构造函数，注入app container
     *
     * @param App $app 框架App对象
     */
    public function __construct(App $app)
    {
        $this->_app = $app;
        if ($this->_autoload) {
            foreach ($this->_autoload as $name => $class) {
                $this->_app->bind($name, $class);
            }
        }
        if ($this->_singleton) {
            foreach ($this->_singleton as $name => $class) {
                $this->_app->singleton($name, $class);
            }
        }
    }

    /**
     * 实例化自动加载的对象
     *
     * @param  string $abstract   对象别名
     * @param  array  $parameters 实例化传入的参数
     * @return object
     */
    public function make($abstract, $parameters = [])
    {
        return $this->_app->make($abstract, $parameters);
    }
}
