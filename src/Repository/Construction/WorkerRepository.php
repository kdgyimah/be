<?php

namespace App\Repository\Construction;

use App\Entity\Construction\Company;
use App\Entity\Construction\Worker;
use App\Repository\Trait\ConstructionTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Worker|null find($id, $lockMode = null, $lockVersion = null)
 * @method Worker|null findOneBy(array $criteria, array $orderBy = null)
 * @method Worker[]    findAll()
 * @method Worker[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WorkerRepository extends ServiceEntityRepository
{
    use ConstructionTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Worker::class);
    }

    /**
     * @return Paginator<Worker>
     */
    public function findByCompany(Company $company, int $page, int $count): Paginator
    {
        $qb = $this->getQueryByCompany($company, 'w')
            ->setFirstResult(($page - 1) * $count)
            ->setMaxResults($count);

        return new Paginator($qb);
    }

    public function countByCompany(Company $company): int
    {
        return $this->getQueryByCompany($company, 'w')
            ->select('count(w.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
