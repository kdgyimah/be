<?php

namespace App\Repository\School;

use App\Entity\School\StudentYear;
use App\Entity\School\Year;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method StudentYear|null find($id, $lockMode = null, $lockVersion = null)
 * @method StudentYear|null findOneBy(array $criteria, array $orderBy = null)
 * @method StudentYear[] findAll()
 * @method StudentYear[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StudentYearRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StudentYear::class);
    }

    public function findByYear(Year $year, int $page, int $count): Paginator
    {
        $qb = $this->createQueryBuilder('sy')
            ->where('sy.year = :year')
            ->setParameter('year', $year)
            ->setFirstResult(($page - 1) * $count)
            ->setMaxResults($count)
            ->getQuery();

        return new Paginator($qb, false);
    }

    public function countByYear(Year $year): int
    {
        return $this->createQueryBuilder('sy')
            ->select('count(sy.id)')
            ->where('sy.year = :year')
            ->setParameter('year', $year)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
