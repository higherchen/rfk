<?php
/**
 * Class Memcache
 *
 * @package Raichu\Tools
 */

namespace Raichu\Tools;

use Raichu\Foundation\App;

/**
 * Memcache类
 * 封装了memcache相关操作
 * 
 * @package Raichu\Tools
 */
class Memcache
{
    /**
     * @var \MemCached|\Memcache $_conn memcache连接对象
     */
    protected $_conn;

    /**
     * 构造函数，连接memcache server
     *
     * @param  Raichu\Foundation\App $app 应用App对象，用于获取配置项
     * @return void
     */
    public function __construct(App $app)
    {
        if (class_exists(\Memcached::class)) {
            // memcached first
            $conn = new \Memcached;
        } elseif (class_exists(\Memcache::class)) {
            $conn = new \Memcache;
        } else {
            return false;
        }

        $config = $app->loadConfig('memcache');
        $conn->addServer($config['host'], $config['port']);
        $this->_conn = $conn;
    }

    /**
     * 执行Memcache(d)类的方法
     *
     * @param  string $method     方法名
     * @param  array  $parameters 参数
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->_conn, $method], $parameters);
    }
}
