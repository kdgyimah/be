<?php

namespace App\Repository\Construction;

use App\Entity\Construction\Company;
use App\Entity\Construction\Engineer;
use App\Entity\Construction\Project;
use App\Enum\ConstructionProjectState;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UuidType;

/**
 * @method Engineer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Engineer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Engineer[]    findAll()
 * @method Engineer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EngineerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Engineer::class);
    }

    /**
     * @return iterable<Engineer>
     */
    public function findByCompany(Company $company, int $page, int $count): iterable
    {
        $qb = $this->getQueryByCompany($company)
            ->leftJoin(Project::class, 'p', Join::ON, 'p.engineer = e AND p.state = :state')
            ->select('e, p')
            ->setParameter('state', ConstructionProjectState::RUNNING)
            ->setFirstResult(($page - 1) * $count)
            ->setMaxResults($count);

        return $qb->getQuery()->toIterable();
    }

    public function countByCompany(Company $company): int
    {
        return $this->getQueryByCompany($company)
            ->select('count(e.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function getQueryByCompany(Company $company): QueryBuilder
    {
        return $this->createQueryBuilder('e')
            ->where('e.company = :company')
            ->setParameter('company', $company->id, UuidType::NAME);
    }
}
