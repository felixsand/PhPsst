<?php
/**
 * PhPsst.
 *
 * @copyright Copyright (c) 2016 Felix Sandström
 * @license   MIT
 */

namespace PhPsstTest\Storage;

use PhPsst\Password;
use PhPsst\Storage\Storage;
use PHPUnit\Framework\TestCase;

/**
 * @author Felix Sandström <http://github.com/felixsand>
 * @coversDefaultClass \PhPsst\Storage\Storage
 */
class StorageTest extends TestCase
{
    /**
     * @covers ::getPasswordFromJson
     */
    public function testGetPasswordFromJson()
    {
        $storage = new TestStorage();

        $password = new Password('secretId', 'password', strtotime('+1 hour'), 30);
        $jsonData = $password->getJson();
        $returnedPassword = $storage->getPasswordFromJson($jsonData);

        $this->assertEquals($password->getId(), $returnedPassword->getId());
        $this->assertEquals($password->getPassword(), $returnedPassword->getPassword());
        $this->assertEquals($password->getTtl(), $returnedPassword->getTtl());
        $this->assertEquals($password->getViews(), $returnedPassword->getViews());
    }

    /**
     * @covers ::getPasswordFromJson
     */
    public function testDeleteOnExpired()
    {
        $storage = new TestStorage();

        $password = new Password('secretId', 'password', 300, 30);
        $jsonData = $password->getJson();
        $returnedPassword = $storage->getPasswordFromJson($jsonData);

        $this->assertNull($returnedPassword);
    }
}

class TestStorage extends Storage
{
    public function get(string $key): ?Password
    {
        return null;
    }

    public function store(Password $password, bool $allowOverwrite = false): void
    {
    }

    public function delete(Password $password): void
    {
    }
}
