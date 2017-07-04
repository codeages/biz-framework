<?php

namespace Tests;

use Codeages\Biz\Framework\Util\ArrayToolkit;
use Tests\Example\Dao\AdvancedExampleDao;

class AdvancedDaoImplTest extends IntegrationTestCase
{
    public function testBatchCreate()
    {
        $count = 10000;
        $this->createBatchRecord(10000);

        $total = $this->getAdvancedExampleDao()->count(array());

        $this->assertEquals($count, $total);
    }

    public function testBatchUpdate()
    {
        $count = 1000;

        $this->createBatchRecord($count);

        $examples = $this->getAdvancedExampleDao()->search(array(), array(), 0, $count);

        $batchUpdates = array();
        foreach ($examples as $example) {
            $update['name'] = 'change_name_'.$example['id'];
            $update['content'] = 'change_content_'.$example['id'];
            $batchUpdates[] = $update;
        }

        $this->getAdvancedExampleDao()->batchUpdate(ArrayToolkit::column($examples, 'id'), $batchUpdates);

        $examples = $this->getAdvancedExampleDao()->search(array(), array(), 0, $count);

        $this->assertEquals('change_name_'.$examples[0]['id'], $examples[0]['name']);
        $this->assertEquals('change_content_'.$examples[0]['id'], $examples[0]['content']);
        $this->assertEquals('change_name_'.$examples[400]['id'], $examples[400]['name']);
        $this->assertEquals('change_content_'.$examples[400]['id'], $examples[400]['content']);
    }


    private function createBatchRecord($count)
    {
        $news = array();
        for ($i=1; $i<=$count; $i++) {
            $fields = array(
                'name' => 'test'.$i,
                'content' => 'content',
            );
            $news[] = $fields;
        }

        $this->getAdvancedExampleDao()->batchCreate($news);
    }

    /**
     * @return AdvancedExampleDao
     */
    private function getAdvancedExampleDao()
    {
        return $this->biz->dao('Example:AdvancedExampleDao');
    }

}
