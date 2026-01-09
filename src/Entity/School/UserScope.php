<?php

namespace App\Entity\School;

use App\Entity\AbstractUserScope;
use App\Entity\User;
use App\Enum\SchoolScope;
use App\Listener\TimestampEntityListener;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity]
#[ORM\UniqueConstraint(fields: ['user', 'school', 'scope'])]
#[UniqueEntity(fields: ['user', 'school', 'scope'])]
#[ORM\EntityListeners([TimestampEntityListener::class])]
final class UserScope extends AbstractUserScope
{
    #[ORM\ManyToOne(targetEntity: School::class)]
    protected School $school;

    public function __construct(User $user, School $school, SchoolScope $schoolScope)
    {
        parent::__construct($user, $schoolScope);
        $this->school = $school;
    }

    public function getScope(): SchoolScope
    {
        return SchoolScope::from($this->getStringScope());
    }
}
