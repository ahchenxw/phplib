<?php
namespace com\chenxw\util;

/**
 * 写日志文件
 * User: ahchenxw@126.com
 * Date: 2017/5/23
 * Time: 9:54
 */
class Log
{
    // 日志级别 从上到下，由低到高
    const EMERG = 'EMERG';  // 严重错误: 导致系统崩溃无法使用
    const ALERT = 'ALERT';  // 警戒性错误: 必须被立即修改的错误
    const ERR = 'ERR';  // 一般错误: 一般性错误
    const WARN = 'WARN';  // 警告性错误: 需要发出警告的错误
    const NOTICE = 'NOTIC';  // 通知: 程序可以运行但是还不够完美的错误
    const INFO = 'INFO';  // 信息: 程序输出信息
    const DEBUG = 'DEBUG';  // 调试: 调试信息
    const SQL = 'SQL';  // SQL：SQL语句 注意只在调试模式开启时有效

    const LOG_MAX_SIZE = 2097152;//log文件最大2M

    private static $instance = [];
    private static $log = [];
    private static $logPath = null;
    private $pathType;

    /**
     * 配置日志存放路径
     * @return array
     */
    private static function configLogPath()
    {
        return [
            'default' => dirname(__FILE__) . '/log_' . date('Ymd') . '.log',
        ];
    }

    /**
     * 获取单例
     * @param string $pathType
     * @return Log
     * @throws \Exception
     */
    public static function instance($pathType = 'default')
    {
        if (empty(self::$instance[$pathType])) {
            //初始化所有日志路径
            if (self::$logPath == null) {
                self::$logPath = self::configLogPath();
            }
            self::$instance[$pathType] = new static($pathType);
        }
        if (empty(self::$logPath[$pathType])) {
            throw new \Exception('未定义路径');
        }
        return self::$instance[$pathType];
    }

    /**
     * Log constructor.
     * @param $pathType
     */
    private function __construct($pathType)
    {
        $this->pathType = $pathType;
    }

    /**
     * 记录日志到缓存中，请使用save()保存
     * @param $message
     * @param string $level
     * @return $this
     */
    public function record($message, $level = self::ERR)
    {
        $time = date('d-M-Y H:i:s') . ' ' . date_default_timezone_get();
        self::$log[] = "[$time] [$level] $message" . PHP_EOL;
        return $this;
    }

    /**
     * 保存到日志文件中
     * @return bool
     */
    public function save()
    {
        $res = false;
        if (is_array(self::$log) && count(self::$log) > 0) {
            $filename = self::$logPath[$this->pathType];

            //文件过大生成一个新文件
            if (file_exists($filename)) {
                $size = filesize($filename);
                if ($size > self::LOG_MAX_SIZE) {
                    $new = dirname($filename) . '/' . date('YmdHis') . '-' . basename($filename);
                    rename($filename, $new);
                }
            }
            $res = file_put_contents($filename, implode('', self::$log), FILE_APPEND | LOCK_EX) != false;
        }
        self::$log = [];
        return $res;
    }

    /**
     * 直接写日志
     * @param $message
     * @param string $level
     * @return $this
     */
    public function write($message, $level = self::ERR)
    {
        return $this->record($message, $level)->save();
    }
}