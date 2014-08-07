<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Kitpages\FileBundle\KitpagesFileBundle(),
            new \Kitpages\UtilBundle\KitpagesUtilBundle(),
            new Kitpages\FileSystemBundle\KitpagesFileSystemBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Liip\FunctionalTestBundle\LiipFunctionalTestBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
        );
        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
