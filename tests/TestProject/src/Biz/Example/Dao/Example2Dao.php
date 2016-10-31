<?php
namespace TestProject\Biz\Example\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface Example2Dao extends GeneralDaoInterface
{
    public function findByName($name, $start, $limit);
}