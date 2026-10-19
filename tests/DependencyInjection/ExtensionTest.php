<?php

namespace Hgabka\LoggerBundle\Tests\DependencyInjection;

use Hgabka\LoggerBundle\DependencyInjection\HgabkaLoggerExtension;
use Hgabka\LoggerBundle\Helper\ExceptionNotifier;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ExtensionTest extends TestCase
{
    private function load(array $config = []): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.logs_dir', '/var/logs');
        (new HgabkaLoggerExtension())->load([$config], $container);

        return $container;
    }

    public function testConfigIsInjectedIntoServices(): void
    {
        $container = $this->load([
            'action_logger' => ['common_identifier' => 'shop', 'translation_domain' => 'acts', 'enabled' => 'always'],
            'column_logger' => ['common_identifier' => 'crm', 'enabled' => 'never'],
            'notifier' => ['logging' => ['log_path' => '/tmp/exc']],
        ]);

        $this->assertSame('/tmp/exc', $container->getDefinition('hgabka_logger.exception_logger')->getArgument(2));
        $this->assertSame(['shop', 'acts', 'always'], array_slice($container->getDefinition('hgabka_logger.action_logger')->getArguments(), 6, 3));
        $this->assertSame(['crm', 'logger', 'never'], array_slice($container->getDefinition('hgabka_logger.column_logger')->getArguments(), 6, 3));
    }

    public function testNotifierReceivesNotifierConfig(): void
    {
        $container = $this->load(['notifier' => ['mails' => ['enabled' => 'never']]]);

        $calls = $container->getDefinition(ExceptionNotifier::class)->getMethodCalls();
        $this->assertSame('setConfig', $calls[0][0]);
        $this->assertSame('never', $calls[0][1][0]['mails']['enabled']);
    }

    public function testListenersAreTagged(): void
    {
        $container = $this->load();

        $this->assertArrayHasKey('kernel.event_listener', $container->getDefinition('hgabka_logger.exception_listener')->getTags());
        $this->assertArrayHasKey('doctrine.event_listener', $container->getDefinition('hgabka_logger.column_log_listener')->getTags());
        $this->assertArrayHasKey('kernel.event_subscriber', $container->getDefinition('hgabka_logger.action_log_subscriber')->getTags());
    }
}
