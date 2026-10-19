<?php

namespace Hgabka\LoggerBundle\Tests\Logger;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManagerInterface;
use Hgabka\LoggerBundle\Entity\LogAction;
use Hgabka\LoggerBundle\Logger\ActionLogger;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ActionLoggerTest extends TestCase
{
    /** @var list<object> */
    private array $persisted = [];

    private int $flushes = 0;

    private function logger(string $enabled = 'always', bool $debug = false, ?Request $request = null): ActionLogger
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('persist')->willReturnCallback(function (object $o): void {
            $this->persisted[] = $o;
        });
        $em->method('flush')->willReturnCallback(function (): void {
            ++$this->flushes;
        });

        $doctrine = $this->createStub(Registry::class);
        $doctrine->method('getManager')->willReturn($em);

        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('trans')->willReturnCallback(
            static fn (string $id, array $params = [], ?string $domain = null): string => sprintf('%s|%s|%s', $domain, $id, json_encode($params))
        );

        $requests = new RequestStack();
        if ($request) {
            $requests->push($request);
        }

        return new ActionLogger(
            $doctrine,
            new TokenStorage(),
            $translator,
            $requests,
            $this->createStub(AuthorizationCheckerInterface::class),
            $debug,
            'ident',
            'logger',
            $enabled
        );
    }

    #[DataProvider('enabledMatrix')]
    public function testLoggingEnabledDependsOnEnvironment(string $enabled, bool $debug, bool $expected): void
    {
        $this->logger($enabled, $debug)->log('type', 'message');

        $this->assertSame($expected, [] !== $this->persisted);
    }

    public static function enabledMatrix(): array
    {
        return [
            'always, prod' => ['always', false, true],
            'always, debug' => ['always', true, true],
            'prod, prod' => ['prod', false, true],
            'prod, debug' => ['prod', true, false],
            'debug, prod' => ['debug', false, false],
            'debug, debug' => ['debug', true, true],
            'never, prod' => ['never', false, false],
            'never, debug' => ['never', true, false],
        ];
    }

    public function testStartPersistsActionWithTranslatedDescription(): void
    {
        $request = Request::create('/orders', 'POST', ['a' => 1]);
        $request->attributes->set('_controller', 'App\\Controller\\OrderController::save');
        $logger = $this->logger('always', false, $request);

        $this->assertSame($logger, $logger->start('order.save', ['%id%' => 7]));

        /** @var LogAction $action */
        $action = $this->persisted[0];
        $this->assertInstanceOf(LogAction::class, $action);
        $this->assertSame('order.save', $action->getType());
        $this->assertSame('logger|order.save|{"%id%":7}', $action->getDescription());
        $this->assertSame('ident', $action->getIdent());
        $this->assertFalse($action->isSuccess());
        $this->assertSame('/orders', $action->getRequestUri());
        $this->assertSame('App\\Controller\\OrderController::save', $action->getController());
        $this->assertSame('POST (POST)', $action->getMethod());
        $this->assertSame('{"a":1}', $action->getPost());
    }

    public function testStringMessageIsUsedAsDescription(): void
    {
        $this->logger()->start('custom', 'Saját szöveg');

        $this->assertSame('Saját szöveg', $this->persisted[0]->getDescription());
    }

    public function testDoneMarksActionSuccessful(): void
    {
        $logger = $this->logger();
        $logger->start('order.save', []);
        $logger->done();

        $action = $this->persisted[0];
        $this->assertTrue($action->isSuccess());
        $this->assertNotNull($action->getEndTime());
    }

    public function testDoneAfterErrorPriorityIsNotSuccessful(): void
    {
        $logger = $this->logger();
        $logger->start('order.save', []);
        $logger->update(null, null, 'ERROR');
        $logger->done();

        $this->assertFalse($this->persisted[0]->isSuccess());
    }

    public function testUpdateKeepsDescriptionWhenNoNewMessageIsGiven(): void
    {
        $logger = $this->logger();
        $logger->start('order.save', ['%id%' => 1]);
        $description = $this->persisted[0]->getDescription();

        $logger->update();

        $this->assertSame($description, $this->persisted[0]->getDescription());
    }

    public function testUpdateAndDoneRequireStart(): void
    {
        $logger = $this->logger();

        try {
            $logger->update();
            $this->fail('update() without start() must fail');
        } catch (\LogicException $e) {
            $this->assertStringContainsString('start()', $e->getMessage());
        }

        $this->expectException(\LogicException::class);
        $logger->done();
    }

    public function testDisabledLoggerSilentlyIgnoresLifecycleCalls(): void
    {
        $logger = $this->logger('never');

        $this->assertNull($logger->start('x', []));
        $this->assertNull($logger->update());
        $this->assertNull($logger->done());
        $this->assertSame([], $this->persisted);
    }

    public function testArrayExtraParametersAreStoredAsJson(): void
    {
        $this->logger()->log('type', 'msg', null, null, ['k' => 'é']);

        $this->assertSame('{"k":"é"}', $this->persisted[0]->getExtraParameters());
    }

    public function testStartedActionIsFlushed(): void
    {
        $this->logger()->start('type', []);

        $this->assertSame(1, $this->flushes);
    }
}
