<?php

namespace Codeages\Biz\Framework\Dao\CacheStrategy;

class CacheStrategyFactory
{
    private static $strategyMap = array();

    public static function getCacheStrategy($strategyName, $config)
    {
        if (empty(self::$strategyMap[$strategyName])) {
            $class                            = __NAMESPACE__.'\\'.ucfirst($strategyName).'CacheStrategy';
            $strategy                         = new $class($config);
            self::$strategyMap[$strategyName] = $strategy;
        }

        return self::$strategyMap[$strategyName];
    }
}
