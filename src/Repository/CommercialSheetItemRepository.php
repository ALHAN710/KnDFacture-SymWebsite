<?php

namespace App\Repository;

use App\Entity\CommercialSheetItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CommercialSheetItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommercialSheetItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommercialSheetItem[]    findAll()
 * @method CommercialSheetItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommercialSheetItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommercialSheetItem::class);
    }

    // /**
    //  * @return CommercialSheetItem[] Returns an array of CommercialSheetItem objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CommercialSheetItem
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
