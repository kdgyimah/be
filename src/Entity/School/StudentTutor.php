<?php

namespace App\Entity\School;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;

#[ORM\Entity]
#[ORM\Table('school_student_tutor')]
class StudentTutor
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Student::class, inversedBy: 'tutors')]
    #[JoinColumn(nullable: false)]
    private(set) Student $student;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Tutor::class, inversedBy: 'students')]
    #[JoinColumn(nullable: false)]
    private(set) Tutor $tutor;

    #[ORM\Column(type: Types::BOOLEAN)]
    private(set) bool $canContact = false;

    public function __construct()
    {
    }
}
