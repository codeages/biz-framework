<?php
namespace Codeages\Biz\Framework\Dao\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
class Cache
{
    private $key;

    public function __construct(array $data)
    {
        $this->key = $data['value'];
    }
}