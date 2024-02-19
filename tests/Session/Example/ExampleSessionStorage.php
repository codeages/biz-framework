<?php

namespace Tests\Session\Example;

use Codeages\Biz\Framework\Session\Storage\SessionStorage;

class ExampleSessionStorage implements SessionStorage
{
    private $biz;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    public function delete($sessId)
    {
        return true;
    }

    public function get($sessId)
    {
        return [
            'sess_id' => 'sess_id',
            'sess_time' => 1707295983,
            'sess_deadline' => 1707295983 + 1000,
            'sess_data' => 'sess_data',
        ];
    }

    public function save($session)
    {
        return $session;
    }

    public function gc()
    {
        return true;
    }
}
