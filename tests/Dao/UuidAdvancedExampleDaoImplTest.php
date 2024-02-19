<?php

namespace Tests;

/**
 * @requires PHP 5.5
 */
class UuidAdvancedExampleDaoImplTest extends IntegrationTestCase
{
    public function testBatchCreate()
    {
        $rows = [
            ['name' => 'test 1'],
            ['name' => 'test 2'],
            ['name' => 'test 3'],
            ['name' => 'test 4'],
            ['name' => 'test 5'],
        ];
        $this->getUuidAdvancedExampleDao()->batchCreate($rows);
        $rows = $this->getUuidAdvancedExampleDao()->search([], ['id' => 'asc'], 0, 10);
        $this->assertCount(5, $rows);

        foreach ($rows as $row) {
            // code...
        }
    }

    private function getUuidAdvancedExampleDao()
    {
        return $this->biz->dao('Example:UuidAdvancedExampleDao');
    }
}
