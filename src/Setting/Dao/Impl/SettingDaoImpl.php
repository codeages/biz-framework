<?php

namespace Codeages\Biz\Framework\Setting\Dao\Impl;

use Codeages\Biz\Framework\Setting\Dao\SettingDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class SettingDaoImpl extends GeneralDaoImpl implements SettingDao
{
    protected $table = 'biz_setting';

    public function getByName($name)
    {
        return $this->getByFields(array('name' => $name));
    }

    public function declares()
    {
        return array(
            'serializes' => array('data' => 'php'),
        );
    }
}
