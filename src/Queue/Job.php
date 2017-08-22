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

    public function getMetadata($key = null, $default = null);

    public function setMetadata($spec = null, $value = null);

    public function setBiz(Biz $biz);
}
