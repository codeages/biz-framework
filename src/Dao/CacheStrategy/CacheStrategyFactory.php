<?php

namespace Codeages\Biz\Framework\Dao\CacheStrategy;

class CacheStrategyFactory
{
    public static function getCacheStrategy($dao, $config)
    {
        $declares = $dao->declares();
        $strategy = $declares['cache'];

        $class = __NAMESPACE__.'\\'.ucfirst($strategy).'CacheStrategy';
        return new $class($dao, $config);
    }
}
