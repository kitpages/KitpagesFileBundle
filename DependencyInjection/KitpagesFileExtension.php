<?php

/*
 * This file is part of the Kitpages File Project
 *
 * (c) Philippe Le Van (@plv)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kitpages\FileBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;

use Kitpages\FileBundle\DependencyInjection\Configuration;

/**
 * KitpagesCmsBundleExtension
 *
 * @author      Philippe Le Van (@plv)
 */
class KitpagesFileExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $this->remapParameters($config, $container, array(
            'tmp_dir'  => 'kitpages_file.tmp_dir'
        ));
        $this->remapParameters($config, $container, array(
            'entity_file_name_list'  => 'kitpages_file.entity_file_name_list'
        ));
        $this->remapParameters($config, $container, array(
            'entity_file_name_default'  => 'kitpages_file.entity_file_name_default'
        ));
        $this->remapParameters($config, $container, array(
            'type_list'  => 'kitpages_file.type_list'
        ));


        $typeList = $container->getParameter('kitpages_file.type_list');

        foreach($typeList as $type => $actionList) {
            foreach($actionList as $action => $actionInfo) {
                $loader->load($type.'/'.$action.'.xml');
                $container->setAlias('kitpages_file.'.$type.'.'.$action.'.library', new Alias('kitpages.file.'.$actionInfo['library']));
            }
        }
    }

    public function getAlias()
    {
        return "kitpages_file";
    }
    /**
     * Dynamically remaps parameters from the config values
     *
     * @param array            $config
     * @param ContainerBuilder $container
     * @param array            $namespaces
     * @return void
     */
    protected function remapParametersNamespaces(array $config, ContainerBuilder $container, array $namespaces)
    {
        foreach ($namespaces as $ns => $map) {
            if ($ns) {
                if (!isset($config[$ns])) {
                    continue;
                }
                $namespaceConfig = $config[$ns];
            } else {
                $namespaceConfig = $config;
            }
            if (is_array($map)) {
                $this->remapParameters($namespaceConfig, $container, $map);
            } else {
                foreach ($namespaceConfig as $name => $value) {
                    if (null !== $value) {
                        $container->setParameter(sprintf($map, $name), $value);
                    }
                }
            }
        }
    }

    /**
     *
     * @param array            $config
     * @param ContainerBuilder $container
     * @param array            $map
     * @return void
     */
    protected function remapParameters(array $config, ContainerBuilder $container, array $map)
    {
        foreach ($map as $name => $paramName) {
            if (isset($config[$name])) {
                $container->setParameter($paramName, $config[$name]);
            }
        }
    }

}