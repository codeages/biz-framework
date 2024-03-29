<?php

namespace Tests\Setting;

use Codeages\Biz\Framework\Service\Exception\ServiceException;
use Tests\IntegrationTestCase;

class SettingServiceTest extends IntegrationTestCase
{
    public function testGet()
    {
        $this->seed('Tests\\Setting\\SettingSeeder');

        $value = $this->getSettingService()->get('with_array_value');
        $this->assertTrue(is_array($value));

        $value = $this->getSettingService()->get('with_string_value');
        $this->assertTrue(is_string($value));

        $value = $this->getSettingService()->get('with_int_value');
        $this->assertTrue(is_int($value));

        $value = $this->getSettingService()->get('error_key');
        $this->assertNull($value);

        $value = $this->getSettingService()->get('error_key', 'default value');
        $this->assertEquals('default value', $value);
    }

    public function testGetWithDot()
    {
        $this->seed('Tests\\Setting\\SettingSeeder');

        $value = $this->getSettingService()->get('dot_key.subkey');
        $this->assertEquals('value', $value);

        $value = $this->getSettingService()->get('dot_key.error_subkey');
        $this->assertNull($value);

        $value = $this->getSettingService()->get('dot_key.error_subkey', 'default value');
        $this->assertEquals('default value', $value);

        $value = $this->getSettingService()->get('error_dot_key.subkey');
        $this->assertNull($value);

        $value = $this->getSettingService()->get('error_dot_key.subkey', 'default value');
        $this->assertEquals('default value', $value);
    }

    public function testSetNoDot()
    {
        $this->seed('Tests\\Setting\\SettingSeeder');

        $this->getSettingService()->set('with_array_value', [
            'new_key' => 'new_value',
        ]);

        $value = $this->getSettingService()->get('with_array_value');
        $this->assertEquals('new_value', $value['new_key']);

        $this->getSettingService()->set('with_array_value2', [
            'new_key' => 'new_value',
        ]);

        $value = $this->getSettingService()->get('with_array_value2');
        $this->assertEquals('new_value', $value['new_key']);
    }

    public function testSetWithDot()
    {
        $this->seed('Tests\\Setting\\SettingSeeder');

        $this->getSettingService()->set('with_array_value.key1', 'new value');
        $this->getSettingService()->set('with_array_value.new_key', 'new key value');
        $value = $this->getSettingService()->get('with_array_value');
        $this->assertEquals('new value', $value['key1']);
        $this->assertEquals('new key value', $value['new_key']);
        $this->assertEquals('value2', $value['key2']);

        $this->getSettingService()->set('new_key.subkey', 'new value');
        $value = $this->getSettingService()->get('new_key');
        $this->assertTrue(is_array($value));
        $this->assertEquals('new value', $value['subkey']);
    }

    public function testSetWithDotInvalidKey()
    {
        $this->expectException(ServiceException::class);
        $this->seed('Tests\\Setting\\SettingSeeder');

        $this->getSettingService()->set('with_string_value.subkey', 'new value');
    }

    public function testRemove()
    {
        $this->seed('Tests\\Setting\\SettingSeeder');

        $this->getSettingService()->remove('with_array_value');
        $value = $this->getSettingService()->get('with_array_value');
        $this->assertNull($value);
    }

    public function testRemoveErrorKey()
    {
        $this->expectException(ServiceException::class);
        $this->seed('Tests\\Setting\\SettingSeeder');

        $this->getSettingService()->remove('error_key');
    }

    public function testRemoveWithDot()
    {
        $this->seed('Tests\\Setting\\SettingSeeder');

        $this->getSettingService()->remove('with_array_value.key1');
        $value = $this->getSettingService()->get('with_array_value');
        $this->assertFalse(isset($value['key1']));
        $this->assertEquals('value2', $value['key2']);
    }

    public function testRemoveWithDotErrorSubKey()
    {
        $this->expectException(ServiceException::class);
        $this->seed('Tests\\Setting\\SettingSeeder');

        $this->getSettingService()->remove('with_array_value.error_key');
    }

    public function testRemoveWithDotInvalidKeyValueType()
    {
        $this->expectException(ServiceException::class);
        $this->seed('Tests\\Setting\\SettingSeeder');

        $this->getSettingService()->remove('with_string_value.subkey');
    }

    protected function getSettingService()
    {
        return $this->biz->service('Setting:SettingService');
    }
}
