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
                ->scalarNode('base_url')
                    ->defaultValue('data/bundle/kitpagesfile')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('entity_file_name_default')
                    ->defaultValue('Kitpages\FileBundle\Entity\File')
                    ->cannotBeEmpty()
                ->end()

                ->arrayNode('entity_file_name_list')
                    ->addDefaultsIfNotSet()
                    ->useAttributeAsKey('entity_file_name')->defaultValue(array('default' => array('class' =>  'Kitpages\FileBundle\Entity\File', 'data_dir_prefix' => '')))
                        ->prototype('array')
                        ->children()
                            ->scalarNode('class')
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('data_dir_prefix')
                                ->cannotBeEmpty()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('type_list')
                    ->useAttributeAsKey('type')
                        ->prototype('array')
                            ->useAttributeAsKey('action')
                                ->prototype('array')
                                ->children()
                                    ->scalarNode('url')
                                        ->defaultValue('')
                                        ->cannotBeEmpty()
                                    ->end()
                                    ->scalarNode('form')
                                        ->cannotBeEmpty()
                                    ->end()
                                    ->scalarNode('form_twig')
                                        ->cannotBeEmpty()
                                    ->end()
                                    ->scalarNode('handler_form')
                                        ->cannotBeEmpty()
                                    ->end()
                                    ->scalarNode('library')
                                        ->cannotBeEmpty()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

}