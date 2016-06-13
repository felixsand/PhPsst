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
class StorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers PhPsst\Storage\Storage::getPasswordFromJson
     * @covers PhPsst\Storage\Storage::getJsonFromPassword
     */
    public function testGetPasswordFromJson()
    {
        $storage = new TestStorage();

        $password = new Password('secretId', 'password', 300, 30);
        $jsonData = $storage->getJsonFromPassword($password);
        $returnedPassword = $storage->getPasswordFromJson($jsonData);

        $this->assertEquals($password->getId(), $returnedPassword->getId());
        $this->assertEquals($password->getPassword(), $returnedPassword->getPassword());
        $this->assertEquals($password->getTtl(), $returnedPassword->getTtl());
        $this->assertEquals($password->getViews(), $returnedPassword->getViews());
    }

    /**
     * @covers PhPsst\Storage\Storage::getPasswordFromJson
     * @covers PhPsst\Storage\Storage::getJsonFromPassword
     */
    public function testDeleteOnExpired()
    {
        $storage = new TestStorage();

        $password = new Password('secretId', 'password', -1, 30);
        $jsonData = $storage->getJsonFromPassword($password);
        $returnedPassword = $storage->getPasswordFromJson($jsonData);

        $this->assertNull($returnedPassword);
    }
}

/**
 */
class TestStorage extends Storage
{
    public function get($key) {}

    public function store(Password $password, $allowOverwrite = false) {}

    public function delete(Password $password) {}
}
