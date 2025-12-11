<?php

namespace App\Repository;

use App\Entity\School\SchoolUserScope;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UuidType;

/**
 * @method SchoolUserScope|null find($id, $lockMode = null, $lockVersion = null)
 * @method SchoolUserScope|null findOneBy(array $criteria, array $orderBy = null)
 * @method SchoolUserScope[] findAll()
 * @method SchoolUserScope[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SchoolUserScopeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SchoolUserScope::class);
    }

    /**
     * @param User $user
     * @return array<SchoolUserScope>
     */
    public function findSchools(User $user): array
    {
        return $this->createQueryBuilder('sus')
            ->leftJoin('sus.school', 's')
            ->where('sus.user = :user')
            ->setParameter('user', $user->id, UuidType::NAME)
            ->getQuery()
            ->getResult();
    }
}
