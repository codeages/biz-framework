<?php

namespace Codeages\Biz\Framework\Dao;

interface CacheStrategy
{
    public function beforeGet($method, $arguments);

    public function afterGet($method, $arguments, $row);

    public function beforeFind($methd, $arguments);

    public function afterFind($methd, $arguments, array $rows);

    public function beforeSearch($methd, $arguments);

    public function afterSearch($methd, $arguments, array $rows);

    public function afterCreate($methd, $arguments, $row);

    public function afterUpdate($methd, $arguments, $row);

    public function afterDelete($methd, $arguments);
}