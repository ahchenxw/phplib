<?php
namespace com\chenxw\sql;

/**
 * PDO数据库操作-实现了数据库的常规增删改查操作
 * User: ahchenxw@126.com
 * Date: 2017/5/23
 * Time: 9:54
 */
class DB extends \PDO
{
    //操作所用的表名，继承类需配置
    private $table;
    private $field = [];
    private $where = [];
    private $data = [];
    private $group = [];
    private $order = [];
    private $limit = null;
    private $lastSql = null;

    private static $instance = [];
    //数据库配置
    public static $pdoConfig = [
        'default' => [
            'dsn' => 'mysql:host=127.0.0.1;port=3306;dbname=aso_data;charset=utf8',
            'username' => 'root',
            'password' => '123123',
            'options' => [\PDO::ATTR_PERSISTENT => false, \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8';", \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION],
        ],
    ];

    /**
     * 根据表名称，快速创建MODEL
     * @param string $table 查询表名
     * @param string $type 配置类型
     * @return DB
     * @throws \Exception
     */
    public static function create($table, $type = 'default')
    {
        if (empty(self::$pdoConfig)) {
            throw new \Exception('Config undefined: ' . $type);
        }

        if (empty(self::$instance[$type])) {
            self::$instance[$type] = new static($table, $type);
        }
        return self::$instance[$type];
    }

    /**
     * DB constructor.
     * @param string $table
     * @param string $type
     */
    public function __construct($table, $type = 'default')
    {
        $this->table = $table;
        $dsn = self::$pdoConfig[$type]['dsn'];
        $username = self::$pdoConfig[$type]['username'];
        $password = self::$pdoConfig[$type]['password'];
        $options = self::$pdoConfig[$type]['options'];
        parent::__construct($dsn, $username, $password, $options);
    }

    /**
     * 向表中添加数据
     * @param array $data
     * @return bool|string
     */
    public function add($data = null)
    {
        if ($data !== null) {
            $this->data($data);
        }

        $data = $this->_data();
        $query = <<<QUERY
INSERT INTO `$this->table` SET $data;
QUERY;
        $this->_reset();
        $stmt = $this->prepare($query);
        if ($stmt->execute() && $stmt->rowCount()) {
            $this->lastSql = $query;
            return $this->lastInsertId();
        }
        return false;
    }

    /**
     * 查询数据
     * @return array|bool
     */
    public function select()
    {
        //生成filed
        $field = $this->_field();
        $query = <<<QUERY
SELECT $field FROM `$this->table`
QUERY;

        //生成where
        $where = $this->_where();
        if ($where) $query .= ' WHERE ' . $where;

        //生成group by
        $group = $this->_group();
        if ($group) $query .= ' GROUP BY ' . $group;

        //生成order
        $order = $this->_order();
        if ($order) $query .= ' ORDER BY ' . $order;

        //生成limit
        if ($this->limit) {
            $query .= ' LIMIT ' . $this->limit;
        }

        $this->_reset();
        $stmt = $this->prepare($query);
        if ($stmt->execute() && $stmt->rowCount()) {
            $this->lastSql = $query;
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        return false;
    }

    /**
     * 查询一条数据
     * @return array|bool
     */
    public function find()
    {
        //生成filed
        $field = $this->_field();
        $query = <<<QUERY
SELECT $field FROM `$this->table`
QUERY;

        //生成where
        $where = $this->_where();
        if ($where) $query .= ' WHERE ' . $where;

        //生成group by
        $group = $this->_group();
        if ($group) $query .= ' GROUP BY ' . $group;

        //生成order
        $order = $this->_order();
        if ($order) $query .= ' ORDER BY ' . $order;

        //生成limit
        if ($this->limit) {
            $query .= ' LIMIT 0,1';
        }

        $this->_reset();
        $stmt = $this->prepare($query);
        if ($stmt->execute() && $stmt->rowCount()) {
            $this->lastSql = $query;
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        }
        return false;
    }

    /**
     * 更新数据
     * @param array|null $data
     * @return bool|int
     */
    public function save($data = null)
    {
        if ($data !== null) {
            $this->data = $data;
        }

        //生成data
        $data = $this->_data();
        $query = <<<QUERY
UPDATE `$this->table` SET $data
QUERY;

        //生成where数据
        $where = $this->_where();
        if ($where) {
            $query .= ' WHERE ' . $where;
        }

        $this->_reset();
        $stmt = $this->prepare($query);
        if ($stmt->execute() && $stmt->rowCount()) {
            $this->lastSql = $query;
            return $stmt->rowCount();
        }
        return false;
    }

    /**
     * 删除数据
     * @param bool $all 没有配置where，请确定是否，删除全部数据
     * @return bool|int
     * @throws \Exception
     */
    public function delete($all = false)
    {
        $query = <<<QUERY
DELETE FROM `$this->table`
QUERY;

        //生成where数据
        $where = $this->_where();
        if ($where) {
            $query .= ' WHERE ' . $where;
        } else if (!$all) {
            throw new \Exception('Can not delete all data');
        }

        $this->_reset();
        $stmt = $this->prepare($query);
        if ($stmt->execute() && $stmt->rowCount()) {
            $this->lastSql = $query;
            return $stmt->rowCount();
        }
        return false;
    }

    /**
     * 执行一条SQL语句
     * @param string $sql
     * @return array|bool|int|string
     */
    public function query($sql)
    {
        $this->_reset();
        $stmt = $this->prepare($sql);
        if ($stmt->execute() && $stmt->rowCount()) {
            $this->lastSql = $sql;
            if (preg_match('/^INSERT.+$/i', $sql)) { //添加
                return $this->lastInsertId();
            } else if (preg_match('/^SELECT.+$/i', $sql)) { //查找
                return $stmt->fetchAll(\PDO::FETCH_ASSOC);
            } else { //更新和删除
                return $stmt->rowCount();
            }
        }
        return false;
    }

    /**
     * 获取最后一次执行的SQL
     * @return string|null
     */
    public function getLastSql()
    {
        return $this->lastSql;
    }

    /**
     * 设置查询字段
     * @param $field
     * @return $this
     */
    public function field($field)
    {
        if (is_array($field)) {
            $this->field = array_merge($this->field, $field);
        } else {
            $this->field[] = $field;
        }
        return $this;
    }

    /**
     * 解析查询字段
     * @return string
     */
    protected function _field()
    {
        if (empty($this->field)) {
            $field = '*';
        } else {
            $field = [];
            foreach ($this->field as $key => $val) {
                $field[] = is_numeric($key) ? "`$val`" : "`$key` AS `$val`";
            }
            $field = implode(',', $field);
        }
        return $field;
    }

    /**
     * 添加查询条件
     * @param $where
     * @return $this
     */
    public function where($where)
    {
        if (is_array($where)) {
            $this->where = array_merge($this->where, $where);
        } else {
            $this->where[] = $where;
        }
        return $this;
    }

    /**
     * 解析where表达式
     * @return bool|string
     * @throws \Exception
     */
    private function _where()
    {
        if (empty($this->where)) {
            return false;
        }

        function exp2arr($key, $data)
        {
            //$key是数字
            if (is_numeric($key)) {
                return [$data];
            }
            //$data不是数组
            if (!is_array($data)) {
                return ["`$key` = ('" . str_replace("'", "\\'", $data) . "')"];
            }

            //数组中第一条数据是字符串，即表达式
            if (is_string($data[0])) {
                //表达式
                $convert = ['EQ' => '=', 'NEQ' => '<>', 'GT' => '>', 'EGT' => '>=', 'LT' => '<', 'ELT' => '<='];
                $exp = strtoupper($data[0]);
                $exp = isset($convert[$exp]) ? $convert[$exp] : $exp;

                $res = [];
                switch ($exp) {
                    case 'BETWEEN':
                    case 'NOT BETWEEN':
                        $res[] = "`$key` $exp '" . str_replace("'", "\\'", $data[1][0]) . "' AND '" . str_replace("'", "\\'", $data[1][1]) . "'";
                        break;
                    default:
                        //数组中第二条数据是数组
                        if (is_array($data[1])) {
                            array_walk($data[1], function (&$val) {
                                $val = "'" . str_replace("'", "\\'", $val) . "'";
                            });
                            $d1 = implode(',', $data[1]);
                        } else {
                            $d1 = "'" . str_replace("'", "\\'", $data[1]) . "'";
                        }
                        $res[] = "`$key` $exp ($d1)";
                        break;
                }
                return $res;
            }

            //数组中第一条数据是数组
            if (is_array($data[0])) {
                $res = [];
                foreach ($data as $kk => $dd) {
                    if ($kk === '_logic') {
                        $res['_logic'] = strtoupper($dd);
                    } else {
                        $res = array_merge($res, exp2arr($key, $dd));
                    }
                }
                return $res;
            }

            throw new \Exception('Parse Where Fail');
        }

        //将条件转换成字符串数组
        $where = [];
        foreach ($this->where as $key => $data) {
            if ($key === '_logic') {
                $where[$key] = strtoupper($data);
                continue;
            }
            $where[] = exp2arr($key, $data);
        }

        //合成所有条件
        $arr = [];
        $logic1 = isset($where['_logic']) ? $where['_logic'] : 'AND';
        unset($where['_logic']);
        foreach ($where as $key => $data) {
            $logic2 = isset($data['_logic']) ? $data['_logic'] : 'AND';
            unset($data['_logic']);
            $arr[] = '(' . implode(" $logic2 ", $data) . ')';
        }

        return implode(" $logic1 ", $arr);
    }

    /**
     * 添加data数据
     * @param $data
     * @return $this
     */
    public function data($data)
    {
        if (is_array($data)) {
            $this->data = array_merge($this->data, $data);
        } else {
            $this->data[] = $data;
        }
        return $this;
    }

    /**
     * 解析data数据
     * @return string
     * @throws \Exception
     */
    private function _data()
    {
        if (empty($this->data)) {
            throw new \Exception('Parse Data Fail');
        }
        $data = $this->data;
        array_walk($data, function (&$val, $key) {
            if (!is_numeric($key)) {
                $val = "`$key`='" . str_replace("'", "\\'", $val) . "'";
            }
        });
        return implode(',', $data);
    }

    /**
     * 添加group数据
     * @param $group
     * @return $this
     */
    public function group($group)
    {
        if (is_array($group)) {
            $this->group = array_merge($this->group, $group);
        } else {
            $this->group[] = $group;
        }
        return $this;
    }

    /**
     * 解析group数据
     * @return bool|string
     */
    private function _group()
    {
        if (empty($this->group)) {
            return false;
        }
        $group = $this->group;
        array_walk($group, function (&$val) {
            $val = "`$val`";
        });
        return implode(',', $group);
    }

    /**
     * 添加order数据
     * @param $order
     * @return $this
     */
    public function order($order)
    {
        if (is_array($order)) {
            $this->order = array_merge($this->order, $order);
        } else {
            $this->order[] = $order;
        }
        return $this;
    }

    /**
     * 解析order数据
     * @return bool|string
     */
    private function _order()
    {
        if (empty($this->order)) {
            return false;
        }
        $order = $this->order;
        array_walk($order, function (&$val, $key) {
            $val = is_numeric($key) ? "`$val` ASC" : "`$key` " . strtoupper($val);
        });
        return implode(',', $order);
    }

    /**
     * 添加limit数据
     * @param $offset
     * @param $length
     * @return $this
     */
    public function limit($offset, $length)
    {
        $this->limit = $offset . ',' . $length;
        return $this;
    }

    /**
     * 充值所有设置的数据
     */
    private function _reset()
    {
        $this->field = [];
        $this->where = [];
        $this->data = [];
        $this->group = [];
        $this->order = [];
        $this->limit = null;
    }

}