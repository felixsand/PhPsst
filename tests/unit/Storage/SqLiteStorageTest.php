<?php
/**
 * PhPsst.
 *
 * @copyright Copyright (c) 2016 Felix Sandström
 * @license   MIT
 */

namespace PhPsstTest\Storage;

use LogicException;
use PhPsst\Password;
use PhPsst\PhPsstException;
use PhPsst\Storage\SqLiteStorage;
use PHPUnit\Framework\TestCase;
use SQLite3;

/**
 * @author Felix Sandström <http://github.com/felixsand>
 * @coversDefaultClass \PhPsst\Storage\SqLiteStorage
 */
class SqLiteStorageTest extends TestCase
{
    /**
     * @covers ::__construct
     */
    public function testContruct()
    {
        $db = new SQLite3(':memory:');
        $storage = new SqLiteStorage($db, 1);
        $this->assertInstanceOf(SqLiteStorage::class, $storage);
    }

    /**
     * @covers ::__construct
     */
    public function testConstructException()
    {
        $db = new SQLite3(':memory:');
        $this->expectException(LogicException::class);
        new SqLiteStorage($db, -1);
    }

    /**
     * @covers ::store
     * @covers ::garbageCollection
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
     * @covers ::store
     * @covers ::garbageCollection
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
     * @covers ::store
     * @covers ::garbageCollection
     */
    public function testStoreSameIdAllowed()
    {
        $db = new SQLite3(':memory:');
        $storage = new SqLiteStorage($db, 0);

        $passwordId = uniqid();
        $ttl = strtotime('+1 hour');
        $password = $this->getMockBuilder(Password::class)->disableOriginalConstructor()
            ->setMethods(['getId', 'getTtl', 'getViews', 'getPassword'])->getMock();
        $password->expects($this->atLeastOnce())->method('getId')->willReturn($passwordId);
        $password->expects($this->atLeastOnce())->method('getTtl')->willReturn($ttl);
        $password->expects($this->atLeastOnce())->method('getViews')->willReturn(10);
        $password2 = clone $password;

        $password->expects($this->atLeastOnce())->method('getPassword')->willReturn('password');
        $password2->expects($this->atLeastOnce())->method('getPassword')->willReturn('password2');
        /* @var Password $password */
        /* @var Password $password2 */

        $storage->store($password);
        $this->assertEquals('password', $storage->get($passwordId)->getPassword());


        $storage->store($password2, true);

        $this->assertEquals('password2', $storage->get($passwordId)->getPassword());
    }

    /**
     * @covers ::store
     * @covers ::garbageCollection
     * @covers ::get
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
     * @covers ::store
     * @covers ::garbageCollection
     * @covers ::get
     * @covers ::delete
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
     * @covers ::store
     * @covers ::garbageCollection
     * @covers ::get
     * @covers ::delete
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
     * @covers ::store
     * @covers ::get
     * @covers ::garbageCollection
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
