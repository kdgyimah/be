<?php

namespace App\Entity\Construction;

use App\Entity\AbstractUserScope;
use App\Entity\User;
use App\Enum\ConstructionScope;
use App\Listener\TimestampEntityListener;
use App\Repository\Construction\UserScopeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: UserScopeRepository::class)]
#[ORM\UniqueConstraint(fields: ['user', 'construction', 'scope'])]
#[UniqueEntity(fields: ['user', 'construction', 'scope'])]
#[ORM\EntityListeners([TimestampEntityListener::class])]
final class UserScope extends AbstractUserScope
{
    #[ORM\ManyToOne(targetEntity: Company::class)]
    protected Company $company;

    public function __construct(User $user, Company $constructionCompany, ConstructionScope $scope)
    {
        parent::__construct($user, $scope);
        $this->company = $constructionCompany;
    }

    public function getScope(): Company
    {
        return $this->company;
    }
}
