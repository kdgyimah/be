<?php

namespace App\Entity\School;

use App\Entity\Interface\TimestampableEntityInterface;
use App\Entity\Trait\TimestampableEntityTrait;
use App\Listener\TimestampEntityListener;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table('school_student_tutor')]
#[ORM\EntityListeners([TimestampEntityListener::class])]
class StudentTutor implements TimestampableEntityInterface
{
    use TimestampableEntityTrait;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Student::class, inversedBy: 'tutors')]
    public private(set) Student $student;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Tutor::class, inversedBy: 'students')]
    public private(set) Tutor $tutor;

    #[ORM\Column(type: Types::BOOLEAN)]
    public private(set) bool $canContact = false;

    public function __construct(Tutor $tutor, Student $student)
    {
        $this->student = $student;
        $this->tutor = $tutor;
    }

    public function setCanContact(bool $canContact): self
    {
        $this->canContact = $canContact;

        return $this;
    }
}
