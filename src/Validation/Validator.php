<?php

namespace Codeages\Biz\Framework\Validation;

/**
 * 数据有效性校验.
 *
 * @author malianbo  <malianbo@howzhi.com>
 * @date   2016-06-08
 */
class Validator
{
    const NAMING_REG = '/^[a-zA-Z_]{1}[a-zA-Z0-9_]{0,30}$/i';
    /**
     * user defined validators.
     *
     * define method like rule_xxx(params)
     *
     * @var array
     */
    private $extValidators;

    /**
     * default validation config.
     *
     * @var array
     */
    private $defaultConfig;

    /**
     * error messages store here.
     *
     * @var array
     */
    private $errors = array();

    /**
     * rule needed to check when val is null
     */
    private $requiredRules = array();
    /**
     * filter $data with keys defined in $ruleConfigs if $config["filter"] = true.
     *
     * @var array
     */
    private $filteredData = array();

    public function __construct(array $cfg = null)
    {
        $this->extValidators = array();
        $this->requiredRules = array('required', 'sameWith', 'diffWith', 'requiredWith');
        //default validation config
        $this->defaultConfig = array(
            'blocked' => true,
            'filter' => false, // work only on linear array
            'throwException' => false,
            'ignoreSyntaxError' => false,
        );
        //default timezone
        date_default_timezone_set('UTC');

        $this->config($cfg);
    }

    /**
     * set global Validation configuration.
     *
     * @param array $cfg
     *
     * @return the effective config
     */
    public function config(array $cfg = null)
    {
        // build effective config
        if ($cfg != null && sizeof($cfg) > 0) {
            $this->defaultConfig = array_merge($this->defaultConfig, $cfg);
        }

        return $this->defaultConfig;
    }

    public function errors()
    {
        return $this->errors;
    }

    public function filteredData()
    {
        return $this->filteredData;
    }

    public function fails()
    {
        return sizeof($this->errors) > 0;
    }

    /**
     * add user defined rule.
     *
     * @param string   $ruleName rule name
     * @param function $func     validation logic of rule, must return error message if any
     */
    public function addRule($ruleName, $func)
    {
        if (!$this->match(self::NAMING_REG, $ruleName)) {
            throw new ValidationException(null, null, "Invalid RuleName : $ruleName");

            return;
        }
        if (!is_callable($func)) {
            throw new ValidationException(null, null, "Not a Function : $func");

            return;
        }
        $this->extValidators[$ruleName] = $func;
    }

    /**
     * get user defined rule.
     *
     * @param string $ruleName
     *
     * @return function
     */
    public function getExtRule($ruleName)
    {
        return $this->extValidators[$ruleName];
    }

    /**
     * validate your data with rules configured.
     * 
     * 
     * @param array $data
     * @param array $rules
     * @param array $config
     */
    public function validate(array $data, array $ruleConfigs, array $config = null)
    {
        //reset
        $this->errors = array();
        $this->filteredData = array();

        if (is_null($data) || sizeof($data) === 0
            || is_null($ruleConfigs) || sizeof($ruleConfigs) === 0) {
            return;
        }

        $this->config($config);

        foreach ($ruleConfigs as $key => $rules) {
            $vals = $this->getValuesFromData($data, $key);
            $rulesArr = explode('|', $rules);
            for ($j = 0; $j < sizeof($rulesArr); ++$j) {
                for ($i = 0; $i < sizeof($vals); ++$i) {
                    $msg = $this->validateByRule($data, $key, $vals[$i], trim($rulesArr[$j]));
                    if (!empty($msg)) {
                        array_push($this->errors, '[' . $this->shorten($vals[$i]) . ']' . $msg);
                        if ($this->defaultConfig['throwException']) {
                            throw new ValidationException($key, $vals[$i], $msg);
                        }
                        if ($this->defaultConfig['blocked']) {
                            return;
                        }
                    }
                }
            }
        }

        //if we should filter $data
        if (sizeof($this->errors) <= 0 && $this->defaultConfig['filter']) {
            $this->filteredData = array();
            foreach ($ruleConfigs as $key => $rules) {
                $vals = $this->getValuesFromData($data, $key);
                if (sizeof($vals) > 0) {
                    //XXX work for linear array 
                    $this->filteredData[$key] = $vals[0];
                }
            }
        }
    }

    private function validateByRule(array $data, $key, $val, $rule)
    {
        $ruleName = $rule;
        $params = array();
        $left_bracket_pos = stripos($rule, '(');
        if (!is_bool($left_bracket_pos)) { //有参数
            $right_bracket_pos = stripos($rule, ')');
            $ruleName = mb_substr($rule, 0, $left_bracket_pos);
            $tmp = mb_substr($rule, $left_bracket_pos + 1, $right_bracket_pos - $left_bracket_pos - 1);
            if ($tmp != '') {
                $params = explode(',', $tmp);
            }
        }

        for ($i = 0; $i < sizeof($params); ++$i) {
            $ele = trim($params[$i]);
            //check per parameter type
            $first = mb_substr($ele, 0, 1);
            if ($first == '\'' || $first == '"') {
                $params[$i] = mb_substr($ele, 1, mb_strlen($ele) - 2);
            } elseif ($this->is_variable($ele)) {
                //only the first, unsupport multi values from subarray
                $values = $this->getValuesFromData($data, $params[$i]);
                $params[$i] = $values[0];
            } else {
                //numeric , ignore
            }
        }
        //如果值为空且不是校验required这样的rule，则不必继续，比如 email,如果$val=null/'',则没必要校验rule email。
        if((is_null($val) || $val === '') && !array_key_exists($ruleName, $this->requiredRules)){
            return '';
        }

        if (array_key_exists($ruleName, $this->extValidators)) {
            if (sizeof($params) > 0) {
                return call_user_func($this->extValidators[$ruleName], $val, $params);
            } else {
                return call_user_func($this->extValidators[$ruleName], $val);
            }
        }

        // validate by internal rule
        $ruleFunc = 'rule_'.$ruleName;
        if (method_exists($this, $ruleFunc)) {
            return $this->$ruleFunc($val, $params);
        } elseif ($this->defaultConfig['ignoreSyntaxError']) {
            echo "\n[ignored] ruleFunction: $ruleFunc is not exist.\n";
        } else {
            throw new ValidationException($key, $val, "Rule[$ruleName] is not defined");
        }
    }

    /**
     * check if a object represent a variable (or constant)
     * variable looks like true/false or starts with a-z/A-Z_ .
     *
     * @param mixed $var string or number
     *
     * @return bool true = is variable
     */
    private function is_variable($var)
    {
        return strcasecmp($var, 'true') !== 0
            && strcasecmp($var, 'false') !== 0
            && $this->match('/[a-zA-Z_]/', mb_substr($var, 0, 1));
    }

    private function getValuesFromData(array $data, $key)
    {
        $keyArr = explode('.', $key);
        if (sizeof($keyArr) == 1) {
            return array_key_exists($key, $data) ? array($data[$key]) : array();
        }
        //XXX support keys like：user.title, user.[].title, but only one layer.
        $vals = array();
        $sublist = $data[$keyArr[0]];
        if ($keyArr[1] == '[]' && sizeof($sublist) > 0) {
            for ($i = 0; $i < sizeof($sublist); ++$i) {
        if(array_key_exists($keyArr[2], $sublist[$i])){
            array_push($vals, $sublist[$i][$keyArr[2]]);    
        }
            }
        } else if(array_key_exists($keyArr[1], $sublist)){
            array_push($vals, $sublist[$keyArr[1]]);
        }

        return $vals;
    }

    //------------------------------------------------------
    // Internal Rules
    //------------------------------------------------------

    /**
     * check if value is null or empty.
     *
     * @return string message if false
     */
    private function rule_required($value)
    {
        if (is_bool($value) || is_numeric($value)) {
            return;
        }

        if (empty($value)) {
            return '不能为空';
        }

        return '';
    }

    private function rule_bool($value)
    {
        if (is_bool($value)
          || $this->match('/^((true)|(false)|1|0)$/i', strval($value))) {
            return '';
        }

        return '不是有效Bool值';
    }

    /**
     * accept an int or int string based 10
     * we do not accept scientific notation.
     *
     * @param int/string $value
     *
     * @return         
     */
    private function rule_int($value)
    {
        if (is_int($value)) {
            return '';
        }
        //is numeric string
        if (is_numeric($value) && is_string($value)) {
            //is integer & is within scope of integer
            if (intval($value) == floatval($value) && $this->match("/^(\+|-)?\d+$/", $value)) {
                return '';
            }
        }

        return '不是有效整数';
    }

    /**
     * accept an float or float string based 10
     * we do not accept scientific notation.
     *
     * @param int/float/string $value
     *
     * @return 
     */
    private function rule_float($value)
    {
        if (is_int($value) || is_float($value)
          || (is_numeric($value) && is_string($value) && $this->match("/^(\+|-)?\d+(\.\d+)?$/", $value))) {
            return '';
        }

        return '不是有效数值';
    }

    /**
     * check if $value is a valid date.
     *
     * @param array $data
     * @param  $value      
     * @param array $extparams ele: $fmt,...
     *
     * @return string void             
     */
    private function rule_date($value, array $params)
    {
        $fmt = sizeof($params) > 0 && mb_strlen($params[0]) > 0 ? $params[0] : 'Y-m-d';
        $d = \DateTime::createFromFormat($fmt, $value);
        if ($d && $d->format($fmt) === $value) {
            return '';
        }

        return '不是有效日期';
    }

    private function rule_string($value)
    {
        if (!is_string($value)) {
            return '不是有效字符串';
        }

        return '';
    }
    /**
     * check json string.
     *
     * @param string $value json string
     *
     * @return  
     */
    private function rule_jsonstr($value)
    {
        if (is_string($value)) {
            json_decode($value);
            if (json_last_error() === JSON_ERROR_NONE) {
                return '';
            };
        }

        return '不是有效JSON字符串';
    }
    /**
     * check json object
     * XXX to be tested.
     *
     * @param object $value
     *
     * @return  
     */
    private function rule_json($value)
    {
        if (!is_scalar($value) && !method_exists($value, '__toString')) {
            return '不是有效JSON';
        }

        json_decode($value);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return '不是有效JSON';
        };

        return '';
    }

    /**
     * check if $value(number) between min & max.
     *
     * @param array  $data
     * @param number $value
     * @param array  $params ele: min, max,...
     *
     * @return  
     */
    private function rule_range($value, array $params)
    {
        $min = sizeof($params) > 0 ? (float) $params[0] : -PHP_INT_MAX;
        $max = sizeof($params) > 1 ? (float) $params[1] : PHP_INT_MAX;
        if (!$this->isNumber($value)) {
            return '不是有效数值';
        }
        $val = floatval($value);
        if ($val < $min || $val > $max) {
            return "不在指定范围($min, $max)内";
        }

        return '';
    }

    private function rule_lenrange($value, array $params)
    {
        $min = sizeof($params) > 0 ? (int) $params[0] : -PHP_INT_MAX;
        $max = sizeof($params) > 1 ? (int) $params[1] : PHP_INT_MAX;

        if (!is_string($value)) {
            return '不是有效字符串';
        }
        if (mb_strlen($value) < $min || mb_strlen($value) > $max) {
            return "不在指定长度范围($min, $max)内";
        }

        return '';
    }

    private function rule_min($value, array $params)
    {
        $min = sizeof($params) > 0 ? (int) $params[0] : -PHP_INT_MAX;
        if (!$this->isNumber($value)) {
            return '不是有效数值';
        }
        $val = floatval($value);
        if ($val < $min) {
            return "小于约定的最小值($min)";
        }

        return '';
    }

    private function rule_max($value, array $params)
    {
        $max = sizeof($params) > 0 ? (int) $params[0] : PHP_INT_MAX;
        if (!$this->isNumber($value)) {
            return '不是有效数值';
        }
        $val = floatval($value);
        if ($val > $max) {
            return "大于约定的最大值($max)";
        }

        return '';
    }

    private function rule_minlen($value, array $params)
    {
        $min = sizeof($params) > 0 ? (int) $params[0] : -PHP_INT_MAX;

        if (!is_string($value)) {
            return '不是有效字符串';
        }
        if (mb_strlen($value) < $min) {
            return "小于约定的最小长度($min)";
        }

        return '';
    }

    private function rule_maxlen($value, array $params)
    {
        $max = sizeof($params) > 0 ? (int) $params[0] : PHP_INT_MAX;

        if (!is_string($value)) {
            return '不是有效字符串';
        }
        if (mb_strlen($value) > $max) {
            return "大于约定的最大长度($max)";
        }

        return '';
    }

    private function rule_ip($value)
    {
        if (!is_string($value)) {
            return '不是有效字符串';
        }
        if (!$this->match("/^(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])$/", $value)) {
            return '不是有效的IP地址';
        }

        return '';
    }

    private function rule_email($value)
    {
        if (!is_string($value)) {
            return '不是有效字符串';
        }
        if (!$this->match("/^(\w)+(\.\w+)*@(\w)+((\.\w+)+)$/", $value)) {
            return '不是有效e-mail';
        }

        return '';
    }

    private function rule_cnid($value)
    {
        if (!is_string($value)) {
            return '不是有效字符串';
        }
        //length:15／18(endsWith:[0-9xX])
        if (!$this->match("/^(\d{18,18}|\d{15,15}|\d{17,17}x)$/", $value)) {
            return '不是有效身份证号';
        }

        return '';
    }

    private function rule_cnmobile($value)
    {
        if (!is_string($value)) {
            return '不是有效字符串';
        }
        // work for china mobile phone number ((+)86)
        if (!$this->match("/^1\d{10}$/", $value)) {
            return '不是有效手机号码';
        }

        return '';
    }

    //XXX too complicated, use rule_url_http instead
    private function rule_url($value)
    {
        /*
         * This pattern is derived from Symfony\Component\Validator\Constraints\UrlValidator (2.7.4).
         *
         * (c) Fabien Potencier <fabien@symfony.com> http://symfony.com
         */
        $pattern = '~^
            ((aaa|aaas|about|acap|acct|acr|adiumxtra|afp|afs|aim|apt|attachment|aw|barion|beshare|bitcoin|blob|bolo|callto|cap|chrome|chrome-extension|cid|coap|coaps|com-eventbrite-attendee|content|crid|cvs|data|dav|dict|dlna-playcontainer|dlna-playsingle|dns|dntp|dtn|dvb|ed2k|example|facetime|fax|feed|feedready|file|filesystem|finger|fish|ftp|geo|gg|git|gizmoproject|go|gopher|gtalk|h323|ham|hcp|http|https|iax|icap|icon|im|imap|info|iotdisco|ipn|ipp|ipps|irc|irc6|ircs|iris|iris.beep|iris.lwz|iris.xpc|iris.xpcs|itms|jabber|jar|jms|keyparc|lastfm|ldap|ldaps|magnet|mailserver|mailto|maps|market|message|mid|mms|modem|ms-help|ms-settings|ms-settings-airplanemode|ms-settings-bluetooth|ms-settings-camera|ms-settings-cellular|ms-settings-cloudstorage|ms-settings-emailandaccounts|ms-settings-language|ms-settings-location|ms-settings-lock|ms-settings-nfctransactions|ms-settings-notifications|ms-settings-power|ms-settings-privacy|ms-settings-proximity|ms-settings-screenrotation|ms-settings-wifi|ms-settings-workplace|msnim|msrp|msrps|mtqp|mumble|mupdate|mvn|news|nfs|ni|nih|nntp|notes|oid|opaquelocktoken|pack|palm|paparazzi|pkcs11|platform|pop|pres|prospero|proxy|psyc|query|redis|rediss|reload|res|resource|rmi|rsync|rtmfp|rtmp|rtsp|rtsps|rtspu|secondlife|service|session|sftp|sgn|shttp|sieve|sip|sips|skype|smb|sms|smtp|snews|snmp|soap.beep|soap.beeps|soldat|spotify|ssh|steam|stun|stuns|submit|svn|tag|teamspeak|tel|teliaeid|telnet|tftp|things|thismessage|tip|tn3270|turn|turns|tv|udp|unreal|urn|ut2004|vemmi|ventrilo|videotex|view-source|wais|webcal|ws|wss|wtai|wyciwyg|xcon|xcon-userid|xfire|xmlrpc\.beep|xmlrpc.beeps|xmpp|xri|ymsgr|z39\.50|z39\.50r|z39\.50s))://                                 # protocol
            (([\pL\pN-]+:)?([\pL\pN-]+)@)?          # basic auth
            (
                ([\pL\pN\pS-\.])+(\.?([\pL]|xn\-\-[\pL\pN-]+)+\.?) # a domain name
                    |                                              # or
                \d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}                 # a IP address
                    |                                              # or
                \[
                    (?:(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){6})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:::(?:(?:(?:[0-9a-f]{1,4})):){5})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){4})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,1}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){3})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,2}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){2})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,3}(?:(?:[0-9a-f]{1,4})))?::(?:(?:[0-9a-f]{1,4})):)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,4}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,5}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,6}(?:(?:[0-9a-f]{1,4})))?::))))
                \]  # a IPv6 address
            )
            (:[0-9]+)?                              # a port (optional)
            (/?|/\S+|\?\S*|\#\S*)                   # a /, nothing, a / with something, a query or a fragment
        $~ixu';

        if (preg_match($pattern, $value) !== 1) {
            return '不是有效URL';
        }

        return '';
    }

    private function rule_urlHttp($value)
    {
        $pattern = '~^
            ((http|https))://                                 # protocol
            (([\pL\pN-]+:)?([\pL\pN-]+)@)?          # basic auth
            (
                ([\pL\pN\pS-\.])+(\.?([\pL]|xn\-\-[\pL\pN-]+)+\.?) # a domain name
                    |                                              # or
                \d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}                 # a IP address
                    |                                              # or
                \[
                    (?:(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){6})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:::(?:(?:(?:[0-9a-f]{1,4})):){5})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){4})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,1}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){3})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,2}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){2})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,3}(?:(?:[0-9a-f]{1,4})))?::(?:(?:[0-9a-f]{1,4})):)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,4}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,5}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,6}(?:(?:[0-9a-f]{1,4})))?::))))
                \]  # a IPv6 address
            )
            (:[0-9]+)?                              # a port (optional)
            (/?|/\S+|\?\S*|\#\S*)                   # a /, nothing, a / with something, a query or a fragment
        $~ixu';

        if (preg_match($pattern, $value) !== 1) {
            return '不是有效网址';
        }

        return '';
    }

    private function rule_requiredWith($value, $params)
    {
        if (sizeof($params) <= 0) {
            return '';
        }
        if (!is_null($params[0])) {
            return $this->rule_required($value);
        }

        return '';
    }

    private function rule_sameWith($value, $params)
    {
        if (sizeof($params) <= 0) {
            return '';
        }
        if ($value != $params[0]) {
            return '和约定字段的值不一致';
        }

        return '';
    }

    private function rule_diffWith($value, $params)
    {
        if (sizeof($params) <= 0) {
            return '';
        }
        if ($value == $params[0]) {
            return "不应和$params[0]值相同";
        }

        return '';
    }

    /**
     * check if date $value is after $params[0].
     *
     * @param [type] $value  [description]
     * @param [type] $params [description]
     *
     * @return [type] [description]
     */
    private function rule_after($value, $params)
    {
        if (sizeof($params) <= 0) {
            return '';
        }
        //accept a number or string matches "Y-m-d" pattern
        $related = $params[0];
        $d1 = $this->isNumber($value) ? (int) $value : strtotime($value);
        $d2 = $this->isNumber($params[0]) ? (int) $params[0] : strtotime($params[0]);

        if (!$d1 || $d1 === 0) {
            return "无效的日期值: $value";
        }
        if (!$d1 || $d2 === 0) {
            return "无效的日期值：$related";
        }

        if ($d1 < $d2) {
            return "不在指定的日期值($params[0])之后";
        }
    }
    /**
     * check if date $value is before $params[0].
     *
     * @param  $value
     * @param  $params
     *
     * @return 
     */
    private function rule_before($value, $params)
    {
        if (sizeof($params) <= 0) {
            return '';
        }
        //accept a number or string matches "Y-m-d" pattern
        $related = $params[0];
        $d1 = $this->isNumber($value) ? (int) $value : strtotime($value);
        $d2 = $this->isNumber($related) ? (int) $related : strtotime($related);

        if (!$d1 || $d1 === 0) {
            return "无效的日期值: $value";
        }
        if (!$d2 || $d2 === 0) {
            return "无效的日期值：$related";
        }

        if ($d1 > $d2) {
            return "不在指定的日期值($related)之前";
        }
    }

    private function rule_between($value, $params)
    {
        if (sizeof($params) <= 0) {
            return '';
        }
        //日期可以是数值格式，或者Y-m-d这样的格式
        $d1 = $this->isNumber($value) ? (int) $value : strtotime($value);

        $d2 = $this->isNumber($params[0]) ? (int) $params[0] : strtotime($params[0]);
        $d3 = $this->isNumber($params[1]) ? (int) $params[1] : strtotime($params[1]);
        if (!$d1 || $d1 === 0) {
            return "无效的日期值: $value";
        }
        if (!$d2 || $d2 === 0) {
            return "无效的日期值：$params[0]";
        }
        if (!$d3 || $d3 === 0) {
            return "无效的日期值: $params[1]";
        }

        //$d2 & $d3 we do not know which is newer
        if (($d1 >= $d2 && $d1 <= $d3) || ($d1 >= $d3 && $d1 <= $d2)) {
            return ''; //OK
        }

        return "不在指定的日期值($params[0], $params[1])之间";
    }

    private function rule_in($value, $params)
    {
        if (is_null($value) || '' == $value) {
            return '';
        }
        $enums = sizeof($params) > 0 ? $params : array();

        for ($i = 0; $i < sizeof($enums); ++$i) {
            if ($value == $enums[$i]) {
                return '';//matches, ignore data type
            }
        }

        return '不在指定的列表中';
    }

    //--------------
    // tools
    //--------------

    /**
     *  check if $val matches regular expression $reg.
     * 
     * @param Regxp  $reg regular expression
     * @param string $val
     *
     * @return bool
     */
    private function match($reg, $val)
    {
        return preg_match($reg, $val) == 1;
    }

    private function isNumber($value)
    {
        if (is_int($value) || is_float($value)) {
            return true;
        }
        if (is_numeric($value) && is_string($value)) {
            return $this->match("/^\d+(\.?)\d+$/", $value);
        }
    }

    private function shorten($val){
        if(!empty($val) && is_string($val) && mb_strlen($val) > 20){
            return mb_substr($val, 0, 20) . '...';
        }
        return $val;
    }


    public function quickTest($ruleFunc, $value, array $params = array())
    {
        if (method_exists($this, $ruleFunc)) {
            return $this->$ruleFunc($value, $params);
        } elseif ($this->defaultConfig['ignoreSyntaxError']) {
            echo "ruleFunction [$ruleFunc] is not exist.";
        } else {
            throw new ValidationException(null, $value, "Rule[$ruleFunc] is not defined");
        }
    }
}
