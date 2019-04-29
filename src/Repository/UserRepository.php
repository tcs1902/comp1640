<?php

namespace App\Repository;

use App\Entity\Faculty;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @param array $criteria
     * @param array $sort
     *
     * @return Pagerfanta
     */
    public function search(array $criteria, array $sort): Pagerfanta
    {
        $queryBuilder = $this->createQueryBuilder('u');
        if (isset($criteria['email'])) {
            $queryBuilder = $queryBuilder
                ->where('u.email LIKE :email')->setParameter('email', "{$criteria['email']}%");
        }
        foreach ($sort as $field => $direction) {
            $queryBuilder = $queryBuilder->orderBy("u.$field", $direction);
        }
        $adapter = new DoctrineORMAdapter($queryBuilder);

        return new Pagerfanta($adapter);
    }

    /**
     * @param string $role
     *
     * @return array
     */
    public function findByRole($role)
    {
        $qb = $this->createQueryBuilder('u')
            ->where('u.roles LIKE :roles')
            ->setParameter('roles', '%"'.$role.'"%');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param string $role
     *
     * @throws NonUniqueResultException
     *
     * @return array
     */
    public function findByRoleAndFaculty($role, $faculty)
    {
        $qb = $this->createQueryBuilder('u')
            ->where('u.roles LIKE :roles')
            ->andWhere('u.faculty = :faculty')
            ->setParameter('roles', '%"'.$role.'"%')
            ->setParameter('faculty', $faculty)
        ;

        return $qb->getQuery()->setMaxResults(1)->getOneOrNullResult();
    }

    /**
     * @param Faculty $faculty
     *
     * @throws NonUniqueResultException
     *
     * @return User
     */
    public function findOneCoordinatorByFaculty(Faculty $faculty): ?User
    {
        $qb = $this->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->andWhere('u.faculty = :faculty')
            ->setParameters([
                'role' => '%"'.User::ROLE_COORDINATOR.'"%',
                'faculty' => $faculty,
            ]);

        return $qb->getQuery()->setMaxResults(1)->getOneOrNullResult();
    }

    /**
     * @param Faculty $faculty
     *
     * @return mixed
     */
    public function findAllStudentsByFaculty(Faculty $faculty)
    {
        $qb = $this->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->andWhere('u.faculty = :faculty')
            ->setParameters([
                'role' => '%"'.User::ROLE_STUDENT.'"%',
                'faculty' => $faculty,
            ]);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Faculty $faculty
     *
     * @return mixed
     */
    public function findAllGuestsByFaculty(Faculty $faculty)
    {
        $qb = $this->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->andWhere('u.faculty = :faculty')
            ->setParameters([
                'role' => '%"'.User::ROLE_GUEST.'"%',
                'faculty' => $faculty,
            ]);

        return $qb->getQuery()->getResult();
    }
}
