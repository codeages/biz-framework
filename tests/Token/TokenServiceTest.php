<?php

namespace Tests\Token;

use Codeages\Biz\Framework\Token\Service\TokenService;
use Tests\IntegrationTestCase;

class TokenServiceTest extends IntegrationTestCase
{
    public function testGenerate_Default()
    {
        $token = $this->getTokenService()->generate('unit_test', 0);
        $this->assertEquals('unit_test', $token['place']);
        $this->assertEquals(0, $token['times']);
        $this->assertEquals(0, $token['expired_time']);
        $this->assertArrayHasKey('key', $token);
    }

    public function testGenerate_Limited()
    {
        $token = $this->getTokenService()->generate('unit_test', 3600, 2);
        $this->assertEquals('unit_test', $token['place']);
        $this->assertEquals(2, $token['times']);
        $this->assertGreaterThanOrEqual(time()+3599, $token['expired_time']);
        $this->assertArrayHasKey('key', $token);
    }

    public function testVerify_NoExpired()
    {
        $tokens = $this->seed('Tests\Token\TokenSeeder');

        $expectedToken = $tokens->filter(function($token) {
            return $token['_key'] == 'unit_test_key_no_expired';
        })->first();

        $verified = $this->getTokenService()->verify('unit_test', 'unit_test_key_no_expired');

        $this->assertEquals($expectedToken['_key'], $verified['key']);
    }

    public function testVerify_Expired()
    {
        $this->seed('Tests\Token\TokenSeeder');
        $verified = $this->getTokenService()->verify('unit_test', 'unit_test_key_zero_remaining_times');
        $this->assertFalse($verified);
    }

    public function testVerify_TimesLimit()
    {
        $this->seed('Tests\Token\TokenSeeder');

        $verified1 = $this->getTokenService()->verify('unit_test', 'unit_test_key_2_times');
        $verified2 = $this->getTokenService()->verify('unit_test', 'unit_test_key_2_times');
        $verified3 = $this->getTokenService()->verify('unit_test', 'unit_test_key_2_times');

        $this->assertEquals('unit_test_key_2_times', $verified1['key']);
        $this->assertEquals(1, $verified1['remaining_times']);

        $this->assertEquals('unit_test_key_2_times', $verified2['key']);
        $this->assertEquals(0, $verified2['remaining_times']);

        $this->assertFalse($verified3);
    }

    public function testVerify_NoTimesLimit()
    {
        $this->seed('Tests\Token\TokenSeeder');

        for ($i=0; $i<100; $i++) {
            $verified = $this->getTokenService()->verify('unit_test', 'unit_test_key');
            $this->assertEquals('unit_test_key', $verified['key']);
        }
    }

    public function testGenerate_HaveData()
    {
        $data = 1;
        $token = $this->getTokenService()->generate('unit_test', 0, 0, $data);
        $this->assertEquals($data, $token['data']);

        $data = 'string';
        $token = $this->getTokenService()->generate('unit_test', 0, 0, $data);
        $this->assertEquals($data, $token['data']);

        $data = array('id' => 1);
        $token = $this->getTokenService()->generate('unit_test', 0, 0, $data);
        $this->assertEquals($data, $token['data']);
    }

    public function testGenerate_DifferentPlace()
    {
        $this->seed('Tests\Token\TokenSeeder');

        $verified = $this->getTokenService()->verify('unit_test_different_place', 'unit_test_key');
        $this->assertFalse($verified);
    }

    /**
     * @var TokenService
     */
    protected function getTokenService()
    {
        return $this->biz->service('Token:TokenService');
    }
}