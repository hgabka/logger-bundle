<?php

namespace Hgabka\LoggerBundle\Tests\Helper;

use Hgabka\LoggerBundle\Helper\TextTruncator;
use PHPUnit\Framework\TestCase;

class TextTruncatorTest extends TestCase
{
    public function testShortAndNullUntouched(): void
    {
        $this->assertSame('abc', TextTruncator::truncate('abc'));
        $this->assertNull(TextTruncator::truncate(null));
        $this->assertNull(TextTruncator::truncate(false));
    }

    public function testLongIsCutWithoutBreakingUtf8(): void
    {
        $res = TextTruncator::truncate(str_repeat('ő', 50000));
        $this->assertLessThanOrEqual(TextTruncator::MAX_LENGTH, \strlen($res));
        $this->assertTrue(mb_check_encoding($res, 'UTF-8'));
    }
}
