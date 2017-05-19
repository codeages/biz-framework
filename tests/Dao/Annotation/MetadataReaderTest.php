<?php
namespace Tests\Dao\Annotation;

use Codeages\Biz\Framework\Dao\Annotation\MetadataReader;
use TestProject\Biz\Example\Dao\Impl\ExampleWithAnnotationDaoImpl;
use Tests\BaseTestCase;

class MetadataReaderTest extends BaseTestCase
{

    public function testRead()
    {
        $reader = new MetadataReader();

        $dao = new ExampleWithAnnotationDaoImpl($this->createBiz());

        $metadata = $reader->read($dao);

        var_dump($metadata);
    }
}