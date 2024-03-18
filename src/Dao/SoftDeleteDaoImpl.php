<?php

namespace Codeages\Biz\Framework\Dao;

use Codeages\Biz\Framework\Dao\SoftDelete\SoftDeletes;

abstract class SoftDeleteDaoImpl extends AdvancedDaoImpl
{
    use SoftDeletes;

    public function __call($name, $arguments)
    {
        $withDeletedSuffix = 'WithDeleted';

        if (substr($name, -strlen($withDeletedSuffix)) === $withDeletedSuffix) {
            $name = substr($name, 0, -strlen($withDeletedSuffix));
        }
        if (method_exists($this, $name)) {
            // 需要加载已删除的数据
            $this->startSetSoftDeleteStatus();
            $this->setSoftDeleteStatus(false);
            $result = call_user_func_array(array($this, $name), $arguments);
            $this->recoverSoftDeleteStatus();

            return $result;
        }
        throw new DaoException("Method: {$name} not exists");
    }
}