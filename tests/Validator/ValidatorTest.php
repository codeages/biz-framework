<?php

namespace Tests\Validator;

use Codeages\Biz\Framework\Validator\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    public function testValidateWhenValidDataThenPass()
    {
        $v = new Validator();

        $data = [
            'username' => 'guest',
            'email' => 'guest@example.com',
            'age' => 18,
        ];

        $rules = [
            'username' => ['required', ['lengthBetween', 4, 16]],
            'email' => ['required', 'email'],
            'age' => ['required', 'integer', ['min', 18], ['max', 100]],
        ];

        $rules = [
            'username' => ['required', ['lengthBetween', 4, 16]],
            'email' => ['required', 'email'],
            'age' => ['required', 'integer', ['min', 18], ['max', 100]],
        ];

        $validatedData = $v->validate($data, $rules);

        $this->assertEquals($data['username'], $validatedData['username']);
        $this->assertEquals($data['email'], $validatedData['email']);
        $this->assertEquals($data['age'], $validatedData['age']);
    }

    public function testValidateWhenInvalidDataThenThrowException()
    {
        $this->expectException('Codeages\Biz\Framework\Validator\ValidatorException');

        $v = new Validator();

        $data = [
            'username' => 'guest',
            'email' => 'guest@example.com',
            'age' => 12,
        ];

        $rules = [
            'username' => ['required', ['lengthBetween', 4, 16]],
            'email' => ['required', 'email'],
            'age' => ['required', 'integer', ['min', 18], ['max', 100]],
        ];

        $v->validate($data, $rules);
    }

    public function testValidateWhenLessRulesThenFilterData()
    {
        $v = new Validator();

        $data = [
            'username' => 'guest',
            'email' => 'guest@example.com',
            'age' => 18,
        ];

        $rules = [
            'username' => ['required', ['lengthBetween', 4, 16]],
            'email' => ['required', 'email'],
        ];

        $validatedData = $v->validate($data, $rules);

        $this->assertEquals($data['username'], $validatedData['username']);
        $this->assertEquals($data['email'], $validatedData['email']);
        $this->assertNotContains('age', $validatedData);
    }
}
