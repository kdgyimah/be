<?php

namespace App\Repository\Construction;

use App\Entity\Construction\UserScope;
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
}
