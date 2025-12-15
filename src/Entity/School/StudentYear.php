<?php

namespace App\Entity\School;

use App\Repository\School\StudentYearRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: StudentYearRepository::class)]
#[UniqueEntity(fields: ['year', 'student'])]
#[ORM\Table('school_student_year')]
class StudentYear
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private(set) ?Uuid $id = null;

    #[ORM\ManyToOne(targetEntity: Year::class)]
    #[ORM\JoinColumn(nullable: false)]
    private(set) Year $year;

    #[ORM\ManyToOne(targetEntity: Level::class)]
    #[ORM\JoinColumn(nullable: false)]
    private(set) Grade $grade;

    #[ORM\ManyToOne(targetEntity: Student::class)]
    #[ORM\JoinColumn(nullable: false)]
    private(set) Student $student;

    /** @var Collection<Payment> */
    #[ORM\OneToMany(targetEntity: Payment::class, mappedBy: 'studentYear')]
    private(set) Collection $payments;

    public function __construct(Year $year, Grade $grade, Student $student)
    {
        $this->year = $year;
        $this->grade = $grade;
        $this->student = $student;
        $this->payments = new ArrayCollection();
    }
}
