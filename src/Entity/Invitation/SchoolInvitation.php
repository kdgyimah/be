<?php

namespace App\Entity\Invitation;

use App\Entity\School\School;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class SchoolInvitation extends RegisterInvitation
{
    #[ORM\ManyToOne(targetEntity: School::class)]
    #[ORM\JoinColumn(nullable: false)]
    private School $module;

    public function __construct(School $school)
    {
        $this->module = $school;
    }

    function getModule(): School
    {
        return $this->module;
    }
}
