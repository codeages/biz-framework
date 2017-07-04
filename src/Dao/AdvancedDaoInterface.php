<?php

namespace Codeages\Biz\Framework\Dao;

interface AdvancedDaoInterface extends GeneralDaoInterface
{
    public function batchCreate($rows);

    /**
     * $param $identifyColumn id
     * @param $identifies array(1,2,3,4)
     * @param $columnsList array(array('name' => '123', 'title' => '456'))
     * @return mixed
     */
    public function batchUpdate($identifies, $updateColumnsList, $identifyColumn = 'id');
}
