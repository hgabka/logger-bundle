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

    public function testScrubReplacesInvalidUtf8AndKeepsValidText(): void
    {
        $res = TextTruncator::scrub("/a/\xC0\xA7\xC0\xA2%2e/árvíztűrő");

        $this->assertTrue(mb_check_encoding($res, 'UTF-8'));
        $this->assertStringContainsString('árvíztűrő', $res);
        $this->assertSame('', TextTruncator::scrub(''));
        $this->assertNull(TextTruncator::scrub(null));
        $this->assertNull(TextTruncator::scrub(false));
    }

    public function testJsonSubstitutesInvalidUtf8InsteadOfFailing(): void
    {
        $res = TextTruncator::json(['q' => "\xC0\xA7x", 'ok' => 'ő']);

        $this->assertIsString($res);
        $this->assertTrue(mb_check_encoding($res, 'UTF-8'));
        $this->assertSame('ő', json_decode($res, true)['ok']);
    }
}
