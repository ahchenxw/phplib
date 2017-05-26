<?php
namespace com\chenxw\util;

/**
 * 向浏览器控制台输出日志
 * User: ahchenxw@126.com
 * Date: 2017/5/23
 * Time: 9:54
 */
class Console
{
    /**
     * 普通类日志输出
     */
    public static function log()
    {
        $num = func_num_args();
        for ($i = 0; $i < $num; $i++) {
            $data = func_get_arg($i);
            if (is_array($data) || is_object($data)) {
                $data = json_encode($data);
            }
            $data = str_replace("'", "\\'", $data);
            echo("<script>console.log('{$data}');</script>");
        }
    }

    /**
     * 消息类日志输出
     */
    public static function info()
    {
        $num = func_num_args();
        for ($i = 0; $i < $num; $i++) {
            $data = func_get_arg($i);
            if (is_array($data) || is_object($data)) {
                $data = json_encode($data);
            }
            $data = str_replace("'", "\\'", $data);
            echo("<script>console.info('{$data}');</script>");
        }
    }

    /**
     * 错误类日志输出
     */
    public static function error()
    {
        $num = func_num_args();
        for ($i = 0; $i < $num; $i++) {
            $data = func_get_arg($i);
            if (is_array($data) || is_object($data)) {
                $data = json_encode($data);
            }
            $data = str_replace("'", "\\'", $data);
            echo("<script>console.error('{$data}');</script>");
        }
    }

    /**
     * 警告类日志输出
     */
    public static function warn()
    {
        $num = func_num_args();
        for ($i = 0; $i < $num; $i++) {
            $data = func_get_arg($i);
            if (is_array($data) || is_object($data)) {
                $data = json_encode($data);
            }
            $data = str_replace("'", "\\'", $data);
            echo("<script>console.warn('{$data}');</script>");
        }
    }
}