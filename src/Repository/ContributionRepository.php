<?php

namespace App\Repository;

use App\Entity\Contribution;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Contribution|null find($id, $lockMode = null, $lockVersion = null)
 * @method Contribution|null findOneBy(array $criteria, array $orderBy = null)
 * @method Contribution[]    findAll()
 * @method Contribution[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContributionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Contribution::class);
    }

    /**
     * @param array $criteria
     * @param array $sort
     *
     * @return Pagerfanta
     */
    public function search(array $criteria, array $sort): Pagerfanta
    {
        $queryBuilder = $this->createQueryBuilder('contribution');

        if (isset($criteria['author'])) {
            $queryBuilder
                ->where('contribution.author = :author')->setParameter('author', $criteria['author']);
        }

        if (isset($criteria['faculty'])) {
            $queryBuilder
                ->join('contribution.author', 'author')
                ->andWhere('author.faculty = :faculty')->setParameter('faculty', $criteria['faculty']);
        }

        if (isset($criteria['title'])) {
            $queryBuilder
                ->andWhere('contribution.title LIKE :title')->setParameter('title', "{$criteria['title']}%");
        }

        if (isset($criteria['published'])) {
            $published = $criteria['published'];
            if ($published) {
                $queryBuilder
                    ->andWhere($queryBuilder->expr()->isNotNull('contribution.publishedAt'));
            } else {
                $queryBuilder
                    ->andWhere($queryBuilder->expr()->isNull('contribution.publishedAt'));
            }
        }

        if (isset($criteria['approved'])) {
            $approved = $criteria['approved'];
            if ($approved) {
                $queryBuilder
                    ->andWhere($queryBuilder->expr()->isNotNull('contribution.approvedAt'));
            } else {
                $queryBuilder
                    ->andWhere($queryBuilder->expr()->isNull('contribution.approvedAt'));
            }
        }

        if (isset($criteria['term'])) {
            $queryBuilder
                ->andWhere('contribution.term = :term')->setParameter('term', $criteria['term']);
        }

        foreach ($sort as $field => $direction) {
            $queryBuilder->orderBy("contribution.$field", $direction);
        }
        $adapter = new DoctrineORMAdapter($queryBuilder);

        return new Pagerfanta($adapter);
    }

    public function findByFacultyAndByTerm($faculty, $term)
    {
        $qb = $this->createQueryBuilder('contribution')
            ->leftJoin('contribution.author', 'author')
            ->where('author.faculty = :faculty')
            ->andWhere('contribution.term = :term')
            ->setParameters([
                'faculty' => $faculty,
                'term' => $term,
            ]);

        return $qb->getQuery()->getResult();
    }

    public function findWithoutComment($faculty, $term)
    {
        $qb = $this->createQueryBuilder('contribution');
        $qb
            ->leftJoin('contribution.author', 'author');
        $qb
            ->where('author.faculty = :faculty')
            ->andWhere('contribution.term = :term')
            ->andWhere($qb->expr()->isNull('contribution.publishedAt'))
            ->setParameters([
                'faculty' => $faculty,
                'term' => $term,
            ]);

        return $qb->getQuery()->getResult();
    }

    public function findWithoutCommentAfter14days($faculty, $term)
    {
        $qb = $this->createQueryBuilder('contribution')
            ->leftJoin('contribution.author', 'author');
        $qb
            ->where('author.faculty = :faculty')
            ->andWhere('contribution.term = :term')
            ->andWhere($qb->expr()->isNull('contribution.publishedAt'))
            ->andWhere('contribution.agreedTermsAt < :time')
            ->setParameters([
                'faculty' => $faculty,
                'term' => $term,
                'time' => new \DateTime('-14 days'),
            ]);

        return $qb->getQuery()->getResult();
    }
}
