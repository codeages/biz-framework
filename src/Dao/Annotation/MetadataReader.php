<?php
namespace Codeages\Biz\Framework\Dao\Annotation;

use Codeages\Biz\Framework\Dao\Annotation\ReadException;
use Doctrine\Common\Annotations\AnnotationReader;

class MetadataReader
{
    public function __construct()
    {

    }

    public function read($dao)
    {
        $reader = new AnnotationReader();
        $classRef = new \ReflectionClass($dao);
        $isDao = $classRef->implementsInterface('Codeages\Biz\Framework\Dao\DaoInterface');
        if (!$isDao) {
            return null;
        }

        $annotation = $reader->getClassAnnotation($classRef, 'Codeages\Biz\Framework\Dao\Annotation\CacheStrategy');
        if (empty($annotation)) {
            return null;
        }

        $metadata = array(
            'strategy' =>  $annotation->getName(),
            'cache_key_of_field_name' => array(),
            'cache_key_of_arg_index' => array(),
            'update_rel_query_methods' => array(),
        );

        $methodRefs = $classRef->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methodRefs as $methodRef) {
            $annotation = $reader->getMethodAnnotation($methodRef, 'Codeages\Biz\Framework\Dao\Annotation\RowCache');
            if (empty($annotation)) {
                continue;
            }

            $args = $this->getMethodArgumentNames($methodRef);
            if (empty($annotation->relFields)) {
                $annotation->relFields = $args;
            }

            $metadata['cache_key_of_field_name'][$methodRef->getName()] = $annotation->relFields;

            $args = array_flip($args);
            foreach ($annotation->relFields as $field) {
                if (empty($metadata['cache_key_of_arg_index'][$methodRef->getName()])) {
                    $metadata['cache_key_of_arg_index'][$methodRef->getName()] = array();
                }
                $metadata['cache_key_of_arg_index'][$methodRef->getName()][] = $args[$field];

                if (empty($metadata['update_rel_query_methods'][$field])) {
                    $metadata['update_rel_query_methods'][$field] = array();
                }
                $metadata['update_rel_query_methods'][$field][] = $methodRef->getName();
            }
        }

        $metadata['cache_key_of_arg_index']['get'] = [0];
        $metadata['cache_key_of_field_name']['get'] = ['id'];

        return $metadata;
    }

    protected function getMethodArgumentNames(\ReflectionMethod $methodRef)
    {
        $args = $methodRef->getParameters();
        $names = array();
        foreach ($args as $arg) {
            $names[] = $arg->getName();
        }
        return $names;
    }
}