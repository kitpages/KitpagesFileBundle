<?php

namespace Kitpages\FileBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration processer.
 * Parses/validates the extension configuration and sets default values.
 *
 * @author Philippe Le Van (@plv)
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree.
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('kitpages_file');

        $this->addMainSection($rootNode);

        return $treeBuilder;
    }

    /**
     * Parses the kitpages_file.xxx config section
     * Example for yaml driver:
     * kitpages_file:
     *     data_dir: /tmp/data
     *
     * @param ArrayNodeDefinition $node
     * @return void
     */
    private function addMainSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->scalarNode('data_dir')
                    ->defaultValue('%kernel.root_dir%/data/bundle/kitpagesfile')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('public_prefix')
                    ->defaultValue('data/bundle/kitpagesfile')
                    ->cannotBeEmpty()
                ->end()
            ->end();
    }

}