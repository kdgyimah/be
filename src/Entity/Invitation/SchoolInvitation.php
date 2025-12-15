<?php

namespace App\Entity\Invitation;

use App\Entity\School\School;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity]
#[ORM\UniqueConstraint(fields: ['school', 'email'])]
#[UniqueEntity(fields: ['school', 'email'])]
class SchoolInvitation extends RegisterInvitation
{
    #[ORM\ManyToOne(targetEntity: School::class)]
    #[ORM\JoinColumn(nullable: false)]
    private School $school;

    public function __construct(School $school, string $email)
    {
        parent::__construct($email);
        $this->school = $school;
    }

    function getModule(): School
    {
        return $this->school;
    }
}
