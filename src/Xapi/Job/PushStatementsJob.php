<?php

namespace Codeages\Biz\Framework\Xapi\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Codeages\Biz\Framework\Util\ArrayToolkit;
use Guzzle\Http\Client;

class PushStatementsJob extends AbstractJob
{

    public function execute()
    {
        $condition = array(
            'status' => 'created'
        );
        $statements = $this->getXapiService()->searchStatements($condition, array('created_time' => 'ASC'), 0, 100);
        $statementIds = ArrayToolkit::column($statements, 'id');

        $this->getXapiService()->updateStatementsPushingByStatementIds($statementIds);
        $result = $this->pushStatements($statements);
        if ($result) {
            $this->getXapiService()->updateStatementsPushedByStatementIds($statementIds);
        }
    }

    protected function pushStatements($statements)
    {
        $client = new Client();
        $request = $client->post($this->biz['xapi.options']['getway'], array(), $statements);

        $response = $request->send();
        if ($response->getStatusCode() == 200) {
            return true;
        }

        return false;
    }

    protected function getXapiService()
    {
        return $this->biz->service('Xapi:XapiService');
    }
}
