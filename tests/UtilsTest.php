<?php

use Catchain\Ton\Address\Utils;
use PHPUnit\Framework\TestCase;

final class UtilsTest extends TestCase
{
    public function testBase64Encoder(): void
    {
        $testData = base64_decode('cZ+8IP/1L2//+Q==');

        $this->assertEquals('cZ-8IP_1L2__-Q==', Utils::base64encode($testData, urlSafe: true));
        $this->assertEquals('cZ+8IP/1L2//+Q==', Utils::base64encode($testData, urlSafe: false));
    }

    public function testBase64Decoder(): void
    {
        $testData = base64_decode('cZ+8IP/1L2//+Q==');

        $this->assertEquals($testData, Utils::base64decode('cZ-8IP_1L2__-Q=='));
        $this->assertEquals($testData, Utils::base64decode('cZ+8IP/1L2//+Q=='));
    }

    public function testSignedHexAbsFunction(): void
    {
        $this->assertEquals(-1, Utils::signedHexAbs(0xffff));
        $this->assertEquals(-1, Utils::signedHexAbs(0x0fff));
    }

    public function testCrc16Function(): void
    {
        $testData = base64_decode('cZ+8IP/1L2//+Q==');
        $testDataHash = base64_decode('YU0=');

        $this->assertEquals($testDataHash, Utils::crc16($testData));
    }
}
