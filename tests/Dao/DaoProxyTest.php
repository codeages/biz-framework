<?php

namespace Tests\Dao;

use PHPUnit\Framework\TestCase;
use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Dao\FieldSerializer;
use Codeages\Biz\Framework\Dao\DaoProxy;
use Prophecy\Argument;

class DaoProxyTest extends TestCase
{
    public function testGetWithHitCache()
    {
        $cache = array(
            'id' => 1,
            'name' => 'test',
        );

        $strategy = $this->prophesize('Codeages\Biz\Framework\Dao\CacheStrategy');
        $strategy->beforeGet(
            Argument::type('Codeages\Biz\Framework\Dao\GeneralDaoInterface'),
            Argument::containingString('get'),
            Argument::type('array')
        )->willReturn($cache);

        $dao = $this->prophesize('Codeages\Biz\Framework\Dao\GeneralDaoInterface');

        $serializer = new FieldSerializer();

        $biz = new Biz();
        $biz['dao.cache.first.enabled'] = false;
        $biz['dao.cache.second.enabled'] = true;
        $biz['dao.cache.second.strategy.default'] = $strategy->reveal();

        $proxy = new DaoProxy($biz, $dao->reveal(), $serializer);

        $row1 = $proxy->get($cache['id']);
        $row2 = $proxy->getByName($cache['name']);

        $this->assertEquals($cache['id'], $row1['id']);
        $this->assertEquals($cache['id'], $row2['id']);
    }

    public function testGetWithoutCache()
    {
        $expected = array(
            'id' => 1,
            'name' => 'test',
        );

        $strategy = $this->prophesize('Codeages\Biz\Framework\Dao\CacheStrategy');
        $strategy->beforeGet(
            Argument::type('Codeages\Biz\Framework\Dao\GeneralDaoInterface'),
            Argument::containingString('get'),
            Argument::type('array')
        )->willReturn(false);
        $strategy->afterGet(
            Argument::type('Codeages\Biz\Framework\Dao\GeneralDaoInterface'),
            Argument::containingString('get'),
            Argument::type('array'),
            Argument::type('array')
        )->willReturn(null);

        $dao = $this->prophesize('Codeages\Biz\Framework\Dao\GeneralDaoInterface');
        $dao->declares()->willReturn(array());
        $dao->get($expected['id'])->willReturn($expected);

        $serializer = new FieldSerializer();

        $biz = new Biz();
        $biz['dao.cache.first.enabled'] = false;
        $biz['dao.cache.second.enabled'] = true;
        $biz['dao.cache.second.strategy.default'] = $strategy->reveal();

        $proxy = new DaoProxy($biz, $dao->reveal(), $serializer);

        $row = $proxy->get($expected['id']);

        $this->assertEquals($expected['id'], $row['id']);
    }

    public function testGetWithNoCache()
    {
        $expected = array(
            'id' => 1,
            'name' => 'test',
        );
        
        $dao = $this->prophesize('Codeages\Biz\Framework\Dao\GeneralDaoInterface');
        $dao->declares()->willReturn(array());
        $dao->get($expected['id'])->willReturn($expected);

        $serializer = new FieldSerializer();

        $biz = new Biz();
        $biz['dao.cache.first.enabled'] = false;
        $biz['dao.cache.second.enabled'] = false;

        $proxy = new DaoProxy($biz, $dao->reveal(), $serializer);

        $row = $proxy->get($expected['id']);

        $this->assertEquals($expected['id'], $row['id']);
    }

}