<?php
/**
 * PhPsst.
 *
 * @copyright Copyright (c) 2016 Felix Sandström
 * @license   MIT
 */

namespace PhPsst\Storage;

use LogicException;
use PhPsst\Password;
use PhPsst\PhPsstException;
use SQLite3;

/**
 * @author Felix Sandström <http://github.com/felixsand>
 * @group failing
 */
class SqLiteStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers PhPsst\Storage\SqLiteStorage::__construct
     */
    public function testContruct()
    {
        $db = $this->getMockBuilder(SQLite3::class)->disableOriginalConstructor()->getMock();
        /* @var SQLite3 $db */

        $storage = new SqLiteStorage($db, 1);
        $this->assertInstanceOf(SqLiteStorage::class, $storage);
    }

    /**
     * @covers PhPsst\Storage\SqLiteStorage::__construct
     */
    public function testConstructException()
    {
        $db = $this->getMockBuilder(SQLite3::class)->disableOriginalConstructor()->getMock();
        /* @var SQLite3 $db */

        $this->expectException(LogicException::class);
        $storage = new SqLiteStorage($db, -1);
    }

    /**
     * @covers PhPsst\Storage\SqLiteStorage::store
     * @covers PhPsst\Storage\SqLiteStorage::garbageCollection
     */
    public function testStore()
    {
        $db = new SQLite3(':memory:');
        $storage = new SqLiteStorage($db, 0);

        $passwordId = uniqid();
        $ttl = strtotime('+1 hour');
        $password = $this->getMockBuilder(Password::class)->disableOriginalConstructor()
            ->setMethods(['getId', 'getTtl', 'getViews', 'getPassword'])->getMock();
        $password->expects($this->atLeastOnce())->method('getId')->willReturn($passwordId);
        $password->expects($this->atLeastOnce())->method('getTtl')->willReturn($ttl);
        $password->expects($this->atLeastOnce())->method('getPassword')->willReturn('password');
        $password->expects($this->atLeastOnce())->method('getViews')->willReturn(10);
        /* @var Password $password */

        $storage->store($password);

        $stmt = $db->prepare('SELECT * FROM phPsst WHERE ID = :id');
        $stmt->bindValue(':id', $passwordId, SQLITE3_TEXT);
        $result = $stmt->execute();
        $row = $result->fetchArray();

        $this->assertEquals($passwordId, $row['id']);
        $this->assertEquals($ttl, $row['ttl']);
        $this->assertEquals('password', $row['password']);
        $this->assertEquals(10, $row['views']);
    }

    /**
     * @covers PhPsst\Storage\SqLiteStorage::store
     * @covers PhPsst\Storage\SqLiteStorage::garbageCollection
     */
    public function testStoreSameId()
    {
        $db = new SQLite3(':memory:');
        $storage = new SqLiteStorage($db, 0);

        $passwordId = uniqid();
        $ttl = strtotime('+1 hour');
        $password = $this->getMockBuilder(Password::class)->disableOriginalConstructor()
            ->setMethods(['getId', 'getTtl', 'getViews', 'getPassword'])->getMock();
        $password->expects($this->atLeastOnce())->method('getId')->willReturn($passwordId);
        $password->expects($this->atLeastOnce())->method('getTtl')->willReturn($ttl);
        $password->expects($this->atLeastOnce())->method('getPassword')->willReturn('password');
        $password->expects($this->atLeastOnce())->method('getViews')->willReturn(10);
        /* @var Password $password */

        $storage->store($password);

        $this->expectException(PhPsstException::class);
        $this->expectExceptionCode(PhPsstException::ID_IS_ALREADY_TAKEN);
        $storage->store($password);
    }

    /**
     * @covers PhPsst\Storage\SqLiteStorage::store
     * @covers PhPsst\Storage\SqLiteStorage::garbageCollection
     * @covers PhPsst\Storage\SqLiteStorage::get
     */
    public function testGet()
    {
        $db = new SQLite3(':memory:');
        $storage = new SqLiteStorage($db, 1);

        $passwordId = uniqid();
        $ttl = strtotime('+1 hour');
        $password = $this->getMockBuilder(Password::class)->disableOriginalConstructor()
            ->setMethods(['getId', 'getTtl', 'getViews', 'getPassword'])->getMock();
        $password->expects($this->atLeastOnce())->method('getId')->willReturn($passwordId);
        $password->expects($this->atLeastOnce())->method('getTtl')->willReturn($ttl);
        $password->expects($this->atLeastOnce())->method('getPassword')->willReturn('password');
        $password->expects($this->atLeastOnce())->method('getViews')->willReturn(10);
        /* @var Password $password */

        $storage->store($password);

        $retrievedPassword = $storage->get($passwordId);
        $this->assertEquals($passwordId, $retrievedPassword->getId());
        $this->assertEquals($ttl, $retrievedPassword->getTtl());
        $this->assertEquals('password', $retrievedPassword->getPassword());
        $this->assertEquals(10, $retrievedPassword->getViews());
    }

    /**
     * @covers PhPsst\Storage\SqLiteStorage::store
     * @covers PhPsst\Storage\SqLiteStorage::garbageCollection
     * @covers PhPsst\Storage\SqLiteStorage::get
     * @covers PhPsst\Storage\SqLiteStorage::delete
     */
    public function testDelete()
    {
        $db = new SQLite3(':memory:');
        $storage = new SqLiteStorage($db, 1);

        $passwordId = uniqid();
        $ttl = strtotime('+1 hour');
        $password = $this->getMockBuilder(Password::class)->disableOriginalConstructor()
            ->setMethods(['getId', 'getTtl', 'getViews', 'getPassword'])->getMock();
        $password->expects($this->atLeastOnce())->method('getId')->willReturn($passwordId);
        $password->expects($this->atLeastOnce())->method('getTtl')->willReturn($ttl);
        $password->expects($this->atLeastOnce())->method('getPassword')->willReturn('password');
        $password->expects($this->atLeastOnce())->method('getViews')->willReturn(10);
        /* @var Password $password */

        $storage->store($password);
        $this->assertNotEmpty($storage->get($passwordId));

        $storage->delete($password);
        $this->assertEmpty($storage->get($passwordId));
    }

    /**
     * @covers PhPsst\Storage\SqLiteStorage::store
     * @covers PhPsst\Storage\SqLiteStorage::garbageCollection
     * @covers PhPsst\Storage\SqLiteStorage::get
     * @covers PhPsst\Storage\SqLiteStorage::delete
     */
    public function testDeleteOtherId()
    {
        $db = new SQLite3(':memory:');
        $storage = new SqLiteStorage($db, 1);

        $passwordId = uniqid();
        $ttl = strtotime('+1 hour');
        $password = $this->getMockBuilder(Password::class)->disableOriginalConstructor()
            ->setMethods(['getId', 'getTtl', 'getViews', 'getPassword'])->getMock();
        $password->expects($this->atLeastOnce())->method('getId')->willReturn($passwordId);
        $password->expects($this->atLeastOnce())->method('getTtl')->willReturn($ttl);
        $password->expects($this->atLeastOnce())->method('getPassword')->willReturn('password');
        $password->expects($this->atLeastOnce())->method('getViews')->willReturn(10);
        /* @var Password $password */

        $storage->store($password);
        $this->assertNotEmpty($storage->get($passwordId));

        $nonExistingPassword = $this->getMockBuilder(Password::class)->disableOriginalConstructor()
            ->setMethods(['getId'])->getMock();
        $nonExistingPassword->expects($this->atLeastOnce())->method('getId')->willReturn(uniqid());
        /* @var Password $nonExistingPassword */
        $storage->delete($nonExistingPassword);

        $this->assertNotEmpty($storage->get($passwordId));
    }

    /**
     * @covers PhPsst\Storage\SqLiteStorage::store
     * @covers PhPsst\Storage\SqLiteStorage::get
     * @covers PhPsst\Storage\SqLiteStorage::garbageCollection
     */
    public function testGarbageCollection()
    {
        $db = new SQLite3(':memory:');
        $storage = new SqLiteStorage($db, 1);

        $passwordId = uniqid();
        $ttl = strtotime('+1 second');
        $password = $this->getMockBuilder(Password::class)->disableOriginalConstructor()
            ->setMethods(['getId', 'getTtl', 'getViews', 'getPassword'])->getMock();
        $password->expects($this->atLeastOnce())->method('getId')->willReturn($passwordId);
        $password->expects($this->atLeastOnce())->method('getTtl')->willReturn($ttl);
        $password->expects($this->atLeastOnce())->method('getPassword')->willReturn('password');
        $password->expects($this->atLeastOnce())->method('getViews')->willReturn(10);
        /* @var Password $password */

        $storage->store($password);
        $this->assertNotEmpty($storage->get($passwordId));

        sleep(2);
        // Run the store again, this should trigger the GC
        $storage->store($password, true);

        $this->assertEmpty($storage->get($passwordId));
    }
}
