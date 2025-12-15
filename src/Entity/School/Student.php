<?php

namespace App\Entity\School;

use App\Repository\School\StudentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: StudentRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table('school_student')]
class Student
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private(set) ?Uuid $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private(set) string $firstname;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private(set) string $lastname;

    #[ORM\ManyToOne(targetEntity: School::class)]
    #[ORM\JoinColumn(nullable: false)]
    private(set) School $school;

    /** @var Collection<StudentTutor> */
    #[ORM\OneToMany(targetEntity: StudentTutor::class, mappedBy: 'student')]
    private(set) Collection $tutors;

    public function __construct()
    {
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

    /**
     * @throws Exception
     */
    #[Assert\Callback]
    #[ORM\PrePersist, ORM\PreUpdate]
    public function validate(?ExecutionContextInterface $context): void
    {
        $count = $this->tutors->count();

        if ($count < 1) {
            $context?->buildViolation('Un enfant doit avoir au moins 1 parent')
                ->atPath('parents')
                ->addViolation();
            if ($context === null) {
                throw new Exception('Un enfant doit avoir au moins 1 parent');
            }
        }

        if ($count > 2) {
            $context?->buildViolation('Un enfant doit avoir au plus 2 parents')
                ->atPath('parents')
                ->addViolation();
            if ($context === null) {
                throw new Exception('Un enfant doit avoir au plus 2 parents');
            }
        }
    }
}
