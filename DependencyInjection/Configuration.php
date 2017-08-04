<?php

namespace AgileFileUploadBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * AgileFileUploadBundle Configuration
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('agile_file_upload');

        $rootNode
            ->children()
                ->arrayNode('model')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('file_class')->defaultValue('AgileFileUploadBundle\Model\File')->cannotBeEmpty()->end()
                        ->scalarNode('file_table')->defaultValue('files')->cannotBeEmpty()->end()
                    ->end()
                ->end()
                ->arrayNode('repository')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('file')->defaultValue('AgileFileUploadBundle\Repository\FileRepository')->cannotBeEmpty()->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
