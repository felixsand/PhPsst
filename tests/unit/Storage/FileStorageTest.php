<?php
/**
 * PhPsst.
 *
 * @copyright Copyright (c) 2018 Felix Sandström
 * @license   MIT
 */

namespace PhPsstTest\Storage;

use LogicException;
use PhPsst\Password;
use PhPsst\PhPsstException;
use PhPsst\Storage\FileStorage;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @author Felix Sandström <http://github.com/felixsand>
 * @coversDefaultClass \PhPsst\Storage\FileStorage
 */
class FileStorageTest extends TestCase
{
    /**
     * @var string
     */
    private $passwordDirectory;

    public function setUp(): void
    {
        $this->passwordDirectory = sys_get_temp_dir() . '/PhPsstUnitTest';
        mkdir($this->passwordDirectory);
    }

    /**
     * @covers ::__construct
     */
    public function testInvalidContruct(): void
    {
        $this->expectException(LogicException::class);
        new FileStorage($this->passwordDirectory, -1);
    }

    /**
     * @covers ::__construct
     * @covers ::store
     * @covers ::garbageCollection
     * @covers ::writeFile
     * @covers ::getFileName
     * @covers ::getFileNameFromKey
     */
    public function testStore(): void
    {
        $passwordId = uniqid('', false);
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
     * @covers ::__construct
     * @covers ::store
     */
    public function testStoreSameId(): void
    {
        $passwordId = uniqid('', false);
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
     * @covers ::__construct
     */
    public function testInvalidDirPath(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid directory path');
        new FileStorage(sys_get_temp_dir() . '/' . uniqid('', false), 1);
    }

    /**
     * @covers ::__construct
     * @covers ::store
     * @covers ::writeFile
     * @covers ::getFileName
     * @covers ::getFileNameFromKey
     */
    public function testNonWriteableDirectory(): void
    {
        $password = $this->getMockBuilder(Password::class)->disableOriginalConstructor()->getMock();
        $password->expects($this->atLeastOnce())->method('getId')->willReturn(uniqid('', false));

        $invalidDirectory = sys_get_temp_dir() . '/invalidDir' . uniqid('', false);
        mkdir($invalidDirectory, 0);
        $fileStorage = new FileStorage($invalidDirectory, 1);


        $this->expectException(RuntimeException::class);
        /** @var Password $password */
        $fileStorage->store($password);

        rmdir($invalidDirectory);
    }

    /**
     * @covers ::__construct
     * @covers ::store
     * @covers ::garbageCollection
     * @covers ::writeFile
     * @covers ::getFileName
     * @covers ::getFileNameFromKey
     * @covers ::delete
     */
    public function testGarbageCollector(): void
    {
        $passwordId = uniqid('', false);
        $password = $this->getMockBuilder(Password::class)->disableOriginalConstructor()->getMock();
        $password->expects($this->atLeastOnce())->method('getId')->willReturn($passwordId);
        $password->expects($this->atLeastOnce())->method('getJson')->willReturn(json_encode([
            'id' => $passwordId,
            'password' => '',
            'ttl' => 300,
            'ttlTime' => strtotime('+1 sec'),
            'views' => 1
        ]));

        $passwordTwoId = uniqid('', false);
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
     * @covers ::__construct
     * @covers ::store
     * @covers ::garbageCollection
     * @covers ::writeFile
     * @covers ::getFileName
     * @covers ::getFileNameFromKey
     * @covers ::delete
     */
    public function testGarbageCollectorNotRunning(): void
    {
        $passwordId = uniqid('', false);
        $password = $this->getMockBuilder(Password::class)->disableOriginalConstructor()->getMock();
        $password->expects($this->atLeastOnce())->method('getId')->willReturn($passwordId);
        $password->expects($this->atLeastOnce())->method('getJson')->willReturn(json_encode([
            'id' => $passwordId,
            'password' => '',
            'ttl' => 1,
            'ttlTime' => strtotime('+1 sec'),
            'views' => 1
        ]));

        $passwordTwoId = uniqid('', false);
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

    public function tearDown(): void
    {
        array_map('unlink', glob("$this->passwordDirectory/*.phpsst"));
        rmdir($this->passwordDirectory);
    }
}
