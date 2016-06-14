<?php
/**
 * PhPsst.
 *
 * @copyright Copyright (c) 2016 Felix Sandström
 * @license   MIT
 */

namespace PhPsst\Storage;

use PhPsst\Password;

/**
 * @author Felix Sandström <http://github.com/felixsand>
 */
class RedisStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers PhPsst\Storage\RedisStorage::__construct
     */
    public function testContruct()
    {
        $clientMock = $this->getMockBuilder('Predis\Client')->setMethods(['set'])->getMock();
        $redisStorage = new RedisStorage($clientMock);

        $this->assertInstanceOf('PhPsst\Storage\RedisStorage', $redisStorage);
    }

    /**
     * @covers PhPsst\Storage\RedisStorage::store
     */
    public function testStore()
    {
        $clientMock = $this->getMockBuilder('Predis\Client')
            ->disableProxyingToOriginalMethods()->setMethods(['set','get'])->getMock();
        $clientMock->expects($this->once())->method('get')->willReturn(null);
        $clientMock->expects($this->once())->method('set')->willReturn(null);
        $redisStorage = new RedisStorage($clientMock);

        $password = $this->getMockBuilder('PhPsst\Password')->disableOriginalConstructor()->getMock();
        $password->expects($this->atLeastOnce())->method('getId');

        $redisStorage->store($password);
    }

    /**
     * @covers PhPsst\Storage\RedisStorage::store
     */
    public function testStoreKeyExists()
    {
        $password = new Password('secretId', 'password', 300, 3);

        $clientMock = $this->getMockBuilder('Predis\Client')
            ->disableProxyingToOriginalMethods()->setMethods(['set','get'])->getMock();
        $clientMock->expects($this->once())->method('get')->willReturn($password->getJson());
        $redisStorage = new RedisStorage($clientMock);

        $password = $this->getMockBuilder('PhPsst\Password')->disableOriginalConstructor()->getMock();
        $password->expects($this->atLeastOnce())->method('getId');

        $this->expectException('PhPsst\PhPsstException');
        $redisStorage->store($password);
    }

    /**
     * @covers PhPsst\Storage\RedisStorage::get
     */
    public function testGet()
    {
        $clientMock = $this->getMockBuilder('Predis\Client')->setMethods(['get'])->getMock();
        $clientMock->expects($this->once())->method('get');
        $redisStorage = new RedisStorage($clientMock);

        $redisStorage->get('secretKey');
    }

    /**
     * @covers PhPsst\Storage\RedisStorage::delete
     */
    public function testDelete()
    {
        $clientMock = $this->getMockBuilder('Predis\Client')->setMethods(['del'])->getMock();
        $clientMock->expects($this->once())->method('del');
        $redisStorage = new RedisStorage($clientMock);

        $password = $this->getMockBuilder('PhPsst\Password')->disableOriginalConstructor()->getMock();
        $password->expects($this->once())->method('getId');
        $redisStorage->delete($password);
    }
}
