<?php
/**
 * PhPsst.
 *
 * @copyright Copyright (c) 2016 Felix Sandström
 * @license   MIT
 */

namespace PhPsst;

/**
 * @author Felix Sandström <http://github.com/felixsand>
 */
class PhPsstTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PhPsst
     */
    private $phPsst;

    /**
     */
    public function setUp()
    {
        $storageMock = $this->getMockBuilder('PhPsst\Storage\FileStorage')->disableOriginalConstructor()->getMock();
        $this->phPsst = new PhPsst($storageMock);
    }

    /**
     * @covers PhPsst\Password::__construct
     */
    public function testConstruct()
    {
        $this->assertInstanceOf('PhPsst\PhPsst', $this->phPsst);
    }

}
