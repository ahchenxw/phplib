<?php
namespace com\chenxw\util;

/**
 * Redis操作封装
 * User: ahchenxw@126.com
 * Date: 2017/5/23
 * Time: 9:54
 */
class Redis extends \Redis
{
    //连接配置
    private static $config = [
        'default' => [
            'host' => '127.0.0.1',
            'port' => 6379,
            'timeout' => 2.5,
        ],
    ];
    private static $instance;

    /**
     * 获取一个实例
     * @param string $type 配置类型
     * @return static
     */
    public static function create($type = 'default')
    {
        if (empty(self::$instance[$type])) {
            $config = self::$config[$type];
            $host = $config['host'];
            $port = isset($config['port']) ? intval($config['port']) : 6379;
            $timeout = isset($config['timeout']) ? floatval($config['timeout']) : 0.0;

            $redis = new static();
            $redis->connect($host, $port, $timeout);
            if (isset($config['password'])) {
                $redis->auth($config['password']);
            }
            self::$instance[$type] = $redis;
        }
        return self::$instance[$type];
    }

    /**
     * @param $key
     * @param $value
     * @param int $timeout
     * @return bool
     */
    public function set($key, $value, $timeout = 0)
    {
        $value = json_encode([$value]);
        if ($timeout > 0) {
            return parent::setex($key, $timeout, $value);
        }
        return parent::set($key, $value);
    }

    /**
     * @param $key
     * @return bool|string|array
     */
    public function get($key)
    {
        $value = parent::get($key);
        $value = json_decode($value, true);
        return ($value === null) ? false : $value[0];
    }

    /**
     * @param $key
     * @param $hashKey
     * @param $value
     * @param int $timeout
     * @return int
     */
    public function hSet($key, $hashKey, $value, $timeout = 0)
    {
        $value = json_encode([$value]);
        $res = parent::hSet($key, $hashKey, $value);
        if ($timeout > 0) {
            parent::setTimeout($key, $timeout);
        }
        return $res;
    }

    /**
     * @param $key
     * @param $hashKey
     * @return bool|string|array
     */
    public function hGet($key, $hashKey)
    {
        $value = parent::hGet($key, $hashKey);
        $value = json_decode($value, true);
        return ($value === null) ? false : $value[0];
    }

    /**
     * @param $key
     * @param $start
     * @param $end
     * @return array
     */
    public function lRange($key, $start, $end)
    {
        $values = parent::lRange($key, $start, $end);
        foreach ($values as &$value) {
            $decode = json_decode($value);
            $value = ($decode === null) ? false : $decode[0];
        }
        return $values;
    }

    /**
     * @param $key
     * @param $value
     * @param int $timeout
     * @return int
     */
    public function lPush($key, $value, $timeout = 0)
    {
        $value = json_encode([$value]);
        if ($timeout > 0) {
            parent::setTimeout($key, $timeout);
        }
        return parent::lPush($key, $value);
    }

    /**
     * @param $key
     * @param $value
     * @param int $timeout
     * @return int
     */
    public function rPush($key, $value, $timeout = 0)
    {
        $value = json_encode([$value]);
        if ($timeout > 0) {
            parent::setTimeout($key, $timeout);
        }
        return parent::rPush($key, $value);
    }

    /**
     * @param $key
     * @return bool
     */
    public function lPop($key)
    {
        $value = parent::lPop($key);
        $value = json_decode($value, true);
        return ($value === null) ? false : $value[0];
    }

    /**
     * @param $key
     * @return bool
     */
    public function rPop($key)
    {
        $value = parent::rPop($key);
        $value = json_decode($value, true);
        return ($value === null) ? false : $value[0];
    }
}
