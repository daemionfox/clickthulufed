<?php

namespace App\Repository;

use App\Entity\CryptKey;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CryptKey>
 *
 * @method CryptKey|null find($id, $lockMode = null, $lockVersion = null)
 * @method CryptKey|null findOneBy(array $criteria, array $orderBy = null)
 * @method CryptKey[]    findAll()
 * @method CryptKey[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CryptKeyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CryptKey::class);
    }

//    /**
//     * @return CryptKey[] Returns an array of CryptKey objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?CryptKey
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
