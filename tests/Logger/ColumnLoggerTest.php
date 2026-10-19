<?php

namespace Hgabka\LoggerBundle\Tests\Logger;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Hgabka\LoggerBundle\Helper\LoggableEntityInterface;
use Hgabka\LoggerBundle\Logger\ColumnLogger;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ColumnLoggerTest extends TestCase
{
    private function logger(string $enabled = 'always'): ColumnLogger
    {
        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('trans')->willReturnCallback(
            static fn (string $id, array $params = [], ?string $domain = null): string => $domain . ':' . $id
        );

        return new ColumnLogger(
            $this->createStub(Registry::class),
            new TokenStorage(),
            $translator,
            new RequestStack(),
            $this->createStub(AuthorizationCheckerInterface::class),
            false,
            'ident',
            'logger',
            $enabled
        );
    }

    private function convert(mixed $value, ?string $type): string
    {
        return (new \ReflectionMethod(ColumnLogger::class, 'convertValue'))->invoke($this->logger(), $value, $type);
    }

    #[DataProvider('values')]
    public function testConvertValue(mixed $value, ?string $type, string $expected): void
    {
        $this->assertSame($expected, $this->convert($value, $type));
    }

    public static function values(): array
    {
        return [
            'string' => ['abc', 'string', 'abc'],
            'null' => [null, 'string', ''],
            'bool true' => [true, null, '1'],
            'bool false' => [false, null, '0'],
            'boolean column is translated (yes)' => [true, 'boolean', 'logger:hgabka_logger.log_value.yes'],
            'boolean column is translated (no)' => [0, 'boolean', 'logger:hgabka_logger.log_value.no'],
            'integer' => ['42', 'integer', '42'],
            'datetime' => [new \DateTime('2024-03-05 10:11:12'), 'datetime', '2024-03-05 10:11:12'],
            'date' => [new \DateTimeImmutable('2024-03-05 10:11:12'), 'date', '2024-03-05'],
            'array is json' => [['a' => 'é'], 'json', '{"a":"é"}'],
        ];
    }

    public function testDisabledLoggerProducesNoLogs(): void
    {
        $entity = new class() implements LoggableEntityInterface {
            public function getLogFields(): ?array
            {
                return [];
            }

            public function getNotLogFields(): ?array
            {
                return null;
            }
        };

        $this->assertSame([], $this->logger('never')->logColumns($entity, ColumnLogger::MOD_TYPE_INSERT, $this->createStub(EntityManager::class)));
    }

    public function testModTypeConstants(): void
    {
        $this->assertSame(['INSERT', 'UPDATE', 'DELETE'], [ColumnLogger::MOD_TYPE_INSERT, ColumnLogger::MOD_TYPE_UPDATE, ColumnLogger::MOD_TYPE_DELETE]);
    }
}
