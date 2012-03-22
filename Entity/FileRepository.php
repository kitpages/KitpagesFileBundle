<?php
namespace Kitpages\FileBundle\Entity;
use Doctrine\ORM\EntityRepository;
use  Kitpages\FileBundle\Entity\FileBaseRepository;

class FileRepository extends FileBaseRepository
{
    CONST entity = 'KitpagesFileBundle:File';
}
