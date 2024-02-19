<?php

namespace Tests;

/**
 * @requires PHP 5.5
 */
class UuidExampleDaoImplTest extends IntegrationTestCase
{
    public function testCreate()
    {
        $row = ['name' => 'test'];
        $row = $this->getUuidExampleDao()->create($row);
        $this->assertArrayHasKey('id', $row);
    }

    private function getUuidExampleDao()
    {
        return $this->biz->dao('Example:UuidExampleDao');
    }
}
