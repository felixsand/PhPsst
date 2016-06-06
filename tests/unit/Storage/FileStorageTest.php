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

/**
 * @author Felix Sandström <http://github.com/felixsand>
 */
class FileStorageTest extends \PHPUnit_Framework_TestCase
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
     * @covers PhPsst\Storage\FileStorage::insert
     * @covers PhPsst\Storage\FileStorage::garbageCollection
     * @covers PhPsst\Storage\FileStorage::writeFile
     * @covers PhPsst\Storage\FileStorage::getFileName
     * @covers PhPsst\Storage\FileStorage::getFileNameFromKey
     * @covers PhPsst\Storage\FileStorage::update
     */
    public function testInsert()
    {
        $password = $this->getMockBuilder('PhPsst\Password')->disableOriginalConstructor()->getMock();
        $password->expects($this->atLeast(2))->method('getId')->willReturn(uniqid());
        $password->expects($this->atLeast(2))->method('getPassword');
        $password->expects($this->atLeast(2))->method('getTtl');
        $password->expects($this->atLeast(2))->method('getViews');

        $fileStorage = new FileStorage($this->passwordDirectory, 1);
        /** @var Password $password */
        $fileStorage->insert($password);
        $fileStorage->update($password);
    }

    /**
     * @covers PhPsst\Storage\FileStorage::__construct
     * @covers PhPsst\Storage\FileStorage::insert
     */
    public function testInsertSameId()
    {
        $password = $this->getMockBuilder('PhPsst\Password')->disableOriginalConstructor()->getMock();
        $password->expects($this->atLeastOnce())->method('getId')->willReturn(uniqid());

        $fileStorage = new FileStorage($this->passwordDirectory, 1);
        /** @var Password $password */
        $fileStorage->insert($password);

        $this->setExpectedException('PhPsst\PhPsstException', '', PhPsstException::ID_IS_ALREADY_TAKEN);
        $fileStorage->insert($password);
    }

    /**
     * @covers PhPsst\Storage\FileStorage::__construct
     */
    public function testInvalidDirPath()
    {
        $this->setExpectedException('RuntimeException', 'Invalid directory path');
        $fileStorage = new FileStorage(sys_get_temp_dir() . '/' . uniqid(), 1);
    }

    /**
     * @covers PhPsst\Storage\FileStorage::__construct
     * @covers PhPsst\Storage\FileStorage::insert
     * @covers PhPsst\Storage\FileStorage::writeFile
     * @covers PhPsst\Storage\FileStorage::getFileName
     * @covers PhPsst\Storage\FileStorage::getFileNameFromKey
     */
    public function testNonWriteableDirectory()
    {
        $password = $this->getMockBuilder('PhPsst\Password')->disableOriginalConstructor()->getMock();
        $password->expects($this->atLeastOnce())->method('getId')->willReturn(uniqid());
        $password->expects($this->atLeastOnce())->method('getPassword');
        $password->expects($this->atLeastOnce())->method('getTtl');
        $password->expects($this->atLeastOnce())->method('getViews');

        $invalidDirectory = sys_get_temp_dir() . '/invalidDir' . uniqid();
        mkdir($invalidDirectory, 0);
        //chmod($invalidDirectory, 0);
        $fileStorage = new FileStorage($invalidDirectory, 1);


        $this->setExpectedException('RuntimeException', 'Can not write file');
        /** @var Password $password */
        $fileStorage->insert($password);

        rmdir($invalidDirectory);
    }

    /**
     * @covers PhPsst\Storage\FileStorage::__construct
     * @covers PhPsst\Storage\FileStorage::insert
     * @covers PhPsst\Storage\FileStorage::garbageCollection
     * @covers PhPsst\Storage\FileStorage::writeFile
     * @covers PhPsst\Storage\FileStorage::getFileName
     * @covers PhPsst\Storage\FileStorage::getFileNameFromKey
     * @covers PhPsst\Storage\FileStorage::delete
     * @covers PhPsst\Storage\FileStorage::update
     */
    public function testUpdateNoFile()
    {
        $password = $this->getMockBuilder('PhPsst\Password')->disableOriginalConstructor()->getMock();
        $password->expects($this->atLeastOnce())->method('getId')->willReturn(uniqid());
        $password->expects($this->atLeastOnce())->method('getPassword');
        $password->expects($this->atLeastOnce())->method('getTtl');
        $password->expects($this->atLeastOnce())->method('getViews');
        /** @var Password $password */

        $fileStorage = new FileStorage($this->passwordDirectory, 10);
        $fileStorage->insert($password);
        $fileStorage->delete($password);

        $this->setExpectedException('PhPsst\PhPsstException', '', PhPsstException::NO_PASSWORD_WITH_ID_FOUND);
        $fileStorage->update($password);
    }

    /**
     * @covers PhPsst\Storage\FileStorage::__construct
     * @covers PhPsst\Storage\FileStorage::insert
     * @covers PhPsst\Storage\FileStorage::garbageCollection
     * @covers PhPsst\Storage\FileStorage::writeFile
     * @covers PhPsst\Storage\FileStorage::getFileName
     * @covers PhPsst\Storage\FileStorage::getFileNameFromKey
     * @covers PhPsst\Storage\FileStorage::delete
     * @covers PhPsst\Storage\FileStorage::update
     */
    public function testGarbageCollector()
    {
        $password = $this->getMockBuilder('PhPsst\Password')->disableOriginalConstructor()->getMock();
        $password->expects($this->atLeastOnce())->method('getId')->willReturn(uniqid());
        $password->expects($this->atLeastOnce())->method('getPassword')->willReturn('secret');
        $password->expects($this->atLeastOnce())->method('getTtl')->willReturn(1);
        $password->expects($this->atLeastOnce())->method('getViews')->willReturn(3);

        $passwordTwo = $this->getMockBuilder('PhPsst\Password')->disableOriginalConstructor()->getMock();
        $passwordTwo->expects($this->atLeastOnce())->method('getId')->willReturn(uniqid());
        $passwordTwo->expects($this->atLeastOnce())->method('getPassword')->willReturn('secret');
        $passwordTwo->expects($this->atLeastOnce())->method('getTtl')->willReturn(1);
        $passwordTwo->expects($this->atLeastOnce())->method('getViews')->willReturn(3);
        /** @var Password $password */
        /** @var Password $passwordTwo */

        $fileStorage = new FileStorage($this->passwordDirectory, 1);
        $fileStorage->insert($password);
        sleep(2);
        $fileStorage->insert($passwordTwo);

        // Since the GC should have run the file with the same ID should not exist anymore
        $fileStorage->insert($password);
    }

    /**
     */
    public function tearDown()
    {
        array_map('unlink', glob("$this->passwordDirectory/*.phpsst"));
        rmdir($this->passwordDirectory);
    }
}
