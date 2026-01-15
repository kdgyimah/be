<?php

namespace App\Repository\Trait;

use App\Entity\Construction\Company;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Types\UuidType;

trait ConstructionTrait
{
    abstract public function createQueryBuilder(string $alias, ?string $indexBy = null): QueryBuilder;

    private function getQueryByCompany(Company $company, string $alias): QueryBuilder
    {
        return $this->createQueryBuilder($alias)
            ->where("$alias.company = :company")
            ->setParameter('company', $company->id, UuidType::NAME);
    }
}
