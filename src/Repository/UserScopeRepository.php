<?php

namespace App\Repository;

use App\Entity\AbstractUserScope;
use App\Entity\Construction\Company;
use App\Entity\Construction\UserScope as ConstructionUserScope;
use App\Entity\School\School;
use App\Entity\School\UserScope as SchoolUserScope;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AbstractUserScope|null find($id, $lockMode = null, $lockVersion = null)
 * @method AbstractUserScope|null findOneBy(array $criteria, array $orderBy = null)
 * @method AbstractUserScope[]    findAll()
 * @method AbstractUserScope[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserScopeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AbstractUserScope::class);
    }

    public function findByUserAndCompany(User $user, Company $company): ?ConstructionUserScope
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('us')
            ->from(ConstructionUserScope::class, 'us')
            ->where('us.user = :user')
            ->andWhere('us.company = :company')
            ->setParameter('user', $user)
            ->setParameter('company', $company)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByUserAndSchool(User $user, School $school): ?SchoolUserScope
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('us')
            ->from(SchoolUserScope::class, 'us')
            ->where('us.user = :user')
            ->andWhere('us.school = :school')
            ->setParameter('user', $user)
            ->setParameter('school', $school)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
