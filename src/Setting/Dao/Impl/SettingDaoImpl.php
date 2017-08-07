<?php

namespace Codeages\Biz\Framework\Setting\Dao\Impl;

use Codeages\Biz\Framework\Setting\Dao\SettingDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\Biz\Framework\Dao\Annotation\CacheStrategy;
use Codeages\Biz\Framework\Dao\Annotation\RowCache;

/**
 * @CacheStrategy("Row")
 */
class SettingDaoImpl extends GeneralDaoImpl implements SettingDao
{
    protected $table = 'biz_setting';

    /**
     * @RowCache
     */
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
