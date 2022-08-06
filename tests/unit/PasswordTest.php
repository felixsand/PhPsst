<?php
/**
 * PhPsst.
 *
 * @copyright Copyright (c) 2016 Felix Sandström
 * @license   MIT
 */

namespace PhPsstTest;

use LogicException;
use PhPsst\Password;
use PHPUnit\Framework\TestCase;

/**
 * @author Felix Sandström <http://github.com/felixsand>
 * @coversDefaultClass \PhPsst\Password
 */
class PasswordTest extends TestCase
{
    /**
     * @var Password
     */
    private $password;

    /**
     */
    public function setUp(): void
    {
        $this->password = new Password('id', 'password', 123, 3);
    }

    /**
     * @covers ::__construct
     */
    public function testConstruct()
    {
        $this->assertInstanceOf(Password::class, $this->password);
    }

    /**
     * @covers ::getId
     */
    public function testGetId()
    {
        $this->assertEquals('id', $this->password->getId());
    }

    /**
     * @covers ::getPassword
     */
    public function testGetPassword()
    {
        $this->assertEquals('password', $this->password->getPassword());
    }

    /**
     * @covers ::getTtl
     */
    public function testGetTtl()
    {
        $this->assertEquals(123, $this->password->getTtl());
    }

    /**
     * @covers ::getViews
     */
    public function testGetViews()
    {
        $this->assertEquals(3, $this->password->getViews());
    }

    /**
     * @covers ::decreaseViews
     */
    public function testDecreaseViews()
    {
        $password = new Password('id', 'password', 123, 2);
        $this->assertEquals(2, $password->getViews());
        $password->decreaseViews();
        $this->assertEquals(1, $password->getViews());
        $password->decreaseViews();
        $this->assertEquals(0, $password->getViews());
    }

    /**
     * @covers ::decreaseViews
     */
    public function testDecreaseViewsException()
    {
        $this->expectException(LogicException::class);
        $password = new Password('id', 'password', 123, 1);
        $password->decreaseViews();
        $password->decreaseViews();
    }

    /**
     * @covers ::getJson
     */
    public function testGetJson()
    {
        $password = new Password('superSecretId', 'superSecretPassword', 123232321244, 983926);
        $jsonData = $password->getJson();

        $this->assertStringContainsString('superSecretId', $jsonData);
        $this->assertStringContainsString('superSecretPassword', $jsonData);
        $this->assertStringContainsString('123232321244', $jsonData);
        $this->assertStringContainsString('983926', $jsonData);
    }
}
