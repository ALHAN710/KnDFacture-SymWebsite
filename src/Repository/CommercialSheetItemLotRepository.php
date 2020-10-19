<?php

namespace App\Repository;

use App\Entity\CommercialSheetItemLot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CommercialSheetItemLot|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommercialSheetItemLot|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommercialSheetItemLot[]    findAll()
 * @method CommercialSheetItemLot[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommercialSheetItemLotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommercialSheetItemLot::class);
    }

    // /**
    //  * @return CommercialSheetItemLot[] Returns an array of CommercialSheetItemLot objects
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
    public function findOneBySomeField($value): ?CommercialSheetItemLot
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
