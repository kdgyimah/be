<?php

namespace App\Entity\School;

use App\Entity\AbstractUserScope;
use App\Entity\User;
use App\Enum\SchoolScope;
use App\Repository\School\UserScopeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserScopeRepository::class)]
#[ORM\Table(name: 'school_user_scope')]
final class UserScope extends AbstractUserScope
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
