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
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @author Felix Sandström <http://github.com/felixsand>
 */
class FileStorageTest extends TestCase
{
    /**
     * @var string
     */
    private $passwordDirectory;

    /**
     */
    public function setUp()
    {
        $this->passwordDirectory = sys_get_temp_dir() . '/PhPsstUnitTest';
        mkdir($this->passwordDirectory);
    }

    /**
     * @covers PhPsst\Storage\FileStorage::__construct
     */
    public function testInvalidContruct()
    {
        $this->expectException(LogicException::class);
        new FileStorage($this->passwordDirectory, -1);
    }

    /**
     * @covers PhPsst\Storage\FileStorage::__construct
     * @covers PhPsst\Storage\FileStorage::store
     * @covers PhPsst\Storage\FileStorage::garbageCollection
     * @covers PhPsst\Storage\FileStorage::writeFile
     * @covers PhPsst\Storage\FileStorage::getFileName
     * @covers PhPsst\Storage\FileStorage::getFileNameFromKey
     */
    public function testStore()
    {
        $passwordId = uniqid();
        $password = $this->getMockBuilder(Password::class)->disableOriginalConstructor()->getMock();
        $password->expects($this->atLeast(2))->method('getId')->willReturn($passwordId);
        $password->expects($this->atLeast(2))->method('getJson')->willReturn(json_encode([
            'id' => $passwordId,
            'password' => '',
            'ttl' => strtotime('+1 hour'),
            'ttlTime' => strtotime('+1 hour'),
            'views' => 1
        ]));

        $fileStorage = new FileStorage($this->passwordDirectory, 1);
        /** @var Password $password */
        $fileStorage->store($password);
        $fileStorage->store($password, true);
    }

    /**
     * @covers PhPsst\Storage\FileStorage::__construct
     * @covers PhPsst\Storage\FileStorage::store
     */
    public function testStoreSameId()
    {
        $passwordId = uniqid();
        $password = $this->getMockBuilder(Password::class)->disableOriginalConstructor()->getMock();
        $password->expects($this->atLeastOnce())->method('getId')->willReturn($passwordId);
        $password->expects($this->atLeastOnce())->method('getJson')->willReturn(json_encode([
            'id' => $passwordId,
            'password' => '',
            'ttl' => strtotime('+1 hour'),
            'ttlTime' => strtotime('+1 hour'),
            'views' => 1
        ]));

        $fileStorage = new FileStorage($this->passwordDirectory, 1);
        /** @var Password $password */
        $fileStorage->store($password);

        $this->expectException(PhPsstException::class);
        $this->expectExceptionCode(PhPsstException::ID_IS_ALREADY_TAKEN);
        $fileStorage->store($password);
    }

    /**
     * @covers PhPsst\Storage\FileStorage::__construct
     */
    public function testInvalidDirPath()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid directory path');
        new FileStorage(sys_get_temp_dir() . '/' . uniqid(), 1);
    }

    /**
     * @covers PhPsst\Storage\FileStorage::__construct
     * @covers PhPsst\Storage\FileStorage::store
     * @covers PhPsst\Storage\FileStorage::writeFile
     * @covers PhPsst\Storage\FileStorage::getFileName
     * @covers PhPsst\Storage\FileStorage::getFileNameFromKey
     */
    public function testNonWriteableDirectory()
    {
        $password = $this->getMockBuilder(Password::class)->disableOriginalConstructor()->getMock();
        $password->expects($this->atLeastOnce())->method('getId')->willReturn(uniqid());

        $invalidDirectory = sys_get_temp_dir() . '/invalidDir' . uniqid();
        mkdir($invalidDirectory, 0);
        $fileStorage = new FileStorage($invalidDirectory, 1);


        $this->expectException(RuntimeException::class);
        /** @var Password $password */
        $fileStorage->store($password);

        rmdir($invalidDirectory);
    }

    /**
     * @covers PhPsst\Storage\FileStorage::__construct
     * @covers PhPsst\Storage\FileStorage::store
     * @covers PhPsst\Storage\FileStorage::garbageCollection
     * @covers PhPsst\Storage\FileStorage::writeFile
     * @covers PhPsst\Storage\FileStorage::getFileName
     * @covers PhPsst\Storage\FileStorage::getFileNameFromKey
     * @covers PhPsst\Storage\FileStorage::delete
     */
    public function testGarbageCollector()
    {
        $passwordId = uniqid();
        $password = $this->getMockBuilder(Password::class)->disableOriginalConstructor()->getMock();
        $password->expects($this->atLeastOnce())->method('getId')->willReturn($passwordId);
        $password->expects($this->atLeastOnce())->method('getJson')->willReturn(json_encode([
            'id' => $passwordId,
            'password' => '',
            'ttl' => 300,
            'ttlTime' => strtotime('+1 sec'),
            'views' => 1
        ]));

        $passwordTwoId = uniqid();
        $passwordTwo = $this->getMockBuilder(Password::class)->disableOriginalConstructor()->getMock();
        $passwordTwo->expects($this->atLeastOnce())->method('getId')->willReturn($passwordTwoId);
        $passwordTwo->expects($this->atLeastOnce())->method('getJson')->willReturn(json_encode([
            'id' => $passwordTwoId,
            'password' => '',
            'ttl' => strtotime('+1 hour'),
            'ttlTime' => strtotime('+1 hour'),
            'views' => 1
        ]));
        /** @var Password $password */
        /** @var Password $passwordTwo */

        $fileStorage = new FileStorage($this->passwordDirectory, 1);
        $fileStorage->store($password);
        sleep(2);
        $fileStorage->store($passwordTwo);

        // Since the GC should have run the file with the same ID should not exist anymore
        $fileStorage->store($password);
    }

    /**
     * @covers PhPsst\Storage\FileStorage::__construct
     * @covers PhPsst\Storage\FileStorage::store
     * @covers PhPsst\Storage\FileStorage::garbageCollection
     * @covers PhPsst\Storage\FileStorage::writeFile
     * @covers PhPsst\Storage\FileStorage::getFileName
     * @covers PhPsst\Storage\FileStorage::getFileNameFromKey
     * @covers PhPsst\Storage\FileStorage::delete
     */
    public function testGarbageCollectorNotRunning()
    {
        $passwordId = uniqid();
        $password = $this->getMockBuilder(Password::class)->disableOriginalConstructor()->getMock();
        $password->expects($this->atLeastOnce())->method('getId')->willReturn($passwordId);
        $password->expects($this->atLeastOnce())->method('getJson')->willReturn(json_encode([
            'id' => $passwordId,
            'password' => '',
            'ttl' => 1,
            'ttlTime' => strtotime('+1 sec'),
            'views' => 1
        ]));

        $passwordTwoId = uniqid();
        $passwordTwo = $this->getMockBuilder(Password::class)->disableOriginalConstructor()->getMock();
        $passwordTwo->expects($this->atLeastOnce())->method('getId')->willReturn($passwordTwoId);
        $passwordTwo->expects($this->atLeastOnce())->method('getJson')->willReturn(json_encode([
            'id' => $passwordTwoId,
            'password' => '',
            'ttl' => strtotime('+1 hour'),
            'ttlTime' => strtotime('+1 hour'),
            'views' => 1
        ]));
        /** @var Password $password */
        /** @var Password $passwordTwo */

        $fileStorage = new FileStorage($this->passwordDirectory, 0);
        $fileStorage->store($password);
        sleep(2);
        $fileStorage->store($passwordTwo);

        // Since the GC should NOT have run the file with the same ID should not exist anymore
        $this->expectException(PhPsstException::class);
        $this->expectExceptionCode(PhPsstException::ID_IS_ALREADY_TAKEN);
        $fileStorage->store($password);
    }

    /**
     */
    public function tearDown()
    {
        array_map('unlink', glob("$this->passwordDirectory/*.phpsst"));
        rmdir($this->passwordDirectory);
    }
}
