<?php
namespace Kitpages\FileBundle\Tests\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Kitpages\FileBundle\Entity\FileBase;
use Kitpages\FileBundle\Entity\FileInterface;
use Symfony\Component\DependencyInjection\ContainerAware;


class LoadFileBaseData
    extends ContainerAware
    implements FixtureInterface, OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $fileBase = new FileBase();
        $fileBase->setFileName('tutu.pdf');
        $fileBase->setStatus(FileInterface::STATUS_TEMP);
        $fileBase->setItemClass('\Kitpages\TestBundle\Entity\Toto');
        $fileBase->setItemId(12);
        $fileBase->setIsPrivate(false);
        $manager->persist($fileBase);
        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 4;
    }
}