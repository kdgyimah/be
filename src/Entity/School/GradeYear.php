<?php

namespace App\Entity\School;

use App\Entity\Interface\TimestampableEntityInterface;
use App\Entity\Trait\PrimaryKeyTrait;
use App\Entity\Trait\TimestampableEntityTrait;
use App\Listener\TimestampEntityListener;
use App\Repository\School\GradeYearRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GradeYearRepository::class)]
#[ORM\Table(name: 'school_grade_year')]
#[ORM\EntityListeners([TimestampEntityListener::class])]
class GradeYear implements TimestampableEntityInterface
{
    use PrimaryKeyTrait;
    use TimestampableEntityTrait;

    #[ORM\ManyToOne(targetEntity: LevelYear::class)]
    #[ORM\JoinColumn(nullable: false)]
    public private(set) LevelYear $levelYear;

    #[ORM\ManyToOne(targetEntity: Year::class)]
    #[ORM\JoinColumn(nullable: false)]
    public private(set) Year $year;

    #[ORM\ManyToOne(targetEntity: Grade::class)]
    #[ORM\JoinColumn(nullable: false)]
    public private(set) Grade $grade;

    #[ORM\ManyToOne(targetEntity: Student::class)]
    #[ORM\JoinColumn(nullable: false)]
    public private(set) Student $student;

    /** @var Collection<Payment> */
    #[ORM\OneToMany(targetEntity: Payment::class, mappedBy: 'gradeYear')]
    public private(set) Collection $payments;

    public function __construct(LevelYear $levelYear, Grade $grade, Student $student)
    {
        $this->levelYear = $levelYear;
        $this->year = $this->levelYear->year;
        $this->grade = $grade;
        $this->student = $student;
        $this->payments = new ArrayCollection();
    }
}
