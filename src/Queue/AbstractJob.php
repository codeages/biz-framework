<?php
namespace Codeages\Biz\Framework\Queue;

use Codeages\Biz\Framework\Context\Biz;

abstract class AbstractJob implements Job
{
    protected $container;

    protected $body;

    protected $id;

    protected $biz;

    public function __construct($body)
    {
        $this->body = $body;
    }

    public function getId()
    {
        return $this->id;
    }
    
    public function setId($id)
    {
        $this->id = $id;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setBody($body)
    {
        $this->body = $body;
    }

    public function setBiz(Biz $biz)
    {
        $this->biz = $biz;
    }
}