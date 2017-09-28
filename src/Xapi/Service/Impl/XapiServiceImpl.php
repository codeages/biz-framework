<?php

namespace Codeages\Biz\Framework\Xapi\Service\Impl;

use Codeages\Biz\Framework\Dao\BatchUpdateHelper;
use Codeages\Biz\Framework\Service\BaseService;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\Framework\Xapi\Service\XapiService;

class XapiServiceImpl extends BaseService implements XapiService
{
    public function createStatement($statement)
    {
        if (empty($this->biz['user'])) {
            throw new AccessDeniedException('user is not login.');
        }

        if (!ArrayToolkit::requireds($statement, array('data'))) {
            throw new InvalidArgumentException('args is invalid, miss required fields.');
        }

        if (!ArrayToolkit::requireds($statement['data'], array('actor', 'verb', 'object'))) {
            throw new InvalidArgumentException('statement is invalid, miss required fields.');
        }

        $statement['version'] = $this->biz['xapi.options']['version'];
        $statement['user_id'] = $this->biz['user']['id'];
        $statement['uuid'] = '';

        return $this->getStatementDao()->create($statement);
    }

    public function updateStatementsPushedByStatementIds($statementIds)
    {
        $batchUpdateHelper = new BatchUpdateHelper($this->getStatementDao());
        foreach ($statementIds as $statementId) {
            $batchUpdateHelper->add('id', $statementId, array(
                'status' => 'pushed',
                'push_time' => time()
            ));
        }
        $batchUpdateHelper->flush();
    }

    public function updateStatementsPushingByStatementIds($statementIds)
    {
        $batchUpdateHelper = new BatchUpdateHelper($this->getStatementDao());
        foreach ($statementIds as $statementId) {
            $batchUpdateHelper->add('id', $statementId, array(
                'status' => 'pushing',
            ));
        }
        $batchUpdateHelper->flush();
    }

    public function searchStatements($conditions, $orders, $start, $limit)
    {
        return $this->getStatementDao()->search($conditions, $orders, $start, $limit);
    }

    protected function getStatementDao()
    {
        return $this->biz->dao('Xapi:StatementDao');
    }
}
