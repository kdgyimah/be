<?php

namespace App\Repository\School;

use App\Entity\School\School;
use App\Entity\School\Year;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Year|null find($id, $lockMode = null, $lockVersion = null)
 * @method Year|null findOneBy(array $criteria, array $orderBy = null)
 * @method Year[] findAll()
 * @method Year[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class YearRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Year::class);
    }

    /**
     * @param School $school
     * @return iterable<Year>
     */
    public function findBySchool(School $school): iterable
    {
        return $this->createQueryBuilder('y')
            ->andWhere('y.school = :school')
            ->setParameter('school', $school)
            ->orderBy('y.name', 'DESC')
            ->getQuery()
            ->toIterable();
    }
}
