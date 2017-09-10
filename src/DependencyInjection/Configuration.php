<?php

namespace Hgabka\LoggerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('hgabka_logger');

        $rootNode
            ->children()
                ->arrayNode('notifier')
                    ->addDefaultsIfNotSet()
                    ->children()
                          ->arrayNode('mails')
            ->addDefaultsIfNotSet()
                              ->children()
                                  ->booleanNode('enabled')->defaultValue(true)->end()
                              ->end()
                          ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
