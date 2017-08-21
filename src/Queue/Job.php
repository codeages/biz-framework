<?php
namespace Codeages\Biz\Framework\Queue;
use Codeages\Biz\Framework\Context\Biz;

interface Job
{
    public function execute();

    public function getId();

    public function setId($id);

    public function getBody();

    public function setBody($body);

    public function setBiz(Biz $biz);
}
