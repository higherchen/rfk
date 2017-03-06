<?php
/**
 * Class Request
 *
 * @package Raichu\Foundation
 */

namespace Raichu\Foundation;

/**
 * http请求类
 * 用于获取请求头，request path，request method
 * 
 * @package Raichu\Foundation
 */
class Request
{
    /**
     * @var array $headers http请求头
     */
    protected $headers;

    /**
     * @var string $path 请求路径
     */
    protected $path;

    /**
     * @var string $method 请求方法
     */
    protected $method;

    /**
     * 获取http请求头
     *
     * @return array
     */
    public function getHeader()
    {
        if ($this->headers === null) {
            if (function_exists('getallheaders')) {
                $this->headers = getallheaders();
            } else {
                foreach ($_SERVER as $name => $value) {
                    if ((substr($name, 0, 5) == 'HTTP_') || ($name == 'CONTENT_TYPE') || ($name == 'CONTENT_LENGTH')) {
                        $headers[str_replace(array(' ', 'Http'), array('-', 'HTTP'), ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                    }
                }
                $this->headers = $headers;
            }
        }
        return $this->headers;
    }

    /**
     * 获取请求路径，用于路由模块
     *
     * @return string
     */
    public function getPath()
    {
        if ($this->path === null) {
            $basepath = implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1)).'/';
            $path = substr($_SERVER['REQUEST_URI'], strlen($basepath));

            // Don't take query params on the path
            if (strstr($path, '?')) {
                $path = substr($path, 0, strpos($path, '?'));
            }

            $this->path = '/'.trim($path, '/');
        }

        return $this->path;
    }

    /**
     * 获取请求方法
     *
     * @return string
     */
    public function getMethod()
    {
        if ($this->method === null) {
            $method = $_SERVER['REQUEST_METHOD'];
            if ($method == 'HEAD') {
                ob_start();
                $method = 'GET';
            }
            if ($method == 'POST') {
                $headers = $this->getHeader();
                isset($headers['X-HTTP-Method-Override']) && 
                in_array($headers['X-HTTP-Method-Override'], ['PUT', 'DELETE', 'PATCH']) && 
                $method = $headers['X-HTTP-Method-Override'];
            }
            $this->method = $method;
        }

        return $this->method;
    }
}
