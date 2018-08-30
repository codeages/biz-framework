<?php

namespace Codeages\Biz\Framework\Targetlog\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Log
{
    const DEBUG = 'debug'; //1
    const INFO = 'info'; //2
    const NOTICE = 'notice'; //3
    const WARNING = 'warning'; //4
    const ERROR = 'error'; //5
    const CRITICAL = 'critical'; //6
    const ALERT = 'alter'; //7
    const EMERGENCY = 'emergency'; //8

    /**
     * @var
     * LOG INFO
     */
    private $message;

    /**
     * @var
     * same to TargetLogService level
     */
    private $level;

    private $action;

    private $targetType;

    private $targetId = 0;

    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            $method = 'set'.str_replace('_', '', $key);
            if (!method_exists($this, $method)) {
                throw new \BadMethodCallException(sprintf('Unknown property "%s" on annotation "%s".', $key, get_class($this)));
            }
            $this->$method($value);
        }
    }

    public function getLevelId()
    {
        switch ($this->level) {
            case self::DEBUG:$levelId = 1; break;
            case self::INFO:$levelId = 2; break;
            case self::NOTICE:$levelId = 3; break;
            case self::WARNING:$levelId = 4; break;
            case self::ERROR:$levelId = 5; break;
            case self::CRITICAL:$levelId = 6; break;
            case self::ALERT:$levelId = 7; break;
            case self::EMERGENCY:$levelId = 8; break;
            default:$levelId = 1;
        }

        return $levelId;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getLevel()
    {
        return $this->level;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getTargetType()
    {
        return $this->targetType;
    }

    public function getTargetId()
    {
        return $this->targetId;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function setLevel($level)
    {
        $this->level = $level;
    }

    public function setAction($action)
    {
        $this->action = $action;
    }

    public function setTargetType($targetType)
    {
        $this->targetType = $targetType;
    }

    public function setTargetId($targetId)
    {
        $this->targetId = $targetId;
    }
}
