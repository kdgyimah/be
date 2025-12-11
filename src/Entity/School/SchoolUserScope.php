<?php

namespace App\Entity\School;

use App\Entity\AbstractUserScope;
use App\Entity\User;
use App\Enum\SchoolScope;
use App\Repository\SchoolUserScopeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SchoolUserScopeRepository::class)]
final class SchoolUserScope extends AbstractUserScope
{
    #[ORM\Id]
    #[ORM\Column(type: Types::STRING, enumType: SchoolScope::class)]
    private SchoolScope $scope;

    public function __construct(User $user, School $school, SchoolScope $schoolScope)
    {
        parent::__construct($user, $school);
        $this->scope = $schoolScope;
    }

    public function getScope(): SchoolScope
    {
        return $this->scope;
    }
}
