<?php
namespace Kitpages\FileBundle\Tests\Entity;

use Kitpages\FileBundle\Entity\FileBase;
use Kitpages\FileBundle\Entity\FileBaseRepository;
use Kitpages\FileBundle\Entity\FileInterface;
use Liip\FunctionalTestBundle\Test\WebTestCase;

/**
 * @group console
 */
class FileBaseRepositoryTest
    extends WebTestCase
{
    private $client;
    public function setUp()
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->loadFixtures(array(
                'Kitpages\FileBundle\Tests\DataFixtures\ORM\LoadFileBaseData'
            ));
    }

    public function testFindByStatusAndItem()
    {
        /** @var FileBaseRepository $repo */
        $repo = $this->getContainer()->get("doctrine.orm.entity_manager")->getRepository('Kitpages\FileBundle\Entity\FileBase');
        $fileList = $repo->findByStatusAndItem(FileInterface::STATUS_TEMP, '\Kitpages\TestBundle\Entity\Toto', 12);
        $this->assertTrue(is_array($fileList));
        $this->assertEquals(1, count($fileList));
        $this->assertTrue($fileList[0] instanceof FileBase);
    }
}