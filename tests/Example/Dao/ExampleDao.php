<?php
namespace Codeages\Biz\Framework\Tests\Example\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ExampleDao extends GeneralDaoInterface
{
    public function findByName($name, $start, $limit);
}