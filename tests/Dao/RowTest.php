<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Codeages\Biz\Framework\Dao\Row;

class RowTest extends TestCase
{
    public function testArrayFeatures()
    {
        $data = array('id' => 1, 'username' => 'testuser_1', 'email' => 'testuser_1@example.com', 'about' => '');
        $row = new Row($data);

        $this->assertTrue(isset($row['id']));
        $this->assertFalse(isset($row['id2']));
        $this->assertTrue(isset($row['about']));
        $this->assertTrue(empty($row['about']));
        $this->assertEquals(4, count($row));

        // 测试迭代函数
        $this->assertEquals($data['id'], current($row));
        next($row);
        $this->assertEquals($data['username'], current($row));

        // 调用array相关方法时，需强制转换
        $this->assertTrue(is_array((array)$row));

        // 测试数组语法访问值
        $this->assertEquals($data['id'], $row['id']);

        // 测试对已有的key赋值
        $row['id'] = 2;
        $this->assertEquals(2, $row['id']);

        // 测试删除key
        unset($row['id']);
        $this->assertFalse(isset($row['id']));

        // 测试新增key
        $row['id3'] = 3;
        $this->assertEquals(3, $row['id3']);

        // 测试迭代
        $data = array('id' => 1, 'username' => 'testuser_1', 'email' => 'testuser_1@example.com', 'about' => '');
        $row = new Row($data);
        foreach ($row as $key => $value) {
            $this->assertEquals($data[$key], $value);
        }

        $json = '{"id":1,"username":"testuser_1","email":"testuser_1@example.com","about":""}';
        $this->assertEquals($json, json_encode($row));
    }
}
