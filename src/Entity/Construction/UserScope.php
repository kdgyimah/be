<?php

namespace App\Entity\Construction;

use App\Entity\AbstractUserScope;
use App\Entity\User;
use App\Listener\TimestampEntityListener;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity]
#[ORM\UniqueConstraint(fields: ['user', 'construction', 'scope'])]
#[UniqueEntity(fields: ['user', 'construction', 'scope'])]
#[ORM\EntityListeners([TimestampEntityListener::class])]
final class UserScope extends AbstractUserScope
{
    #[ORM\ManyToOne(targetEntity: Company::class)]
    protected Company $company;

    /**
     * @param User $user
     * @param Company $constructionCompany
     * @param list<string> $scopes
     */
    public function __construct(User $user, Company $constructionCompany, array $scopes)
    {
        parent::__construct($user, $scopes);
        $this->company = $constructionCompany;
    }

    public function getScope(): Company
    {
        return $this->company;
    }
}
