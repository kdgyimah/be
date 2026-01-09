<?php

namespace App\Entity\School;

use App\Entity\Interface\TimestampableEntityInterface;
use App\Entity\Trait\PrimaryKeyTrait;
use App\Entity\Trait\TimestampableEntityTrait;
use App\Listener\TimestampEntityListener;
use App\Repository\School\StudentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: StudentRepository::class)]
#[ORM\Table('school_student')]
#[ORM\EntityListeners([TimestampEntityListener::class])]
class Student implements TimestampableEntityInterface
{
    use PrimaryKeyTrait;
    use TimestampableEntityTrait;

    #[ORM\Column(type: Types::STRING, length: 255)]
    public private(set) string $firstname;

    #[ORM\Column(type: Types::STRING, length: 255)]
    public private(set) string $lastname;

    #[ORM\ManyToOne(targetEntity: School::class)]
    #[ORM\JoinColumn(nullable: false)]
    public private(set) School $school;

    #[ORM\Column(type: Types::BOOLEAN)]
    public private(set) bool $male;

    /** @var Collection<StudentTutor> */
    #[ORM\OneToMany(targetEntity: StudentTutor::class, mappedBy: 'student')]
    public private(set) Collection $tutors;

    public function __construct(School $school)
    {
        $this->school = $school;
        $this->tutors = new ArrayCollection();
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function setMale(bool $male): self
    {
        $this->male = $male;

        return $this;
    }

    public function validate(?ExecutionContextInterface $context): void
    {
        $count = $this->tutors->count();

        if ($count < 1) {
            $context?->buildViolation('Un enfant doit avoir au moins 1 parent')
                ->atPath('parents')
                ->addViolation();
            if (null === $context) {
                throw new \Exception('Un enfant doit avoir au moins 1 parent');
            }
        }

        if ($count > 2) {
            $context?->buildViolation('Un enfant doit avoir au plus 2 parents')
                ->atPath('parents')
                ->addViolation();
            if (null === $context) {
                throw new \Exception('Un enfant doit avoir au plus 2 parents');
            }
        }
    }
}
