<?php
namespace Kitpages\FileBundle\Entity;
use Doctrine\ORM\EntityRepository;

class FileBaseRepository extends EntityRepository
{
    CONST entity = 'KitpagesFileBundle:FileBase';
    public function findByStatusAndItem($status, $itemClass, $itemId)
    {
        $calledClass = get_called_class();
        $qb = $this->_em->createQueryBuilder();
        $qb
            ->select('f')
            ->from($calledClass::entity, 'f')
            ->where('f.status = :status')
            ->andWhere('f.itemClass = :itemClass')
            ->andWhere('f.itemId = :itemId')
            ->setParameter("status", $status)
            ->setParameter("itemClass", $itemClass)
            ->setParameter("itemId", $itemId)
        ;
        $listFile = $qb->getQuery()->getResult();
        return $listFile;
    }

}
