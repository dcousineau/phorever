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
                            ->scalarNode('name')->cannotBeEmpty()->end()
                            ->arrayNode('roles')->prototype('scalar')->end()->end()
                            ->scalarNode('up')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('resurrect_after')->defaultValue(5)->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}