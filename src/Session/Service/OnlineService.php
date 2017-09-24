<?php

namespace Codeages\Biz\Framework\Session\Service;

interface OnlineService
{
    public function createOnline($online);

    public function updateOnline($id, $online);

    public function getOnlineBySessId($sessId);

    public function countLogined($gtSessTime);

    public function countTotal($gtSessTime);

    public function gc();

    public function searchOnlines($condition, $orderBy, $start, $limit);

    public function countOnlines($condition);

    public function sample($online);
}