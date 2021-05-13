<?php

 namespace DanielZ\MusicCodes\Tests;

use PHPUnit\Framework\TestCase;
use DanielZ\MusicCodes\Iswc;

class IswcTest extends TestCase
{
    public function testParts()
    {
        $sample_iswc = 'T-034.524.680-1';
        $iswc = new Iswc($sample_iswc);
        self::assertEquals(true, $iswc->isValid());
        self::assertEquals('T', $iswc->getPrefix());
        self::assertEquals('034.524.680', $iswc->getId(true));
        self::assertEquals('034524680', $iswc->getId(false));
        self::assertEquals('1', $iswc->getCheckDigit());
        self::assertEquals($sample_iswc, (string)$iswc);
        self::assertEquals($sample_iswc, $iswc->getIswc(true));
    }

    public function testFromId()
    {
        $iswc = Iswc::fromId('034.524.680');
        self::assertEquals('T', $iswc->getPrefix());
        self::assertEquals('1', $iswc->getCheckDigit());
        self::assertEquals('034524680', $iswc->getId(false));
    }

    public function testPrevNext()
    {
        $iswc = Iswc::fromId('034.524.680');

        $iswc->next();
        self::assertEquals('034.524.681', $iswc->getId(true));

        $iswc->previous();
        $iswc->previous();
        self::assertEquals('034.524.679', $iswc->getId(true));
    }

    public function testCheckDigits()
    {
        $iswcs = [
            'T-911.665.386-2',
            'T-910.126.830-8',
            'T-910.691.619-2',
            'T-913.327.074-3',
            'T-913.587.630-1',
        ];

        foreach($iswcs as $iswc) {
            list(,,$check_digit) = explode('-', $iswc);
            $this->assertEquals($check_digit, (new Iswc($iswc))->getCheckDigit());
        }
    }

    public function testFormatting()
    {
        $iswc = Iswc::fromId('034.524.680');

        $this->assertEquals('T0345246801', $iswc->getIswc(false));
        $this->assertEquals('T-034.524.680-1', $iswc->getIswc(true));
        $this->assertEquals('T-034.524.680-1', (string)$iswc);
    }
}
