<?php
namespace Codeages\PhalconBiz;

use Codeages\Biz\Framework\Context\Biz;

interface BizAwareInterface
{
    public function setBiz(Biz $biz);
}