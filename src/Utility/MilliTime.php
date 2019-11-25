<?php

namespace Codeages\Biz\Framework\Utility;

class MilliTime
{
    /**
     * 获取当前时间戳(毫秒)
     *
     * @return int
     */
    public static function now()
    {
        return intval(microtime(true) * 1000);
    }

    /**
     * 格式化显示毫秒时间戳
     *
     * @param $format
     * @param null $timestamp
     * @return string
     */
    public static function format($format, $timestamp = null)
    {
        $timestamp = is_null($timestamp) ? time() : intval($timestamp/1000);

        return date($format, $timestamp);
    }

    /**
     * 毫秒转换为秒
     *
     * @param $timestamp
     * @return int
     */
    public static function toSecond($timestamp)
    {
        return intval($timestamp/1000);
    }

    /**
     * 秒转换为毫秒
     *
     * @param $timestamp
     * @return float|int
     */
    public static function fromSecond($timestamp)
    {
        return $timestamp * 1000;
    }
}