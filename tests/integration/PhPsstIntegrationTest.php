<?php
/**
 * PhPsst.
 *
 * @copyright Copyright (c) 2016 Felix Sandström
 * @license   MIT
 */

namespace PhPsstTest;

use PhPsst\PhPsst;
use PhPsst\PhPsstException;
use PhPsst\Storage\FileStorage;
use PHPUnit\Framework\TestCase;

/**
 * @author Felix Sandström <http://github.com/felixsand>
 */
class PhPsstIntegrationTest extends TestCase
{
    /**
     * @var string
     */
    private $passwordDirectory;

    /**
     */
    public function setUp()
    {
        $this->passwordDirectory = sys_get_temp_dir() . '/PhPsstIntegrationTest';
        mkdir($this->passwordDirectory);
    }

    /**
     * @covers \PhPsst\Storage\FileStorage::__construct
     * @covers \PhPsst\Storage\FileStorage::store
     * @covers \PhPsst\Storage\FileStorage::get
     * @covers \PhPsst\Storage\FileStorage::garbageCollection
     * @covers \PhPsst\Storage\FileStorage::writeFile
     * @covers \PhPsst\Password::__construct
     * @covers \PhPsst\PhPsst::__construct
     * @covers \PhPsst\PhPsst::store
     * @covers \PhPsst\PhPsst::retrieve
     */
    public function testStoreRetrieve()
    {
        $password = 'my secret password';
        $storage = new FileStorage($this->passwordDirectory, 1);
        $phPsst = new PhPsst($storage);
        $secret = $phPsst->store($password);

        $this->assertEquals($password, $phPsst->retrieve($secret));
    }

    /**
     * @covers \PhPsst\Storage\FileStorage::__construct
     * @covers \PhPsst\Storage\FileStorage::store
     * @covers \PhPsst\Storage\FileStorage::delete
     * @covers \PhPsst\Storage\FileStorage::get
     * @covers \PhPsst\Storage\FileStorage::garbageCollection
     * @covers \PhPsst\Storage\FileStorage::writeFile
     * @covers \PhPsst\Password::__construct
     * @covers \PhPsst\PhPsst::__construct
     * @covers \PhPsst\PhPsst::store
     * @covers \PhPsst\PhPsst::retrieve
     */
    public function testStoreRetrieveOneView()
    {
        $password = 'my secret password';
        $storage = new FileStorage($this->passwordDirectory, 1);
        $phPsst = new PhPsst($storage);
        $secret = $phPsst->store($password, 300, 1);

        $this->assertEquals($password, $phPsst->retrieve($secret));

        $this->expectException(PhPsstException::class);
        $this->expectExceptionCode(PhPsstException::NO_PASSWORD_WITH_ID_FOUND);
        $phPsst->retrieve($secret);
    }

    /**
     * @covers \PhPsst\Storage\FileStorage::__construct
     * @covers \PhPsst\Storage\FileStorage::store
     * @covers \PhPsst\Storage\FileStorage::delete
     * @covers \PhPsst\Storage\FileStorage::get
     * @covers \PhPsst\Storage\FileStorage::garbageCollection
     * @covers \PhPsst\Storage\FileStorage::writeFile
     * @covers \PhPsst\Password::__construct
     * @covers \PhPsst\PhPsst::__construct
     * @covers \PhPsst\PhPsst::store
     * @covers \PhPsst\PhPsst::retrieve
     */
    public function testStoreRetrieveTtlTimeout()
    {
        $password = 'my secret password';
        $storage = new FileStorage($this->passwordDirectory, 1);
        $phPsst = new PhPsst($storage);
        $secret = $phPsst->store($password, 1, 3);

        $this->assertEquals($password, $phPsst->retrieve($secret));

        sleep(2);
        $this->expectException(PhPsstException::class);
        $this->expectExceptionCode(PhPsstException::NO_PASSWORD_WITH_ID_FOUND);
        $phPsst->retrieve($secret);
    }

    /**
     */
    public function tearDown()
    {
        array_map('unlink', glob("$this->passwordDirectory/*.phpsst"));
        rmdir($this->passwordDirectory);
    }
}
