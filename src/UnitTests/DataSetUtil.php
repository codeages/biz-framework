<?php
namespace Codeages\Biz\Framework\UnitTests;

class DataSetUtil extends \PHPUnit_Framework_TestCase
{
    private static $kernel;

    private static $instance;

    public static function instance($kernel)
    {
        if (empty(self::$kernel)) {
            self::$kernel   = $kernel;
            self::$instance = new self();
        }
    }

    public static function initDataSet($dataSet)
    {
        foreach ($dataSet as $table => $data) {
            foreach ($data as $fields) {
                self::$kernel['db']->insert($table, $fields);
            }
        }
    }

    public static function assertDataSet($dataSet)
    {
        foreach ($dataSet as $table => $data) {
            foreach ($data as $fields) {
                $sql    = "SELECT * FROM {$table} WHERE id = ? ";
                $record = self::$kernel['db']->fetchAssoc($sql, array($fields['id'])) ?: null;
                foreach ($fields as $key => $value) {
                    if (array_key_exists($key, $record)) {
                        $this->assertEquals($value, $record[$key]);
                    }
                }
            }
        }
    }
}
