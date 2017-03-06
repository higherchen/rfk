<?php
/**
 * Class Response
 *
 * @package Raichu\Foundation
 */

namespace Raichu\Foundation;

/**
 * http响应类
 * 用于请求的返回，设置http返回码
 * 
 * @package Raichu\Foundation
 */
class Response
{
    /**
     * @var array $_ret 注册http响应数组
     */
    protected $_ret;

    /**
     * @var array $error_http_code http返回码数组
     */
    public static $error_http_code = [
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        500 => 'Internal Server Error',
        502 => 'Bad Gateway or Proxy Error',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
    ];

    /**
     * 通过设置成员变量来设置响应数据
     * 
     * @param  string $name  键值
     * @param  mix    $value 键值
     * @return void
     */
    public function __set($name, $value)
    {
        $this->_ret[$name] = $value;
    }

    /**
     * 通过成员变量来获取响应数据
     *
     * @param  string $name 键值
     * @return mixed
     */
    public function __get($name)
    {
        return $this->_ret[$name];
    }

    /**
     * 停止请求，直接返回
     *
     * @param  int    $code    返回码，如果为已经注册的http code，则写到http响应头
     * @param  string $message 返回信息
     * @param  string $format  返回格式，支持json参数
     * @return void
     */
    public function abort($code, $message = '', $format = '')
    {
        $accept_code = [404, 405, 500, 502, 503, 504];
        if (!in_array($code, $accept_code)) {
            $code = 500;
        }
        if (!$message) {
            $message = $code.' '.static::$error_http_code[$code];
        }
        if ($format == 'json') {
            $this->json(['code' => $code, 'data' => $message]);
        } else {
            header($_SERVER['SERVER_PROTOCOL'].' '.$message);
            exit($message);
        }
    }

    /**
     * 停止请求并返回json格式的数据
     *
     * @param  array $data 返回数据
     * @return void
     */
    public function json($data)
    {
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($data));
    }

    /**
     * 返回注册到$_ret的json数据
     *
     * @return void
     */
    public function response()
    {
        if ($this->_ret) {
            $this->json($this->_ret);
        }
    }
}
