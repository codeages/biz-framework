<?php

if (!function_exists('env')) {
    function env($key, $default = null)
    {
        return \Codeages\Biz\Framework\Utility\Env::get($key, $default);
    }
}

function array_filter_keys($array, array $keys)
{
    if (!is_array($array)) {
        return $array;
    }
    return array_intersect_key($array, array_flip($keys));
}

function array_walk_transform(array $array, callable $callback): array
{
    $transformed = [];
    foreach ($array as $item) {
        $transformed[] = $callback($item);
    }
    return $transformed;
}