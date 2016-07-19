<?php

namespace Codeages\Biz\Framework\UnitTests;

abstract class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    protected static $kernel;

    public static function setUpBeforeClass()
    {
        
    }

    public function setUp()
    {
        self::emptyDatabase();
    }

    public static function setKernel($kernel)
    {
        self::$kernel = $kernel;
    }

    public static function emptyDatabase($all=false)
    {
        $db = self::$kernel['db'];

        if ($all) {
            $tableNames = $db->getSchemaManager()->listTableNames();
        } else {
            $tableNames = $db->getInsertedTables();
            $tableNames = array_unique($tableNames);
        }

        $sql = '';

        foreach ($tableNames as $tableName) {
            if ($tableName == 'migrations') {
                continue;
            }

            $sql .= "TRUNCATE {$tableName};";
        }

        if (!empty($sql)) {
            $db->exec($sql);
            $db->resetInsertedTables();
        }

    }

}