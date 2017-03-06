<?php
/**
 * Class Loader
 *
 * @package Raichu\Foundation
 */

namespace Raichu\Foundation;

/**
 * 业务代码自动加载类
 * 通过命名空间来查找目录
 *
 * @package Raichu\Foundation
 */
class Loader
{
    /**
     * @var array $loaded 注册已经加载好的类
     */
    protected static $loaded = [];

    /**
     * @var string $_basedir 注册文件查找的根目录
     */
    protected $_basedir;

    /**
     * 构造函数，设置文件查找的根目录
     * 
     * @param  string $basedir 根目录
     * @return void
     */
    public function __construct($basedir = '.')
    {
        $this->_basedir = rtrim($basedir, '/');
    }

    /**
     * 自动加载方法，用于spl_autoload_register
     *
     * @param  string $classname 类名
     * @return void
     */
    public function autoload($classname)
    {
        if (isset(static::$loaded[$classname])) {
            return true;
        }

        $basedir = $this->_basedir;

        // Load class in module
        $classname = trim($classname, '\\');
        $block = explode('\\', $classname);
        if (!in_array($block[0], ['Controllers', 'Models', 'Services'])) {
            $basedir .= '/Modules';
        }

        $block = explode('\\', $classname);
        $truename = array_pop($block);
        if ($block) {
            $basedir .= '/'.implode('/', $block);
        }
        $class_file = $basedir.'/'.$truename.'.php';

        if (file_exists($class_file)) {
            static::$loaded[$classname] = true;
            include $class_file;
        }
    }
}