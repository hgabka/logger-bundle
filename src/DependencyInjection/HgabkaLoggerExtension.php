<?php

namespace Hgabka\LoggerBundle\DependencyInjection;

use Hgabka\LoggerBundle\Helper\ExceptionNotifier;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class HgabkaLoggerExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration($container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $loggerDefinition = $container->getDefinition('hgabka_logger.exception_logger');
        $loggerDefinition->replaceArgument(2, $config['notifier']['logging']['log_path']);

        $columnLoggerDefinition = $container->getDefinition('hgabka_logger.column_logger');
        $columnLoggerDefinition->replaceArgument(6, $config['column_logger']['common_identifier']);
        $columnLoggerDefinition->replaceArgument(7, $config['column_logger']['translation_domain']);
        $columnLoggerDefinition->replaceArgument(8, $config['column_logger']['enabled']);

        $actionLoggerDefinition = $container->getDefinition('hgabka_logger.action_logger');
        $actionLoggerDefinition->replaceArgument(6, $config['action_logger']['common_identifier']);
        $actionLoggerDefinition->replaceArgument(7, $config['action_logger']['translation_domain']);
        $actionLoggerDefinition->replaceArgument(8, $config['action_logger']['enabled']);

        $notifierDefinition = $container->getDefinition(ExceptionNotifier::class);
        $notifierDefinition->addMethodCall('setConfig', [$config['notifier']]);
    }
}
