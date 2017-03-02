<?php

namespace Codeages\Biz\Framework\Dao;

class GeneralFieldSerializer implements FieldSerializer
{
    /**
     * @inheritDoc
     */
    public function serialize($name, $value)
    {
        $methods = [
            'json' => function ($value) {
                if (empty($value)) {
                    return '';
                }

                return json_encode($value);
            },
            'delimiter' => function ($value) {
                if (empty($value)) {
                    return '';
                }

                return '|'.implode('|', $value).'|';
            },
            'php' => function ($value) {
                return serialize($value);
            },
        ];

        return $methods[$name]($value);
    }

    /**
     * @inheritDoc
     */
    public function unserialize($name, $value)
    {
        $methods = [
            'json' => function ($value) {
                if (empty($value)) {
                    return array();
                }

                return json_decode($value, true);
            },
            'delimiter' => function ($value) {
                if (empty($value)) {
                    return array();
                }

                return explode('|', trim($value, '|'));
            },
            'php' => function ($value) {
                return unserialize($value);
            },
        ];

        return $methods[$name]($value);
    }
}
