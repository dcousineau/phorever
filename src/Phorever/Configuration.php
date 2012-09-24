<?php
namespace Phorever;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('phorever');

        $rootNode
            ->children()
                ->scalarNode('pidfile')
                    ->defaultValue('./phorever.pid')
                ->end()
                ->arrayNode('processes')
                    ->prototype('array')
                        ->children()
                            //description & metadata
                            ->scalarNode('name')->cannotBeEmpty()->end()
                            ->arrayNode('roles')->prototype('scalar')->end()->end()
                            //process information
                            ->scalarNode('up')->isRequired()->cannotBeEmpty()->end()
                            //failure management
                            ->scalarNode('resurrect_after')->defaultValue(5)->end()
                            //log files
                            ->booleanNode('enable_logging')->defaultValue(true)->end()
                            ->scalarNode('log_file')->defaultValue('./logs/%name%.log')->cannotBeEmpty()->end()
                            ->scalarNode('errorlog_file')->defaultValue('./logs/%name%.err.log')->cannotBeEmpty()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}