<?php

namespace Codeages\Biz\Framework\Token\Dao\Impl;

use Codeages\Biz\Framework\Token\Dao\TokenDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class TokenDaoImpl extends GeneralDaoImpl implements TokenDao
{
    protected $table = 'biz_token';

    public function getByKey($key)
    {
        return $this->getByFields(['_key' => $key]);
    }

    public function declares()
    {
        return array(
            'timestamps' => array('created_time'),
            'serializes' => array('data' => 'php'),
            'conditions' => array(
            ),
        );
    }
}
