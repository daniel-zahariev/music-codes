<?php

// namespace Tests;

use PHPUnit\Framework\TestCase;
use DanielZ\MusicCodes\Isrc;

class IsrcTest extends TestCase
{
    public function testParts()
    {
        $isrc = new Isrc('GB-A1B-11-00036');
        self::assertEquals('GB', $isrc->getCountryCode());
        self::assertEquals('A1B', $isrc->getIssuerCode());
        self::assertEquals('11', $isrc->getYear(true));
        self::assertEquals(11, $isrc->getYear(false));
        self::assertEquals('00036', $isrc->getId(true));
        self::assertEquals(36, $isrc->getId(false));
    }

    public function testValidIsrcs()
    {
        $valid_isrcs = [
            'GBA1B1100036' => 'GBA1B1100036',
            '  GB-A1B-11-00036  ' => 'GBA1B1100036',
            'ISRCGBA1B1100036' => 'GBA1B1100036',
            'ISRCGB-A1B-11-00036' => 'GBA1B1100036',
            'ISRC GBA1B1100036' => 'GBA1B1100036',
            'ISRC  GB-A1B-11-00036 ' => 'GBA1B1100036',
            'GBA1B1100036 ISRC' => 'GBA1B1100036',
            'gb-a1b-11-00036   isrc' => 'GBA1B1100036',
        ];
        foreach($valid_isrcs as $valid_isrc => $formatted_isrc) {
            $isrc = new Isrc($valid_isrc);
            $this->assertTrue($isrc->isValid(), $valid_isrc);
            $this->assertEquals($formatted_isrc, $isrc->getIsrc(false));
            $this->assertEquals("ISRC {$formatted_isrc}", $isrc->getIsrc(false, true));
        }

    }

    public function testInvalidIsrcs()
    {
        $invalid_isrcs = [
            'GB-A1B1100036' => 'One dash shouldn\'t work',
            'GB-A1B-A1-00036' => 'No letters allowed in the year',
            'GB-A1B-11-A0036' => 'No letters allowed in the id',
            '1B-A1B-11-A0036' => 'No numbers allowed in the country',
            'GB-A1B-11-00000' => 'Id should be at least 1',
            'ISR GB-A1B-11-A0036' => 'No letters allowed in the id',
        ];
        foreach($invalid_isrcs as $invalid_isrc => $message) {
            $isrc = new Isrc($invalid_isrc);
            $this->assertFalse($isrc->isValid(), $message . ' : ' . $invalid_isrc);
        }
    }

    public function testNextIsrc()
    {
        $isrc = new Isrc('GBA1B1150000');
        $this->assertTrue($isrc->isValid());

        $isrc->next();
        $this->assertTrue($isrc->isValid());
        $this->assertEquals('GBA1B1150001', $isrc->getIsrc(false));

        $isrc->next(99999);
        $this->assertTrue($isrc->isValid());
        $this->assertEquals('GBA1B1199999', $isrc->getIsrc(false));

        $isrc->next();
        $this->assertFalse($isrc->isValid());
    }

    public function testPreviousIsrc()
    {
        $isrc = new Isrc('GBA1B1150000');
        $this->assertTrue($isrc->isValid());

        $isrc->previous();
        $this->assertTrue($isrc->isValid());
        $this->assertEquals('GBA1B1149999', $isrc->getIsrc(false));

        $isrc->previous(1);
        $this->assertTrue($isrc->isValid());
        $this->assertEquals('GBA1B1100001', $isrc->getIsrc(false));

        $isrc->previous();
        $this->assertFalse($isrc->isValid());
    }

    public function testFlipYearNext()
    {
        $isrc = new Isrc('GBA1B1199999');
        $this->assertTrue($isrc->isValid());

        $isrc->next(0, true);
        $this->assertTrue($isrc->isValid());
        $this->assertEquals('GBA1B1200001', $isrc->getIsrc(false));


        $isrc = new Isrc('GBA1B9999999');
        $this->assertTrue($isrc->isValid());

        $isrc->next(0, true);
        $this->assertTrue($isrc->isValid());
        $this->assertEquals('GBA1B0000001', $isrc->getIsrc(false));
    }

    public function testFlipYearPrevious()
    {
        $isrc = new Isrc('GBA1B1100001');
        $this->assertTrue($isrc->isValid());

        $isrc->previous(Isrc::MAX_ID, true);
        $this->assertTrue($isrc->isValid());
        $this->assertEquals('GBA1B1099999', $isrc->getIsrc(false));


        $isrc = new Isrc('GBA1B0000001');
        $this->assertTrue($isrc->isValid());

        $isrc->previous(Isrc::MAX_ID, true);
        $this->assertTrue($isrc->isValid());
        $this->assertEquals('GBA1B9999999', $isrc->getIsrc(false));
    }

    public function testSettingTheParts()
    {
        $isrc = new Isrc();
        $isrc->setCountryCode('GB');
        $isrc->setIssuerCode('A1B');
        $isrc->setYear(11);
        $isrc->setId(36);

        $this->assertEquals('GB-A1B-11-00036', $isrc->getIsrc(true));
    }
}
