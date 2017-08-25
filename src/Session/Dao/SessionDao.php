<?php

namespace Codeages\Biz\Framework\Session\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface SessionDao extends GeneralDaoInterface
{
    public function updateBySessId($sessId, $session);

    public function getBySessId($sessId);

    public function deleteBySessId($sessId);

    public function gc();

    public function countLogined($gtSessTime);

    public function countTotal($gtSessTime);
}