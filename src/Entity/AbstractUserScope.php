<?php

namespace App\Entity;

use App\Entity\School\School;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\DatePointType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Clock\DatePoint;

#[ORM\MappedSuperclass]
#[UniqueEntity(fields: ['user', 'school', 'scope'])]
#[UniqueEntity(fields: ['school', 'scope'])]
abstract class AbstractUserScope
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: User::class)]
    private(set) User $user;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: School::class)]
    private(set) School $school;

    #[ORM\Column(type: DatePointType::NAME)]
    private(set) DatePoint $createdAt;

    public function __construct(User $user, School $school)
    {
        $this->user = $user;
        $this->school = $school;
        $this->createdAt = new DatePoint();
    }

    abstract public function getScope(): \BackedEnum;
}
