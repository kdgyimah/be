<?php

namespace App\Entity\School;

use App\Entity\AbstractUserScope;
use App\Entity\User;
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

    /**
     * @param User $user
     * @param School $school
     * @param list<string> $scopes
     */
    public function __construct(User $user, School $school, array $scopes)
    {
        parent::__construct($user, $scopes);
        $this->school = $school;
    }
}
