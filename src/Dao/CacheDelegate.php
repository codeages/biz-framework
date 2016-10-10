<?php

namespace Codeages\Biz\Framework\Dao;

use Codeages\Biz\Framework\Dao\CacheStrategy\CacheStrategyFactory;

class CacheDelegate
{
    private $strategy; // table, promise
    private $rootNameSpace;

    public function __construct($config, $strategyName = 'table', $rootNameSpace)
    {
        $this->strategy      = CacheStrategyFactory::getCacheStrategy($strategyName, $config);
        $this->rootNameSpace = $rootNameSpace;
    }

    public function proccess()
    {
        $args = func_get_args();
        if (empty($args[1])) {
            throw new \InvalidArgumentException('args is invalid. ');
        }

        $prefix = $this->getPrefix($args[1], array('get', 'find', 'create', 'update', 'delete', 'wave'));
        if (empty($prefix)) {
            throw new \InvalidArgumentException('args is invalid. ');
        }

        return $this->strategy->$prefix($this->rootNameSpace, $args);
    }

    protected function getPrefix($str, $prefixs)
    {
        $_prefix = '';
        foreach ($prefixs as $prefix) {
            if (strpos($str, $prefix) === 0) {
                $_prefix = $prefix;
                break;
            }
        }

        return $_prefix;
    }
}
