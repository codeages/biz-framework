<?php

namespace Tests\Cache;

use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Provider\RedisServiceProvider;
use PHPUnit\Framework\TestCase;

class ExampleUserServiceTest extends TestCase
{
    /**
     * @var ExampleUserServiceImpl
     */
    private $exampleUserService;

    public function testGetByIdCached_UserExist()
    {
        $user = $this->exampleUserService->getByIdCached(1);
        $this->assertEquals(1, $user['id']);

        $user = $this->exampleUserService->getByIdCached(1);
        $this->assertEquals(1, $user['id']);
    }

    public function testGetByIdCached_UserNotFound()
    {
        $user = $this->exampleUserService->getByIdCached(999);
        $this->assertNull($user);

        $user = $this->exampleUserService->getByIdCached(999);
        $this->assertNull($user);
    }

    public function testGetByUsernameCached_UserExist()
    {
        $user = $this->exampleUserService->getByUsernameCached("user_1");
        $this->assertEquals('user_1', $user['username']);

        $user = $this->exampleUserService->getByUsernameCached("user_1");
        $this->assertEquals('user_1', $user['username']);
    }

    public function testRegister()
    {
        $user = $this->exampleUserService->register(['username' => 'user_new']);
        $this->assertEquals('user_new', $user['username']);
    }

    public function testChangeUsername()
    {
        $user = $this->exampleUserService->getByIdCached(1);
        $this->assertEquals('user_1', $user['username']);

        $this->exampleUserService->changeUsername(1, 'user_new');

        $user = $this->exampleUserService->getByIdCached(1);
        $this->assertEquals('user_new', $user['username']);
    }

    public function testGetTop10LatestLoginUsers()
    {
        $users = $this->exampleUserService->getTop10LatestLoginUsers();
        $this->assertCount(3, $users);

        $users = $this->exampleUserService->getTop10LatestLoginUsers();
        $this->assertCount(3, $users);
    }

    public function setUp()
    {
        $options = [
            'redis.options' => [
                'host' => getenv('REDIS_HOST'),
            ],
        ];
        $biz = new Biz($options);
        $biz->register(new RedisServiceProvider());
        $this->biz = $biz;

        $this->biz['redis']->flushDB();

        $this->exampleUserService = new ExampleUserServiceImpl($biz);
    }

    public function tearDown()
    {
        $this->biz['redis']->close();
        unset($this->biz);
    }
}