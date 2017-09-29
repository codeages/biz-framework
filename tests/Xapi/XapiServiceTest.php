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
            'getway' => ''
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

    public function testPushStatementJob()
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