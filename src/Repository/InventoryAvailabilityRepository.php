<?php

namespace App\Repository;

use App\Entity\InventoryAvailability;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method InventoryAvailability|null find($id, $lockMode = null, $lockVersion = null)
 * @method InventoryAvailability|null findOneBy(array $criteria, array $orderBy = null)
 * @method InventoryAvailability[]    findAll()
 * @method InventoryAvailability[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InventoryAvailabilityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InventoryAvailability::class);
    }

    // /**
    //  * @return InventoryAvailability[] Returns an array of InventoryAvailability objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?InventoryAvailability
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
