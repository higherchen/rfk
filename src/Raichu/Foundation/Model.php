<?php
/**
 * Abstract Class Model
 *
 * @package Raichu\Foundation
 */

namespace Raichu\Foundation;

/**
 * 模型抽象类
 * 封装了数据库连接以及一些简单的查询方法
 * 
 * @package Raichu\Foundation
 */
abstract class Model
{
    /**
     * @var array $_db 数据库连接对象数组
     */
    protected static $_db;

    /**
     * @var string $_database 数据库配置名称，用于获取连接配置及属性
     */
    protected $_database = 'default';

    /**
     * @var string $_table 当前类访问的表名
     */
    protected $_table = '';

    /**
     * @var string $_pk 当前类访问的表主键字段
     */
    protected $_pk = 'id';

    /**
     * @var array $_prepared 当前类生成的PDOStatement对象数组
     */
    protected $_prepared = [];

    /**
     * @var string 新增数据的SQL常量
     */
    const INSERT_SQL = '';

    /**
     * @var string 更新数据的SQL常量
     */
    const UPDATE_SQL = '';

    /**
     * 构造函数，数据库连接，设置表名
     *
     * @param  App $app 注入框架App对象
     * @return void
     */
    public function __construct(App $app)
    {
        if (!isset(static::$_db[$this->_database])) {
            $config = $app->loadConfig('database');
            $db_config = $config[$this->_database];
            static::$_db[$this->_database] = new \PDO(
                $db_config['connection_string'],
                $db_config['username'],
                $db_config['password'],
                $db_config['driver_options']
            );
        }
        if (!$this->_table) {
            $ref = new \ReflectionClass(get_class($this));
            $name = $ref->getShortName();
            $this->_table = lcfirst(str_replace('Model', '', $name));
        }
    }

    /**
     * 新增数据
     *
     * @param  array $data 数据参数
     * @return int|void
     */
    public function add($data)
    {
        if (static::INSERT_SQL) {
            $stmt = $this->getStatement(static::INSERT_SQL);
            $stmt->execute($data);
            $count = $stmt->rowCount();

            return $count ? $this->lastInsertId() : $count;
        }
    }

    /**
     * 更新数据
     *
     * @param  array $data 数据参数
     * @return int|void
     */
    public function update($data)
    {
        if (static::UPDATE_SQL) {
            $stmt = $this->getStatement(static::UPDATE_SQL);
            $stmt->execute($data);

            return $stmt->rowCount();
        }
    }

    /**
     * 根据主键ID查找数据
     *
     * @param  int $id 主键ID
     * @return array
     */
    public function find_one($id)
    {
        $sql = "SELECT * FROM {$this->_table} WHERE {$this->_pk}=?";
        $stmt = $this->getStatement($sql);
        $stmt->execute([$id]);

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * 根据主键ID删除数据
     *
     * @param  int $id 主键ID
     * @return int
     */
    public function remove($id)
    {
        $sql = "DELETE FROM {$this->_table} WHERE {$this->_pk}=?";
        $stmt = $this->getStatement($sql);
        $stmt->execute([$id]);

        return $stmt->rowCount();
    }

    /**
     * 获取PDOStatement对象
     *
     * @param  string $sql 需要Prepare的SQL语句
     * @return \PDOStatement
     */
    public function getStatement($sql)
    {
        $mark = md5($sql);
        if (!isset($this->_prepared[$mark])) {
            $this->_prepared[$mark] = $this->prepare($sql);
        }

        return $this->_prepared[$mark];
    }

    /**
     * 执行PDO类的方法
     *
     * @param  string $method    方法名
     * @param  array  $arguments 参数
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        if (static::$_db[$this->_database] && method_exists(static::$_db[$this->_database], $method)) {
            return call_user_func_array([static::$_db[$this->_database], $method], $arguments);
        }

        return false;
    }
}
