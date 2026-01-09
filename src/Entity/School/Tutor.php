<?php

namespace App\Entity\School;

use App\Entity\Interface\TimestampableEntityInterface;
use App\Entity\Trait\PrimaryKeyTrait;
use App\Entity\Trait\TimestampableEntityTrait;
use App\Listener\TimestampEntityListener;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table('school_tutor')]
#[ORM\EntityListeners([TimestampEntityListener::class])]
class Tutor implements TimestampableEntityInterface
{
    use TimestampableEntityTrait;
    use PrimaryKeyTrait;

    public private(set) School $school;

    /** @var Collection<StudentTutor> */
    #[ORM\OneToMany(targetEntity: StudentTutor::class, mappedBy: 'tutor')]
    public private(set) Collection $students;

    #[ORM\Column(type: Types::STRING, length: 255)]
    public private(set) string $firstname;

    #[ORM\Column(type: Types::STRING, length: 255)]
    public private(set) string $lastname;

    #[ORM\Column(type: Types::STRING, length: 50)]
    public private(set) string $phoneNumber;

    public function __construct(School $school)
    {
        $this->school = $school;
        $this->students = new ArrayCollection();
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

    public function setPhoneNumber(string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }
}
