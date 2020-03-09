<?php

namespace Tests\Dao\CacheStrategy;


use Tests\IntegrationTestCase;

class ManualStrategyTest extends IntegrationTestCase
{
    /**
     * @var \Redis
     */
    protected $redis;

    public function setUp()
    {
        parent::setUp();
        $this->redis = $this->biz['redis'];
    }
}
