<?php
/**
 * Class Redis
 *
 * @package Raichu\Tools
 */

namespace Raichu\Tools;

use Raichu\Foundation\App;

/**
 * Redis类
 * 封装了redis相关操作
 * 
 * @package Raichu\Tools
 */
class Redis
{
    /**
     * @var \Redis $_conn redis对象
     */
    protected $_conn;

    /**
     * 构造函数，连接redis server
     *
     * @param  Raichu\Foundation\App $app 应用App对象，用于获取配置项
     * @return void
     */
    public function __construct(App $app)
    {
        if (!class_exists(\Redis::class)) {
            return false;
        }

        $redis = new \Redis();
        $config = $app->loadConfig('redis');
        $redis->connect($config['host'], $config['port']);

        $this->_conn = $redis;
    }

    /**
     * 执行Redis类的方法
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
