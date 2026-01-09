<?php

namespace App\Repository\School;

use App\Entity\School\School;
use App\Entity\School\UserScope;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UuidType;

/**
 * @method School|null find($id, $lockMode = null, $lockVersion = null)
 * @method School|null findOneBy(array $criteria, array $orderBy = null)
 * @method School[]    findAll()
 * @method School[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SchoolRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, School::class);
    }

    /**
     * @param User $user
     * @return iterable<School>
     */
    public function findByUser(User $user): iterable
    {
        return $this->createQueryBuilder('s')
            ->innerJoin(UserScope::class, 'us', Join::WITH, 's.id = us.school')
            ->where('us.user = :user')
            ->setParameter('user', $user->id, UuidType::NAME)
            ->getQuery()
            ->toIterable();
    }
}
