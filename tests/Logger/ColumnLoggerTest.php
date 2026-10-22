<?php

namespace Hgabka\LoggerBundle\Tests\Logger;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Hgabka\LoggerBundle\Entity\LogColumn;
use Hgabka\LoggerBundle\Helper\LoggableEntityInterface;
use Hgabka\LoggerBundle\Logger\ColumnLogger;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ColumnLoggerTest extends TestCase
{
    private function logger(string $enabled = 'always', bool $logRequestData = true, ?RequestStack $requestStack = null, ?TokenStorage $tokenStorage = null): ColumnLogger
    {
        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('trans')->willReturnCallback(
            static fn (string $id, array $params = [], ?string $domain = null): string => $domain . ':' . $id
        );

        return new ColumnLogger(
            $this->createStub(Registry::class),
            $tokenStorage ?? new TokenStorage(),
            $translator,
            $requestStack ?? new RequestStack(),
            $this->createStub(AuthorizationCheckerInterface::class),
            false,
            'ident',
            'logger',
            $enabled,
            $logRequestData
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
            'entity is logged by short class and id' => [new LoggedEntityStub(7), null, 'LoggedEntityStub#7'],
            'stringable' => [new class() implements \Stringable {
                public function __toString(): string
                {
                    return 'Név';
                }
            }, null, 'Név'],
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

    private function fill(ColumnLogger $logger): LogColumn
    {
        $log = new LogColumn();
        (new \ReflectionMethod(ColumnLogger::class, 'setLogFields'))->invoke($logger, $log);

        return $log;
    }

    private function requestStack(): RequestStack
    {
        $stack = new RequestStack();
        $stack->push(Request::create('/x', 'POST', ['name' => 'Rohadt ugyfel']));

        return $stack;
    }

    public function testRequestDataIsLoggedByDefault(): void
    {
        $log = $this->fill($this->logger('always', true, $this->requestStack()));

        $this->assertSame('{"name":"Rohadt ugyfel"}', $log->getPost());
        $this->assertNotNull($log->getRequestAttributes());
    }

    public function testRequestDataCanBeDisabled(): void
    {
        $log = $this->fill($this->logger('always', false, $this->requestStack()));

        $this->assertNull($log->getPost());
        $this->assertNull($log->getRequestAttributes());
        $this->assertSame('POST (POST)', $log->getMethod());
    }

    public function testUserClassIsRecorded(): void
    {
        $user = new class() implements UserInterface {
            public function getId(): int
            {
                return 7;
            }

            public function getRoles(): array
            {
                return [];
            }

            public function eraseCredentials(): void
            {
            }

            public function getUserIdentifier(): string
            {
                return 'admin';
            }
        };
        $tokenStorage = new TokenStorage();
        $tokenStorage->setToken(new UsernamePasswordToken($user, 'main'));

        $log = $this->fill($this->logger('always', true, $this->requestStack(), $tokenStorage));

        $this->assertSame(7, $log->getUserId());
        $this->assertSame($user::class, $log->getUserClass());
        $this->assertNull($log->getOriginalUserClass());
    }
}

class LoggedEntityStub
{
    public function __construct(private int $id)
    {
    }

    public function getId(): int
    {
        return $this->id;
    }
}
