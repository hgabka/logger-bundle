<?php

namespace Hgabka\LoggerBundle\Tests\DependencyInjection;

use Hgabka\LoggerBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ConfigurationTest extends TestCase
{
    private function process(array $config = []): array
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.logs_dir', '/var/logs');

        return (new Processor())->processConfiguration(new Configuration($container), [$config]);
    }

    public function testDefaults(): void
    {
        $config = $this->process();

        $this->assertSame('prod', $config['notifier']['mails']['enabled']);
        $this->assertSame('info@hgabka.eu', $config['notifier']['mails']['from_mail']);
        $this->assertSame([], $config['notifier']['mails']['recipients']);
        $this->assertFalse($config['notifier']['mails']['send_only_if_new']);
        $this->assertTrue($config['notifier']['mails']['send_404']);

        $this->assertSame('prod', $config['notifier']['logging']['enabled']);
        $this->assertSame(['debug' => 'none', 'prod' => 'both'], $config['notifier']['logging']['type']);
        $this->assertSame('/var/logs/exception', $config['notifier']['logging']['log_path']);
        $this->assertTrue($config['notifier']['logging']['log_404']);

        foreach (['action_logger', 'column_logger'] as $logger) {
            $this->assertSame('logger', $config[$logger]['translation_domain']);
            $this->assertSame('symfony', $config[$logger]['common_identifier']);
            $this->assertSame('prod', $config[$logger]['enabled']);
        }
    }

    public function testLoggingTypeStringAppliesToBothEnvironments(): void
    {
        $config = $this->process(['notifier' => ['logging' => ['type' => 'file']]]);

        $this->assertSame(['debug' => 'file', 'prod' => 'file'], $config['notifier']['logging']['type']);
    }

    public function testInvalidEnabledValueIsRejected(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->process(['action_logger' => ['enabled' => 'sometimes']]);
    }

    public function testInvalidLoggingTypeIsRejected(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->process(['notifier' => ['logging' => ['type' => ['prod' => 'cloud']]]]);
    }

    public function testRecipientsAreCollected(): void
    {
        $config = $this->process(['notifier' => ['mails' => ['recipients' => ['a@example.com', 'b@example.com']]]]);

        $this->assertSame(['a@example.com', 'b@example.com'], $config['notifier']['mails']['recipients']);
    }
}
