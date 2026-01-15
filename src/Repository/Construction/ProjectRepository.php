<?php

namespace App\Repository\Construction;

use App\Entity\Construction\Company;
use App\Entity\Construction\Project;
use App\Repository\Trait\ConstructionTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Project|null find($id, $lockMode = null, $lockVersion = null)
 * @method Project|null findOneBy(array $criteria, array $orderBy = null)
 * @method Project[]    findAll()
 * @method Project[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectRepository extends ServiceEntityRepository
{
    use ConstructionTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    /**
     * @param Company $company
     * @param int $page
     * @param int $count
     * @return iterable<Project>
     */
    public function findByCompany(Company $company, int $page, int $count): iterable
    {
        return $this->getQueryByCompany($company, 'p')
            ->setFirstResult(($page - 1) * $count)
            ->setMaxResults($count)
            ->getQuery()
            ->toIterable();
    }

    public function countByCompany(Company $company): int
    {
        return $this->getQueryByCompany($company, 'p')
            ->select('count(p.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
