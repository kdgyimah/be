<?php

namespace App\Repository\Construction;

use App\Entity\Construction\Company;
use App\Entity\Construction\UserScope;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Company|null find($id, $lockMode = null, $lockVersion = null)
 * @method Company|null findOneBy(array $criteria, array $orderBy = null)
 * @method Company[]    findAll()
 * @method Company[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Company::class);
    }

    /**
     * @param User $user
     * @return iterable<Company>
     */
    public function findByUser(User $user): iterable
    {
        return $this->createQueryBuilder('c')
            ->select('DISTINCT c')
            ->innerJoin(UserScope::class, 'us', Join::WITH, 'c.id = us.company')
            ->where('us.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->toIterable();

    }
}
