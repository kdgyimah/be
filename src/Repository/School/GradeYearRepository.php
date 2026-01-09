<?php

namespace App\Repository\School;

use App\Entity\School\GradeYear;
use App\Entity\School\Year;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method GradeYear|null find($id, $lockMode = null, $lockVersion = null)
 * @method GradeYear|null findOneBy(array $criteria, array $orderBy = null)
 * @method GradeYear[]    findAll()
 * @method GradeYear[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GradeYearRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GradeYear::class);
    }

    /**
     * @return Paginator<GradeYear>
     */
    public function findByYear(Year $year, int $page, int $count): Paginator
    {
        return new Paginator(
            $this->getQueryByYear($year)
                ->setFirstResult(($page - 1) * $count)
                ->setMaxResults($count),
            false
        );
    }

    public function countByYear(Year $year): int
    {
        return $this->getQueryByYear($year)
            ->select('count(gy.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function getQueryByYear(Year $year): QueryBuilder
    {
        return $this->createQueryBuilder('gy')
            ->select('gy, g, s')
            ->innerJoin('gy.grade', 'g')
            ->innerJoin('gy.student', 's')
            ->where('gy.year = :year')
            ->setParameter('year', $year);
    }
}
