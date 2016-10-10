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
        $args     = func_get_args();
        $method   = $args[1];
        $callback = array_pop($args);

        $prefix = $this->getPrefix($method, array('get', 'find'));
        if ($prefix) {
            return $this->strategy->fetchCache($this->rootNameSpace, $args, $callback);
        }

        $prefix = $this->getPrefix($method, array('create', 'update', 'delete', 'wave'));
        if ($prefix) {
            $data = call_user_func_array($callback, $args);
            $this->strategy->deleteCache($this->rootNameSpace, $args);
            return $data;
        }
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
