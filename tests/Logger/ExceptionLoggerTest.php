<?php

namespace Hgabka\LoggerBundle\Tests\Logger;

use Hgabka\LoggerBundle\Logger\ExceptionLogFormatter;
use Hgabka\LoggerBundle\Logger\ExceptionLogger;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class ExceptionLoggerTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/hg_logger_' . bin2hex(random_bytes(4));
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '/*') ?: [] as $file) {
            unlink($file);
        }
        if (is_dir($this->dir)) {
            rmdir($this->dir);
        }
    }

    public function testWritesDailyLogFileWithPlainFormat(): void
    {
        $logger = new ExceptionLogger(new Logger('exception'), new ExceptionLogFormatter(ExceptionLogFormatter::SIMPLE_FORMAT), $this->dir);

        $logger->getLogger()->error("first line\nsecond line");

        $file = $this->dir . '/' . date('Ymd') . '.log';
        $this->assertFileExists($file);
        $content = file_get_contents($file);
        $this->assertMatchesRegularExpression('/^\[\d{4}-\d\d-\d\d.*\] first line\nsecond line\n$/', $content);
    }

    public function testEmptyPathLeavesHandlersUntouched(): void
    {
        $monolog = new Logger('exception');

        new ExceptionLogger($monolog, new ExceptionLogFormatter(), '');

        $this->assertSame([], $monolog->getHandlers());
    }

    public function testLoggerCanBeReplaced(): void
    {
        $first = new Logger('a');
        $second = new Logger('b');
        $logger = new ExceptionLogger($first, new ExceptionLogFormatter(), '');

        $this->assertSame($first, $logger->getLogger());
        $this->assertSame($logger, $logger->setLogger($second));
        $this->assertSame($second, $logger->getLogger());
    }

    public function testFormatterStringifiesArraysAndScalars(): void
    {
        $formatter = new ExceptionLogFormatter();

        $this->assertSame('abc', $formatter->stringify('abc'));
        $this->assertSame('{"a":1}', $formatter->stringify(['a' => 1]));
    }
}
