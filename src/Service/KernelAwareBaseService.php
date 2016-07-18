<?php

namespace Codeages\Biz\Framework\Service;

abstract class KernelAwareBaseService
{
    protected $kernle;

    public function __construct($kernel)
    {
        $this->kernel = $kernel;
    }
}
