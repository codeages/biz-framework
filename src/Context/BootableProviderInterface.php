<?php

namespace Codeages\Biz\Framework\Context;

use Codeages\Biz\Framework\Context\Biz;

interface BootableProviderInterface
{
    public function boot(Biz $biz);
}