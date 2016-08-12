<?php

namespace Codeages\Biz\Framework\Service;

use Codeages\Biz\Framework\Context\Kernel;

abstract class BaseService
{
    protected $biz;

    public function __construct(Kernel $biz)
    {
        $this->biz = $biz;
    }
}
