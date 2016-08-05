<?php

namespace Codeages\Biz\Framework\Validation;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function testArray()
    {
    }

    /**
     *
     * @return 
     */
    public function testAddRule()
    {
        $valid = new Validator();

        //throws ValidationException:
        //Invalid RuleName : dfasgew 
        // $valid->addRule("dfasgew ", function($tmp){
        // 	var_dump($tmp);
        // });

        $valid->addRule('basicRule', function ($tmp) {
            // var_dump($tmp);
        });

        call_user_func($valid->getExtRule('basicRule'), "\nparam!!!");
    }

    public function testValidate4VerySimpleBean()
    {
        $data = array(
            'name' => 'Hello, World',
        );

        $valid = new Validator();
        $valid->validate($data, array(
            'name' => 'required | lenrange(6,8)',
        ));

        $this->assertEquals(1, count($valid->errors()));

        $valid = new Validator();
        $valid->validate($data, array(
            'name' => 'required | lenrange(6,20)',
        ));

        $this->assertEquals(0, count($valid->errors()));
    }

    public function testValidate4SimpleBean()
    {
        $data = array(
            'name' => 'Hello, World',
            'age' => 13,
            'pass' => '123456',
            'pass_repeat' => '12345',
            'remark' => '',
        );

        $valid = new Validator();
        $valid->validate($data, array(
            'name' => 'required|lenrange(6,2)',
            'age' => 'required|int|range(10, 2)',
            'pass' => 'required | lenrange(6,2)',
            'pass_repeat' => 'required | sameWith(pass)',
            'remark' => 'maxlen(200)',
        ), array(
            'blocked' => false,
        ));

        $this->assertEquals(4, sizeof($valid->errors()));
    }

    public function testValidate4BeanWithSubClass()
    {
        $data = array(
            'name' => 'Hello, World',
            'age' => 13,
            'pass' => '123456',
            'pass_repeat' => '12345',
            'remark' => '计算符号，可以考虑重复特定符号来表达，比如|| 、&& . 不考虑正则，因此使用｜最简单便捷',
            'profile' => array(
                'real_name' => 'Tomes Hanks',
                'birth' => '1968-09-01',
                'gender' => 'male',
                'career' => 'actor',
            ),
        );
        $valid = new Validator();
        $valid->validate($data, array(
            'name' => 'required | lenrange(6,16)',
            'age' => 'required | int | range(10, 120)',
            'pass' => 'required | lenrange(6,10)',
            'pass_repeat' => 'required | sameWith(pass)',
            'remark' => 'maxlen(20)',
            'profile.real_name' => 'required | lenrange(6,50)',
            'profile.birth' => 'date(\'Y-m-d\')',
            'profile.gender' => 'required | in(\'male\', \'female\')',
            'profile.career' => '',
        ), array(
            'blocked' => false,
            'ignoreSyntaxError' => true,
        ));

        $this->assertEquals(2, sizeof($valid->errors()));
    }

    /**
     * test validation of Bean with Sub List (Array with 2-dimensional array).
     */
    public function testValidate4BeanWithSubList()
    {
        $data = array(
            'name' => 'James Bond',
            'nickname' => '007',
            'remark' => 'hello, Kitty',
            'stories' => array(
                array(
                    'title' => 'Dr. No',
                    'time' => '1962',
                    'actor' => '肖恩·康纳利',
                ),
                array(
                    'title' => 'Spectre',
                    'time' => '2015',
                    'actor' => '丹尼尔·克雷格',
                ),
            ),
        );
        $valid = new Validator();
        $valid->validate($data, array(
            'name' => 'required | lenrange(6,16)',
            'nickname' => 'maxlen(50)',
            'stories.[].title' => 'required | lenrange(6,50)',
            'stories.[].time' => 'date(\'Y\')',
            'stories.[].actor' => 'required | in(\'male\', \'female\')',
        ), array(
            'blocked' => false,
            'ignoreSyntaxError' => true,
        ));
        $this->assertEquals(2, sizeof($valid->errors()));
    }

    //-----------------------------------
    // empty data/rule

    public function testEmptyData()
    {
        $valid = new Validator();
        $valid->validate(array('name' => 'James Bond'), array());
        //will escape validation
        $this->assertEquals(0, sizeof($valid->errors()));
    }

    public function testEmptyRule()
    {
        $valid = new Validator();
        $valid->validate(array(), array(
            'name' => 'required',
        ), array());
        //will escape validation
        $this->assertEquals(0, sizeof($valid->errors()));
    }

    //------------------------------------
    // ext rule

    public function testExtRule()
    {
        $data = array(
            'c' => 'a',
            'd' => 'abc',
        );

        $valid = new Validator();
        $valid->addRule('singleChar', function ($val) {
            if (is_string($val) && strlen($val) === 1) {
                //OK
                return '';
            }

            return '不是单字符';
        });

        $valid->validate($data, array(
            'c' => 'singleChar',
            'd' => 'singleChar',
        ), array('blocked' => false));

        $this->assertEquals(1, sizeof($valid->errors()));
    }

    public function testOverridedRule()
    {
        $data = array(
            'a' => 10.9,
            'b' => -2.6,
        );

        $valid = new Validator();
        $valid->addRule('range', function ($value, array $params) {
            $min = 0;
            $max = sizeof($params) > 0 ? (float) $params[0] : PHP_INT_MAX;
            if (!is_numeric($value)) {
                return '不是有效数值';
            }
            $val = floatval($value);
            if ($val < $min || $val > $max) {
                return "不在指定范围($min, $max)内";
            }

            return '';
        });

        $valid->validate($data, array(
            'a' => 'range(11)',
            'b' => 'range(11)',
        ), array());

        $this->assertEquals(1, sizeof($valid->errors()));
    }

    //------------------------------------
    // test internal rule

    public function testRuleBool()
    {
        $valid = new Validator();

        //valid bool : true/false/1/0
        ////true
        $this->assertTrue(strlen($valid->quickTest('rule_bool', true)) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_bool', false)) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_bool', 'false')) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_bool', 'true')) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_bool', 'trUe')) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_bool', 1)) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_bool', 0)) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_bool', '1')) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_bool', '0')) <= 0);

        //false
        $this->assertTrue(strlen($valid->quickTest('rule_bool', 'yes')) > 0);
        $this->assertTrue(strlen($valid->quickTest('rule_bool', 'no')) > 0);
        $this->assertTrue(strlen($valid->quickTest('rule_bool', 'abc')) > 0);
        $this->assertTrue(strlen($valid->quickTest('rule_bool', 213)) > 0);
        $this->assertTrue(strlen($valid->quickTest('rule_bool', null)) > 0);
        $this->assertTrue(strlen($valid->quickTest('rule_bool', '')) > 0);
    }

    public function testRuleInt()
    {
        $valid = new Validator();

        //true
        $this->assertFalse(strlen($valid->quickTest('rule_int', 0)) > 0);
        $this->assertFalse(strlen($valid->quickTest('rule_int', 1)) > 0);
        $this->assertFalse(strlen($valid->quickTest('rule_int', 12423)) > 0);
        $this->assertFalse(strlen($valid->quickTest('rule_int', -12423)) > 0);
        $this->assertFalse(strlen($valid->quickTest('rule_int', +12423)) > 0);

        $this->assertFalse(strlen($valid->quickTest('rule_int', '1')) > 0);
        $this->assertFalse(strlen($valid->quickTest('rule_int', '-112423')) > 0);
        //false
        $this->assertTrue(strlen($valid->quickTest('rule_int', 12.423)) > 0);
        $this->assertTrue(strlen($valid->quickTest('rule_int', '4e5')) > 0);
        $this->assertTrue(strlen($valid->quickTest('rule_int', '12.423')) > 0);
        $this->assertTrue(strlen($valid->quickTest('rule_int', '999999999999999999999')) > 0);
        $this->assertTrue(strlen($valid->quickTest('rule_int', '0x44')) > 0);
        $this->assertTrue(strlen($valid->quickTest('rule_int', '325dsag')) > 0);
        $this->assertTrue(strlen($valid->quickTest('rule_int', 'f4dsaf3')) > 0);
        $this->assertTrue(strlen($valid->quickTest('rule_int', '325dsag')) > 0);
    }

    public function testRuleFloat()
    {
        $valid = new Validator();

        //true
        $this->assertTrue(strlen($valid->quickTest('rule_float', 0)) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_float', 1)) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_float', 12423)) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_float', -12423)) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_float', +12423)) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_float', 12.423)) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_float', '12.423')) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_float', '1')) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_float', '-112423')) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_float', '999999999999999999999')) <= 0);
        //false
        $this->assertFalse(strlen($valid->quickTest('rule_float', '4e5')) <= 0);
        $this->assertFalse(strlen($valid->quickTest('rule_float', '1.425235e2')) <= 0);
        $this->assertFalse(strlen($valid->quickTest('rule_float', '0x44')) <= 0);
        $this->assertFalse(strlen($valid->quickTest('rule_float', '325dsag')) <= 0);
        $this->assertFalse(strlen($valid->quickTest('rule_float', 'f4dsaf3')) <= 0);
        $this->assertFalse(strlen($valid->quickTest('rule_float', '325dsag')) <= 0);
    }

    public function testRuleDate()
    {
        $valid = new Validator();

        //true
        $this->assertTrue(strlen($valid->quickTest('rule_date', '2016-09-09')) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_date', '2116-09-09')) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_date', '2116/09/09', array('Y/m/d'))) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_date', '2116/09', array('Y/m'))) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_date', '2116', array('Y'))) <= 0);

        $this->assertFalse(strlen($valid->quickTest('rule_date', 'fadsfafw')) <= 0);
        $this->assertFalse(strlen($valid->quickTest('rule_date', '19900816')) <= 0);
        $this->assertFalse(strlen($valid->quickTest('rule_date', '2116')) <= 0);
    }

    public function testRuleString()
    {
        $valid = new Validator();

        //true
        $this->assertTrue(strlen($valid->quickTest('rule_string', 'fdsawe')) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_string', '3254534')) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_string', '')) <= 0);

        $this->assertFalse(strlen($valid->quickTest('rule_string', null)) <= 0);
        $this->assertFalse(strlen($valid->quickTest('rule_string', true)) <= 0);
        $this->assertFalse(strlen($valid->quickTest('rule_string', 0)) <= 0);
    }

    public function testRuleJsonStr()
    {
        $valid = new Validator();

        //true
        $this->assertTrue(strlen($valid->quickTest('rule_jsonstr', '{"a": 1, "b" : "csd", "e": true, "d": 124.5325}')) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_jsonstr', '[{"a": 1, "b" : "csd", "e": true, "d": 124.5325}]')) <= 0);

        //false
        //single quotes in json string are invalid in php
        $this->assertFalse(strlen($valid->quickTest('rule_jsonstr', "{'a': 1, 'b' : 'csd', 'e': true, 'd': 124.5325}")) <= 0);
        $this->assertFalse(strlen($valid->quickTest('rule_jsonstr', "{a: 1, b : 'csd', e: true, d: 124.5325}")) <= 0);
        $this->assertFalse(strlen($valid->quickTest('rule_jsonstr', 'dfsafw{a: a}eaa')) <= 0);
        $this->assertFalse(strlen($valid->quickTest('rule_jsonstr', '{a: a}')) <= 0);
    }

    public function testRuleRange()
    {
        $valid = new Validator();

        //true
        $this->assertTrue(strlen($valid->quickTest('rule_range', '10', array(0))) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_range', 2355.225, array(0))) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_range', 100, array(0, 10000))) <= 0);
        //false
        $this->assertFalse(strlen($valid->quickTest('rule_range', '10', array(100))) <= 0);
        $this->assertFalse(strlen($valid->quickTest('rule_range', 'dasfw', array(0))) <= 0);
        $this->assertFalse(strlen($valid->quickTest('rule_range', 14, array(0, 10))) <= 0);
        $this->assertFalse(strlen($valid->quickTest('rule_range', 14, array(100, 20))) <= 0);
    }

    public function testRuleLenrange()
    {
        $valid = new Validator();

        //true
        $this->assertTrue(strlen($valid->quickTest('rule_lenrange', '10', array(0))) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_lenrange', 'geagae1g0', array(0))) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_lenrange', '43terg', array(5, 10))) <= 0);

        //false
        $this->assertFalse(strlen($valid->quickTest('rule_lenrange', '10', array(0, 1))) <= 0);
        $this->assertFalse(strlen($valid->quickTest('rule_lenrange', '43fafdsafs', array(-1, 6))) <= 0);
        $this->assertFalse(strlen($valid->quickTest('rule_lenrange', '4324362', array(100))) <= 0);
        $this->assertFalse(strlen($valid->quickTest('rule_lenrange', 3251, array(100))) <= 0);
    }

    public function testRuleMin()
    {
        $valid = new Validator();

        //true
        $this->assertTrue(strlen($valid->quickTest('rule_min', '10', array(0))) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_min', 43254, array(1000))) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_min', 33.5, array(10))) <= 0);
        //false
        $this->assertFalse(strlen($valid->quickTest('rule_min', '10', array(20))) <= 0);
        $this->assertFalse(strlen($valid->quickTest('rule_min', 'feafefea', array(20))) <= 0);
    }

    public function testRuleMax()
    {
        $valid = new Validator();

        //true
        $this->assertTrue(strlen($valid->quickTest('rule_max', '10', array(20))) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_max', 321, array(2000))) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_max', 332.321, array(2000))) <= 0);
        //false
        $this->assertFalse(strlen($valid->quickTest('rule_max', 321, array(200))) <= 0);
        $this->assertFalse(strlen($valid->quickTest('rule_max', '321', array(200))) <= 0);
        $this->assertFalse(strlen($valid->quickTest('rule_max', 'dsgaw', array(2000))) <= 0);
    }

    public function testRuleMinlen()
    {
        $valid = new Validator();

        //true
        $this->assertTrue(strlen($valid->quickTest('rule_minlen', 'fsdfwafw', array(2))) <= 0);
        //false
        $this->assertFalse(strlen($valid->quickTest('rule_minlen', 'fsdfwafw', array(100))) <= 0);
        $this->assertFalse(strlen($valid->quickTest('rule_minlen', 4254, array(1))) <= 0);
    }

    public function testRuleMaxlen()
    {
        $valid = new Validator();

        //true
        $this->assertTrue(strlen($valid->quickTest('rule_maxlen', 'fsdfwafw', array(20))) <= 0);
        //false
        $this->assertFalse(strlen($valid->quickTest('rule_maxlen', 'fsdfwafw', array(2))) <= 0);
        $this->assertFalse(strlen($valid->quickTest('rule_maxlen', 342543, array(2))) <= 0);
    }

    public function testRuleIp()
    {
        $valid = new Validator();

        //true
        $this->assertTrue(strlen($valid->quickTest('rule_ip', '192.168.1.124')) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_ip', '0.0.1.0')) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_ip', '255.255.255.255')) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_ip', '11.11.11.11')) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_ip', '222.222.222.222')) <= 0);
        //false
        $this->assertFalse(strlen($valid->quickTest('rule_ip', 'dsafega')) <= 0);
        $this->assertFalse(strlen($valid->quickTest('rule_ip', '11.11.11.11111')) <= 0);
        $this->assertFalse(strlen($valid->quickTest('rule_ip', '11.11.1.1.11')) <= 0);
        $this->assertFalse(strlen($valid->quickTest('rule_ip', '11.11.1')) <= 0);
        $this->assertFalse(strlen($valid->quickTest('rule_ip', '267.11.11.11')) <= 0);
    }
    /**
     *
     * @return  
     */
    public function testRuleEmail()
    {
        $valid = new Validator();

        //true
        $this->assertTrue(strlen($valid->quickTest('rule_email', 'malianbo@howzhi.com')) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_email', 'a.c.v@e.f')) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_email', 'a.c.v@e.f.g')) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_email', 'malianbomalianbomalianbomalianbomalianbomalianbo@howzhi.com')) <= 0);
        //false
        $this->assertFalse(strlen($valid->quickTest('rule_email', 'a.c.v@ee.e.f.')) <= 0);
        $this->assertFalse(strlen($valid->quickTest('rule_email', 'a.c@v@ee.e.f')) <= 0);
        $this->assertFalse(strlen($valid->quickTest('rule_email', 'a.cee.e.f')) <= 0);
        $this->assertFalse(strlen($valid->quickTest('rule_email', 'a.cee@f')) <= 0);
        $this->assertFalse(strlen($valid->quickTest('rule_email', 'a@f')) <= 0);
    }

    public function testRuleCnid()
    {
        $valid = new Validator();

        //true
        $this->assertTrue(strlen($valid->quickTest('rule_cnid', '135266199506086466')) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_cnid', '13526619950608646x')) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_cnid', '135233529506066')) <= 0);

        //false
        $this->assertFalse(strlen($valid->quickTest('rule_cnid', 'fiwegewgwgwgwww33t')) <= 0);
        $this->assertFalse(strlen($valid->quickTest('rule_cnid', '1352663352950608xx')) <= 0);
        $this->assertFalse(strlen($valid->quickTest('rule_cnid', '1352663356699950')) <= 0);
        $this->assertFalse(strlen($valid->quickTest('rule_cnid', 'x9352663352950608')) <= 0);
    }

    public function testRuleCnmobile()
    {
        $valid = new Validator();

        //true
        $this->assertTrue(strlen($valid->quickTest('rule_cnmobile', '15258873961')) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_cnmobile', '18759999931')) <= 0);
        //false
        $this->assertFalse(strlen($valid->quickTest('rule_cnmobile', '25258873961')) <= 0);
        $this->assertFalse(strlen($valid->quickTest('rule_cnmobile', '2525887396')) <= 0);
        $this->assertFalse(strlen($valid->quickTest('rule_cnmobile', '1343253253s')) <= 0);
    }

    public function testRuleUrl()
    {
        $valid = new Validator();

        //true
        $this->assertTrue(strlen($valid->quickTest('rule_url', 'http://gitlab.howzhi.net/edusoho/blob/master/developer-guide/git.md')) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_url', 'ftp://gitlab.howzhi.net')) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_url', 'http://127.0.0.1:91000/edusoho?name=mark')) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_url', 'http://gitlab.howzhi.net???a')) <= 0);
        //false
        $this->assertFalse(strlen($valid->quickTest('rule_url', 'fftttpp://gitlab.howzhi.net')) <= 0);

        $this->assertFalse(strlen($valid->quickTest('rule_url', 'http//gitlab.howzhi.net?a=ab&#@%fasd')) <= 0);
    }

    public function testRuleUrlHttp()
    {
        $valid = new Validator();

        //true
        $this->assertTrue(strlen($valid->quickTest('rule_urlHttp', 'http://gitlab.howzhi.net/edusoho/blob/master/developer-guide/git.md')) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_urlHttp', 'http://127.0.0.1:91000/edusoho?name=mark')) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_urlHttp', 'http://gitlab.howzhi.net???a')) <= 0);
        $this->assertTrue(strlen($valid->quickTest('rule_urlHttp', 'http://gitlab.howzhi.net?a=ab&#@%fasd')) <= 0);
        //false
        $this->assertFalse(strlen($valid->quickTest('rule_urlHttp', 'ftp://gitlab.howzhi.net')) <= 0);
        $this->assertFalse(strlen($valid->quickTest('rule_urlHttp', 'fftttpp://gitlab.howzhi.net')) <= 0);
    }

    public function testRuleRequiredWith()
    {
        /*$data = array(
            'a' => null,
            'b' => 'e',
            'c' => null,
            'd' => 'abc',
        );

        $valid = new Validator();

        $valid->validate($data, array(
            'c' => 'requiredWith(b)', //error
            'd' => 'requiredWith(b)', //ok
            'b' => 'requiredWith(a)', //ok
        ), array('blocked' => false));

        $this->assertEquals(1, sizeof($valid->errors()));*/
    }

    public function testRuleSameWith()
    {
        /*$data = array(
            'a' => 'eh',
            'b' => 'eh',
            'c' => null,
            'd' => 'abc',
        );

        $valid = new Validator();

        $valid->validate($data, array(
            'b' => 'sameWith(a)', //ok
            'c' => 'sameWith(b)', //error
            'd' => 'sameWith(c)', //error
        ), array('blocked' => false));

        $this->assertEquals(2, sizeof($valid->errors()));*/
    }

    public function testRuleDiffWith()
    {
        $data = array(
            'a' => 'eh',
            'b' => 'eh',
            'c' => null,
            'd' => 'abc',
        );

        $valid = new Validator();

        $valid->validate($data, array(
            'b' => 'diffWith(a)', //error
            'c' => 'diffWith(b)', //ok
            'd' => 'diffWith(c)', //ok
        ), array('blocked' => false));

        $this->assertEquals(1, sizeof($valid->errors()));
    }

    public function testRuleAfter()
    {
        $data = array(
            'a' => '2016-05-01',
            'b' => '2017-09-18',
            'c' => '2016-12-12',
            'd' => '2019-15-09',
        );

        $valid = new Validator();

        $valid->validate($data, array(
            'b' => 'after(a)', //ok
            'c' => 'after(b)', //error
            'd' => 'after(c)', //error : not a valid date
        ), array('blocked' => false));
        $this->assertEquals(2, sizeof($valid->errors()));
    }

    public function testRuleBefore()
    {
        $data = array(
            'a' => '2016-05-01',
            'b' => '2017-09-18',
            'c' => '2016-12-12',
            'd' => '2019-15-09',
        );

        $valid = new Validator();

        $valid->validate($data, array(
            'b' => 'before(a)', //error
            'c' => 'before(b)', //ok
            'd' => 'before(c)', //error : not a valid date
        ), array('blocked' => false));

        $this->assertEquals(2, sizeof($valid->errors()));
    }

    public function testRuleBetween()
    {
        $data = array(
            'a' => '2016-05-01',
            'b' => '2017-09-18',
            'c' => '2016-12-12',
            'd' => '2019-15-09',
        );

        $valid = new Validator();

        $valid->validate($data, array(
            'b' => 'between(a,c)', //error
            'c' => 'between(a,b)', //ok
            'c' => 'between(a,d)', //error : not a valid date
        ), array('blocked' => false));

        $this->assertEquals(2, sizeof($valid->errors()));
    }

    public function testRuleIn()
    {
        $data = array(
            'a' => 'male',
            'b' => 2,
            'c' => true,
            'd' => null,
            'f' => 'true',
        );

        $valid = new Validator();

        $valid->validate($data, array(
            'a' => 'in(\'male\', \'female\')', //ok
            'b' => 'in(1,2,3,4,5)', //ok
            'c' => 'in(true,false)', //ok
            'd' => 'in(\'male\', \'female\')', //ok
            'f' => 'in(true,false)', //error
        ), array('blocked' => false));

        $this->assertEquals(0, sizeof($valid->errors()));
    }

    public function testBlocked()
    {
        $data = array(
            'a' => 'male',
            'b' => 2,
            'c' => true,
            'd' => null,
            'f' => 'tru1e',
        );

        $valid = new Validator();

        $valid->validate($data, array(
            'a' => 'in(\'male\', \'female\')', //ok
            'b' => 'in(1,3,4,5)', //ok
            'c' => 'in(true,false)', //ok
            'd' => 'in(\'male\', \'female\')', //ok
            'f' => 'in(true,false)', //ok
        ), array('blocked' => false));

        $this->assertEquals(2, sizeof($valid->errors()));

        $valid1 = new Validator();
        $valid1->validate($data, array(
            'a' => 'in(\'male\', \'female\')', //ok
            'b' => 'in(1,3,4,5)', //ok
            'c' => 'in(true,false)', //ok
            'd' => 'in(\'male\', \'female\')', //ok
            'f' => 'in(true,false)', //error
        ), array('blocked' => true));
        $this->assertEquals(1, sizeof($valid1->errors()));
    }

    public function testFiltered()
    {
        $data = array(
            'a' => 'male',
            'b' => 2,
            'c' => true,
            'd' => null,
            'f' => 'tru1e',
        );

        $valid = new Validator();

        $valid->validate($data, array(
            'a' => 'in(\'male\', \'female\')', //ok
            'b' => 'in(1,2,3,4,5)', //ok
            'c' => 'in(true,false)', //ok
            'd' => 'in(\'male\', \'female\')', //ok
        ), array('blocked' => false, 'filter' => true));

        // var_dump($valid->errors());
        // var_dump($valid->filteredData());
        $this->assertEquals(4, sizeof($valid->filteredData()));
    }

    public function testThrowException()
    {
        $data = array(
            'a' => 'male',
            'b' => 2,
            'c' => true,
            'd' => null,
            'f' => 'tru1e',
        );

        $valid = new Validator();

        try {
            $valid->validate($data, array(
            'a' => 'in(\'male\', \'female\')', //ok
            'b' => 'in(1,2,3,4,5)', //ok
            'c' => 'in(true,false)', //ok
            'd' => 'inabc(\'male\', \'female\')', //ok
        ), array('blocked' => false, 'throwException' => false));
        } catch (\Exception $e) {
            echo "\n[Expected] ValidationException : ".$e->getMessage();
        }
    }

    public function testIgnoreSyntaxError()
    {
        $data = array(
            'a' => 'male',
            'b' => 2,
            'c' => true,
            'd' => null,
            'f' => 'tru1e',
        );

        $valid = new Validator();

        try {
            $valid->validate($data, array(
            'a' => 'in(\'male\', \'female\')', //ok
            'b' => 'in(1,2,3,4,5)', //ok
            'c' => 'in(true,false)', //ok
            'd' => 'inabc(\'male\', \'female\')', //ok
        ), array('blocked' => false, 'ignoreSyntaxError' => true));

            echo "\n [Expceted] Non Exception catched.";
        } catch (\Exception $e) {
            echo "\n[Expected] ValidationException : ".$e->getMessage();
        }
    }
}
