<?php

namespace Dafuer\JpgraphBundle\DependencyInjection;

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
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('dafuer_jpgraph');

        
        $rootNode
                ->children()
                    ->arrayNode('constants')
                    ->useAttributeAsKey('file')
                    ->prototype('array')
                        ->useAttributeAsKey('file')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                
                    ->arrayNode('graph_viewer_default')
                        ->canBeUnset()
                        ->useAttributeAsKey('key')
                            ->prototype('scalar')->end()      
                         ->end()
                    ->end()
                ->end()
            
                ;

        return $treeBuilder;
    }
}
