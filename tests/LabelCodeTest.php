<?php

 namespace DanielZ\MusicCodes\Tests;

use PHPUnit\Framework\TestCase;
use DanielZ\MusicCodes\LabelCode;

class LabelCodeTest extends TestCase
{
    public function testParts()
    {
        $sample_code = 'LC-1234';
        $label_code = new LabelCode($sample_code);
        self::assertEquals(true, $label_code->isValid());
        self::assertEquals('LC', $label_code->getPrefix());
        self::assertEquals('1234', $label_code->getId(true));
        self::assertEquals(1234, $label_code->getId(false));
    }


    public function testPrevNext()
    {
        $label_code = LabelCode::fromParts('LC', 1234);

        $label_code->next();
        self::assertEquals(true, $label_code->isValid());
        self::assertEquals(1235, $label_code->getId(false));

        $label_code->previous();
        $label_code->previous();
        self::assertEquals(true, $label_code->isValid());
        self::assertEquals(1233, $label_code->getId(false));

        self::assertEquals(false, $label_code->load('LC-01')->previous());

        self::assertEquals(false, $label_code->load('LC-99')->next());
    }


    public function testFormatting()
    {
        $label_code = new LabelCode('LC1234');

        $this->assertEquals('LC-1234', $label_code->getLabelCode(true));
        $this->assertEquals('LC1234', $label_code->getLabelCode(false));
        $this->assertEquals('LC1234', (string)$label_code);
    }
}
