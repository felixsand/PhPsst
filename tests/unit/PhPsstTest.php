<?php
/**
 * PhPsst.
 *
 * @copyright Copyright (c) 2016 Felix Sandström
 * @license   MIT
 */

namespace PhPsstTest;

use Illuminate\Encryption\Encrypter;
use InvalidArgumentException;
use PhPsst\Password;
use PhPsst\PhPsst;
use PhPsst\PhPsstException;
use PhPsst\Storage\FileStorage;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @author Felix Sandström <http://github.com/felixsand>
 * @coversDefaultClass \PhPsst\PhPsst
 */
class PhPsstTest extends TestCase
{
    /**
     * @var PhPsst
     */
    private $phPsst;

    public function setUp(): void
    {
        $storageMock = $this->getMockBuilder(FileStorage::class)->disableOriginalConstructor()->getMock();

        /** @var FileStorage $storageMock */
        $this->phPsst = new PhPsst($storageMock);
    }

    /**
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $this->assertInstanceOf(PhPsst::class, $this->phPsst);
    }

    /**
     * @covers ::__construct
     */
    public function testConstructWithCipher(): void
    {
        $storageMock = $this->getMockBuilder(FileStorage::class)->disableOriginalConstructor()->getMock();

        /** @var FileStorage $storageMock */
        $phPsst = new PhPsst($storageMock, 'AES-256-CBC');
        $this->assertInstanceOf(PhPsst::class, $phPsst);
    }

    /**
     * @covers ::store
     * @covers ::generateKey
     */
    public function testNonDefaultCipher(): void
    {
        $storageMock = $this->getMockBuilder(FileStorage::class)->disableOriginalConstructor()->getMock();
        $storageMock->expects($this->once())->method('store');

        /** @var FileStorage $storageMock */
        $phPsst = new PhPsst($storageMock, 'AES-128-CBC');
        $secret = $phPsst->store('test', 300, 3);

        $this->assertStringContainsString(';', $secret);
    }

    /**
     * @covers ::store
     * @covers ::generateKey
     */
    public function testInvalidCipher(): void
    {
        $storageMock = $this->getMockBuilder(FileStorage::class)->disableOriginalConstructor()->getMock();

        /** @var FileStorage $storageMock */
        $phPsst = new PhPsst($storageMock, 'invalid-cipher');

        $this->expectException(RuntimeException::class);
        $phPsst->store('test', 300, 3);
    }

    /**
     * @covers ::store
     * @covers ::generateKey
     */
    public function testStore(): void
    {
        $storageMock = $this->getMockBuilder(FileStorage::class)->disableOriginalConstructor()->getMock();
        $storageMock->expects($this->once())->method('store');

        /** @var FileStorage $storageMock */
        $phPsst = new PhPsst($storageMock);
        $secret = $phPsst->store('test', 300, 3);

        $this->assertStringContainsString(';', $secret);
    }

    /**
     * @covers ::store
     */
    public function testStoreNoKey(): void
    {
        $storageMock = $this->getMockBuilder(FileStorage::class)->disableOriginalConstructor()->getMock();

        /** @var FileStorage $storageMock */
        $phPsst = new PhPsst($storageMock);

        $this->expectException(InvalidArgumentException::class);
        $phPsst->store('');
    }

    /**
     * @covers ::store
     */
    public function testStoreInvalidTtl(): void
    {
        $storageMock = $this->getMockBuilder(FileStorage::class)->disableOriginalConstructor()->getMock();

        /** @var FileStorage $storageMock */
        $phPsst = new PhPsst($storageMock);

        $this->expectException(InvalidArgumentException::class);
        $phPsst->store('test', -1);
    }

    /**
     * @covers ::store
     */
    public function testStoreInvalidViews(): void
    {
        $storageMock = $this->getMockBuilder(FileStorage::class)->disableOriginalConstructor()->getMock();

        /** @var FileStorage $storageMock */
        $phPsst = new PhPsst($storageMock);

        $this->expectException(InvalidArgumentException::class);
        $phPsst->store('test', 300, -1);
    }

    /**
     * @covers ::retrieve
     */
    public function testRetrieve(): void
    {
        $id = uniqid();
        $key = bin2hex(random_bytes(16));
        $encryptedPassword = (new Encrypter($key, PhPsst::CIPHER_DEFAULT))->encrypt('secretMessage');
        $password = new Password('id', $encryptedPassword, 300, 3);

        $storageMock = $this->getMockBuilder(FileStorage::class)->disableOriginalConstructor()->getMock();
        $storageMock->expects($this->once())->method('get')->willReturn($password);

        /** @var FileStorage $storageMock */
        $phPsst = new PhPsst($storageMock);

        $decryptedPassword = $phPsst->retrieve($id . ';' . $key);
        $this->assertEquals('secretMessage', $decryptedPassword);
    }

    /**
     * @covers ::retrieve
     */
    public function testRetrieveInvalidSecret(): void
    {
        $storageMock = $this->getMockBuilder(FileStorage::class)->disableOriginalConstructor()->getMock();

        /** @var FileStorage $storageMock */
        $phPsst = new PhPsst($storageMock);

        $this->expectException(InvalidArgumentException::class);
        $phPsst->retrieve('');
    }

    /**
     * @covers ::retrieve
     */
    public function testRetrieveNoPasswordFound(): void
    {
        $storageMock = $this->getMockBuilder(FileStorage::class)->disableOriginalConstructor()->getMock();

        /** @var FileStorage $storageMock */
        $phPsst = new PhPsst($storageMock);

        $this->expectException(PhPsstException::class);
        $this->expectExceptionCode(PhPsstException::NO_PASSWORD_WITH_ID_FOUND);
        $phPsst->retrieve('id;secret');
    }
}
