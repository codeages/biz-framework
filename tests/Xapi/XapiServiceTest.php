<?php

namespace Tests\Xapi;

use Codeages\Biz\Framework\Xapi\Job\PushStatementsJob;
use Tests\IntegrationTestCase;

class XapiServiceTest extends IntegrationTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->biz['xapi.options'] = array(
            'version' => '1.0.0',
            'getway' => 'http://192.168.4.214:8762/xapi/statement'
        );

        $this->biz['user'] = array(
            'id' => 1,
        );
    }

    public function testCreateStatement()
    {
        $statement = array(
            'data' => array(
                'verb' => 'xx',
                'actor' => 'xx',
                'object' => 'xx'
            ),
        );
        $savedStatement = $this->getXapiService()->createStatement($statement);

        $this->assertNotEmpty($savedStatement['uuid']);
        $this->asserts($statement, $savedStatement);
    }

    public function testPushedStatements()
    {
        $statement = array(
            'data' => array(
                'verb' => 'xx',
                'actor' => 'xx',
                'object' => 'xx'
            ),
        );
        $savedStatement = $this->getXapiService()->createStatement($statement);
        $this->asserts($statement, $savedStatement);

        $this->getXapiService()->updateStatementsPushingByStatementIds(array($savedStatement['id']));
        $savedStatement = $this->getStatementDao()->get($savedStatement['id']);
        $statement['status'] = 'pushing';
        $this->asserts($statement, $savedStatement);

        $this->getXapiService()->updateStatementsPushedByStatementIds(array($savedStatement['id']));
        $savedStatement = $this->getStatementDao()->get($savedStatement['id']);
        $statement['status'] = 'pushed';
        $this->asserts($statement, $savedStatement);
    }

    public function pushStatementJob()
    {
        $statement = array(
            'data' => array(
                'actor' => array(
                    "objectType" => "Agent",
                        "account" => array(
                        "id" => "38923",
                        "name" => "张三",
                        "email" => "zhangsan@howzhi.com",
                        "homePage" => "http://www.ganlantu.com"
                    )
                ),
                'verb' => array(
                    "id" => "https://w3id.org/xapi/acrossx/verbs/watched",
                    "display" => array(
                        "zh-CN" => "观看",
                        "en-US" => "watched"
                    )
                ),
                'object' => array(
                    "id" => "99cfb51730c44434b60a9bb23c65bd0a",
                    "type" => "https://w3id.org/xapi/acrossx/activities/video",
                    "defination" => array(
                        "name" => array(
                            "zh-CN" => "摄影基础"
                        )
                    )
                ),
                'result' => array(
                    "duration" => "PT4M30S",
                    "extensions" => array(
                        "http://id.tincanapi.com/extension/starting-point" => "PT2M",
                        "http://id.tincanapi.com/extension/ending-point" => "PT6M30S"
                    )
                ),
                'context' => array(
                    "extensions" => array(
                        "http://xapi.edusoho.com/extensions/school" => array(
                            "id" => "110",
                            "name" => "Photo",
                            "url" => "http://www.edusoho.com"
                        )
                    )
                )
            ),
        );
        $savedStatement = $this->getXapiService()->createStatement($statement);
        $this->asserts($statement, $savedStatement);

        $job = new PushStatementsJob(array(), $this->biz);
        $job->execute();

        $savedStatement = $this->getStatementDao()->get($savedStatement['id']);
        $statement['status'] = 'pushed';
        $this->asserts($statement, $savedStatement);
    }

    protected function asserts($excepted, $acturel)
    {
        $keys = array_keys($excepted);
        foreach ($keys as $key) {
            $this->assertEquals($excepted[$key], $acturel[$key]);
        }
    }

    protected function getXapiService()
    {
        return $this->biz->service('Xapi:XapiService');
    }

    protected function getStatementDao()
    {
        return $this->biz->service('Xapi:StatementDao');
    }
}