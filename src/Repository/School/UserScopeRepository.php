<?php

namespace App\Repository\School;

use App\Entity\School\School;
use App\Entity\School\UserScope;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserScope|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserScope|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserScope[]    findAll()
 * @method UserScope[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserScopeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserScope::class);
    }

    public function deleteRoles(School $school, User $user): void
    {
        $this->createQueryBuilder('us')
            ->delete()
            ->where('us.school = :school')
            ->andWhere('us.user = :user')
            ->setParameter('school', $school)
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }
}
