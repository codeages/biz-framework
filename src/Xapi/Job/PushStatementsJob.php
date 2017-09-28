<?php

namespace Codeages\Biz\Framework\Xapi\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Codeages\Biz\Framework\Util\ArrayToolkit;

class PushStatementsJob extends AbstractJob
{
    public function execute()
    {
        $condition = array(
            'status' => 'created'
        );
        $statments = $this->getXapiService()->searchStatements($condition, array('created_time' => 'ASC'), 0, 100);
        $statementIds = ArrayToolkit::column($statments, 'id');

        $this->getXapiService()->updateStatementsPushingByStatementIds($statementIds);
//        $this->getClient()->push($statments);
        $this->getXapiService()->updateStatementsPushedByStatementIds($statementIds);
    }

    protected function getClient()
    {
        // TODO
    }

    protected function getXapiService()
    {
        return $this->biz->service('Xapi:XapiService');
    }
}
