<?php

namespace Codeages\Biz\Framework\Validation;

class RuleParser
{
    public static function parse($rules)
    {
        $parts = explode('|', $rules);
        $rules = array();
        foreach ($parts as $rule) {
            if (strpos($rule, ':') !== false) {
                list($name, $parameters) = explode(':', $rule, 2);
                $parameters = str_getcsv($parameters);
                $rules[] = array_merge(array($name), $parameters);
            } else {
                $rules[] = $rule;
            }
        }

        return $rules;
    }
}
