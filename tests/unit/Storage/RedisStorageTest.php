<?php
/**
 * PhPsst.
 *
 * @copyright Copyright (c) 2016 Felix Sandström
 * @license   MIT
 */

namespace PhPsst\Storage;

use PhPsst\Password;
use PhPsst\PhPsstException;
use Predis\Client;

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
        $clientMock = $this->getMockBuilder(Client::class)->getMock();
        /* @var Client $clientMock */

        $redisStorage = new RedisStorage($clientMock);

        $this->assertInstanceOf(RedisStorage::class, $redisStorage);
    }

    /**
     * @covers PhPsst\Storage\RedisStorage::store
     */
    public function testStore()
    {
        $clientMock = $this->getMockBuilder(Client::class)
            ->disableProxyingToOriginalMethods()->setMethods(['set','get', 'expireat'])->getMock();
        $clientMock->expects($this->once())->method('get')->willReturn(null);
        $clientMock->expects($this->once())->method('set')->willReturn(null);
        $clientMock->expects($this->once())->method('expireat')->willReturn(null);
        /* @var Client $clientMock */

        $redisStorage = new RedisStorage($clientMock);

        $password = $this->getMockBuilder(Password::class)->disableOriginalConstructor()->getMock();
        $password->expects($this->atLeastOnce())->method('getId');
        /* @var Password $password */

        $redisStorage->store($password);
    }

    /**
     * @covers PhPsst\Storage\RedisStorage::store
     */
    public function testStoreKeyExists()
    {
        //$password = new Password('secretId', 'password', strtotime('+3600'), 3);

        $clientMock = $this->getMockBuilder(Client::class)
            ->disableProxyingToOriginalMethods()->setMethods(['set','get', 'expireat'])->getMock();
        $clientMock->expects($this->once())->method('get')->willReturn(json_encode([
            'id' => 'secretId',
            'password' => 'password',
            'ttl' => strtotime('+1 hour'),
            'views' => 1
        ]));
        /* @var Client $clientMock */

        $redisStorage = new RedisStorage($clientMock);

        $password = $this->getMockBuilder(Password::class)->disableOriginalConstructor()
            ->setMethods(['getId', 'getTtl'])->getMock();
        $password->expects($this->atLeastOnce())->method('getId')->willReturn('secretId');
        /* @var Password $password */

        $this->expectException(PhPsstException::class);
        $redisStorage->store($password);
    }

    /**
     * @covers PhPsst\Storage\RedisStorage::get
     */
    public function testGet()
    {
        $passwordId = uniqid();
        $clientMock = $this->getMockBuilder(Client::class)->setMethods(['get'])->getMock();
        $clientMock->expects($this->once())->method('get')->willReturn(json_encode([
            'id' => $passwordId,
            'password' => 'password',
            'ttl' => strtotime('+1 hour'),
            'views' => 10
        ]));
        /* @var Client $clientMock */

        $redisStorage = new RedisStorage($clientMock);

        $this->assertEquals($passwordId, $redisStorage->get('secretKey')->getId());
    }

    /**
     * @covers PhPsst\Storage\RedisStorage::delete
     */
    public function testDelete()
    {
        $clientMock = $this->getMockBuilder(Client::class)->setMethods(['del'])->getMock();
        $clientMock->expects($this->once())->method('del');
        /* @var Client $clientMock */

        $redisStorage = new RedisStorage($clientMock);

        $password = $this->getMockBuilder(Password::class)->disableOriginalConstructor()->getMock();
        $password->expects($this->once())->method('getId');
        /* @var Password $password */

        $redisStorage->delete($password);
    }
}
