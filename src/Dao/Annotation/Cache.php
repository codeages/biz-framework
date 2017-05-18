<?php
namespace Codeages\Biz\Framework\Dao\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
class Cache
{
    private $key;

    /**
     * 定义哪些字段更新时，需要清除当前Cache条目
     */
    public $relFields;

    public function __construct(array $data)
    {
        var_dump($data);exit();
        $this->key = $data['value'];

        if (!empty($data['rel_fields'])) {
            $this->relFields = explode(',', $data['rel_fields']);
        }
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getRelFields()
    {
        return $this->relFields;
    }
}