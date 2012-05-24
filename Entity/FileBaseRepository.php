<?php
namespace Kitpages\FileBundle\Entity;
use Doctrine\ORM\EntityRepository;

class FileBaseRepository extends EntityRepository
{
    CONST entity = 'KitpagesFileBundle:FileBase';
    public function findByStatusAndItem($status, $itemClass, $itemId)
    {
        $calledClass = get_called_class();
        $listFile = $this->_em
            ->createQuery("
                SELECT f
                FROM ".$calledClass::entity." f
                WHERE f.status = :status
                  AND f.itemClass = :itemClass
                  AND f.itemId = :itemId
            ")
            ->setParameter("status", $status)
            ->setParameter("itemClass", $itemClass)
            ->setParameter("itemId", $itemId)
            ->getResult();
        return $listFile;
    }

}
