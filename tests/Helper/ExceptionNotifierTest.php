<?php

namespace Hgabka\LoggerBundle\Tests\Helper;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Hgabka\LoggerBundle\Helper\ExceptionNotifier;
use Hgabka\LoggerBundle\Logger\ExceptionLogger;
use Hgabka\UtilsBundle\Helper\HgabkaUtils;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ExceptionNotifierTest extends TestCase
{
    /** @var list<Email> */
    private array $sent = [];

    private function notifier(array $config, bool $debug = false): ExceptionNotifier
    {
        $mailer = $this->createStub(MailerInterface::class);
        $mailer->method('send')->willReturnCallback(function (Email $mail): void {
            $this->sent[] = $mail;
        });
        $utils = $this->createStub(HgabkaUtils::class);
        $utils->method('getHost')->willReturn('example.test');

        $notifier = new ExceptionNotifier(
            $this->createStub(Registry::class),
            $mailer,
            new RequestStack(),
            $this->createStub(ExceptionLogger::class),
            $utils,
            $debug
        );

        return $notifier->setConfig($config);
    }

    private static function config(string $mails = 'prod', string $logging = 'prod', array $type = ['debug' => 'none', 'prod' => 'both'], array $extra = []): array
    {
        return [
            'mails' => array_merge([
                'enabled' => $mails,
                'from_mail' => 'from@example.com',
                'from_name' => 'Notifier',
                'subject' => 'Exception on [host]',
                'recipients' => ['dev@example.com'],
                'send_only_if_new' => false,
                'send_404' => true,
            ], $extra),
            'logging' => ['enabled' => $logging, 'type' => $type, 'log_404' => true],
        ];
    }

    #[DataProvider('environments')]
    public function testEnabledFlagsFollowEnvironment(string $setting, bool $debug, bool $expected): void
    {
        $notifier = $this->notifier(self::config($setting, $setting), $debug);

        $this->assertSame($expected, $notifier->isMailSendingEnabled());
        $this->assertSame($expected, $notifier->isLoggingEnabled());
        $this->assertSame($expected, $notifier->isEnabled());
    }

    public static function environments(): array
    {
        return [
            ['always', false, true],
            ['always', true, true],
            ['prod', false, true],
            ['prod', true, false],
            ['debug', false, false],
            ['debug', true, true],
            ['never', false, false],
            ['never', true, false],
        ];
    }

    #[DataProvider('logTypes')]
    public function testLogTargetsFollowConfiguredType(string $type, bool $file, bool $database): void
    {
        $notifier = $this->notifier(self::config('never', 'always', ['debug' => $type, 'prod' => $type]));

        $this->assertSame($file, $notifier->isFileLoggingEnabled());
        $this->assertSame($database, $notifier->isDatabaseLoggingEnabled());
    }

    public static function logTypes(): array
    {
        return [
            'file' => ['file', true, false],
            'database' => ['database', false, true],
            'both' => ['both', true, true],
            'none' => ['none', false, false],
        ];
    }

    public function testLogTargetsAreOffWhenLoggingIsDisabled(): void
    {
        $notifier = $this->notifier(self::config('never', 'never', ['debug' => 'both', 'prod' => 'both']));

        $this->assertFalse($notifier->isFileLoggingEnabled());
        $this->assertFalse($notifier->isDatabaseLoggingEnabled());
    }

    public function testTriggerDoesNothingWhenEverythingIsDisabled(): void
    {
        $this->notifier(self::config('never', 'never'))->trigger(new \RuntimeException('boom'));

        $this->assertSame([], $this->sent);
    }

    public function testTriggerSendsMailWhenDatabaseLoggingIsOff(): void
    {
        $notifier = $this->notifier(self::config('always', 'never'));

        $notifier->trigger(new \RuntimeException('boom'));

        $this->assertCount(1, $this->sent);
        $mail = $this->sent[0];
        $this->assertSame('Exception on example.test', $mail->getSubject());
        $this->assertSame('dev@example.com', $mail->getTo()[0]->getAddress());
        $this->assertSame('from@example.com', $mail->getFrom()[0]->getAddress());
        $this->assertStringContainsString('boom', $mail->getHtmlBody());
    }

    public function testNotFoundExceptionsDoNotSendMailWhen404MailsAreOff(): void
    {
        $notifier = $this->notifier(self::config('always', 'never', ['debug' => 'none', 'prod' => 'none'], ['send_404' => false]));

        $notifier->trigger(new NotFoundHttpException('nope'));

        $this->assertSame([], $this->sent);
    }

    public function testWarningMailUsesDefaultsAndPlaceholders(): void
    {
        $notifier = $this->notifier(['mails' => ['enabled' => 'always']]);

        $notifier->sendWarningMail('Problem on [host]', 'body');

        $mail = $this->sent[0];
        $this->assertStringContainsString('example.test', $mail->getSubject());
        $this->assertStringContainsString('Problem on example.test', $mail->getHtmlBody());
        $this->assertStringContainsString('body', $mail->getHtmlBody());
        $this->assertSame('hgabka@gmail.com', $mail->getTo()[0]->getAddress());
        $this->assertSame('hgLoggerBundle', $mail->getFrom()[0]->getName());
    }

    public function testWarningMailExplicitSubjectWins(): void
    {
        $notifier = $this->notifier(self::config());

        $notifier->sendWarningMail('m', 'b', 'Custom [host]');

        $this->assertSame('Custom example.test', $this->sent[0]->getSubject());
    }
}
