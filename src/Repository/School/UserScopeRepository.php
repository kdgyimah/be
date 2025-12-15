<?php

namespace App\Repository\School;

use App\Entity\School\UserScope;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UuidType;

/**
 * @method UserScope|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserScope|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserScope[] findAll()
 * @method UserScope[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserScopeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserScope::class);
    }

    /**
     * @param User $user
     * @return iterable<UserScope>
     */
    public function findSchools(User $user): iterable
    {
        return $this->createQueryBuilder('sus')
            ->leftJoin('sus.school', 's')
            ->where('sus.user = :user')
            ->setParameter('user', $user->id, UuidType::NAME)
            ->getQuery()
            ->toIterable();
    }
}
