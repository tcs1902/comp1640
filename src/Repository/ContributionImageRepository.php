<?php

namespace App\Repository;

use App\Entity\ContributionImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ContributionImage|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContributionImage|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContributionImage[]    findAll()
 * @method ContributionImage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContributionImageRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ContributionImage::class);
    }

    // /**
    //  * @return ContributionImage[] Returns an array of ContributionImage objects
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
    public function findOneBySomeField($value): ?ContributionImage
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
