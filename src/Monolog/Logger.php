<?php

namespace Codeages\Biz\Framework\Monolog;

use Monolog\Logger as BaseLogger;

/**
 * TODO 暂时重写6个等级，后续开发完成补全
 */
class Logger extends BaseLogger
{
    public function log($level, $message, array $context = array(), $type = '')
    {
        $level = static::toMonologLevel($level);

        return $this->addRecord($level, $message, $context, $type);
    }

    public function debug($message, array $context = array(), $type = '')
    {
        return $this->addRecord(static::DEBUG, $message, $context, $type);
    }

    public function info($message, array $context = array(), $type = '')
    {
        return $this->addRecord(static::INFO, $message, $context, $type);
    }

    public function notice($message, array $context = array(), $type = '')
    {
        return $this->addRecord(static::NOTICE, $message, $context, $type);
    }

    public function warn($message, array $context = array(), $type = '')
    {
        return $this->addRecord(static::WARNING, $message, $context, $type);
    }

    public function warning($message, array $context = array(), $type = '')
    {
        return $this->addRecord(static::WARNING, $message, $context, $type);
    }

    public function err($message, array $context = array(), $type = '')
    {
        return $this->addRecord(static::ERROR, $message, $context, $type);
    }

    public function error($message, array $context = array(), $type = '')
    {
        return $this->addRecord(static::ERROR, $message, $context, $type);
    }

    public function crit($message, array $context = array(), $type = '')
    {
        return $this->addRecord(static::CRITICAL, $message, $context, $type);
    }

    public function critical($message, array $context = array(), $type = '')
    {
        return $this->addRecord(static::CRITICAL, $message, $context, $type);
    }

    public function alert($message, array $context = array(), $type = '')
    {
        return $this->addRecord(static::ALERT, $message, $context, $type);
    }

    public function emerg($message, array $context = array(), $type = '')
    {
        return $this->addRecord(static::EMERGENCY, $message, $context, $type);
    }

    public function emergency($message, array $context = array(), $type = '')
    {
        return $this->addRecord(static::EMERGENCY, $message, $context, $type);
    }

    public function addRecord($level, $message, array $context = array(), $type = '')
    {
        if (!$this->handlers) {
            $this->pushHandler(new StreamHandler('php://stderr', static::DEBUG));
        }

        $levelName = static::getLevelName($level);

        // check if any handler will handle this message so we can return early and save cycles
        $handlerKey = null;
        reset($this->handlers);
        while ($handler = current($this->handlers)) {
            if ($handler->isHandling(array('level' => $level))) {
                $handlerKey = key($this->handlers);
                break;
            }

            next($this->handlers);
        }

        if (null === $handlerKey) {
            return false;
        }

        if (!static::$timezone) {
            static::$timezone = new \DateTimeZone(date_default_timezone_get() ?: 'UTC');
        }

        // php7.1+ always has microseconds enabled, so we do not need this hack
        if ($this->microsecondTimestamps && PHP_VERSION_ID < 70100) {
            $ts = \DateTime::createFromFormat('U.u', sprintf('%.6F', microtime(true)), static::$timezone);
        } else {
            $ts = new \DateTime(null, static::$timezone);
        }
        $ts->setTimezone(static::$timezone);

        $record = array(
            'message' => (string) $message,
            'context' => $context,
            'level' => $level,
            'level_name' => $levelName,
            'channel' => $this->name,
            'datetime' => $ts,
            'extra' => array(),
            'type' => $type,
        );
        foreach ($this->processors as $processor) {
            $record = call_user_func($processor, $record);
        }

        while ($handler = current($this->handlers)) {
            if (true === $handler->handle($record)) {
                break;
            }

            next($this->handlers);
        }

        return true;
    }
}
