<?php

namespace App\Entity\School;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table('school_tutor')]
class Tutor
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private(set) ?Uuid $id = null;

    /** @var Collection<StudentTutor> */
    #[ORM\OneToMany(targetEntity: StudentTutor::class, mappedBy: 'student')]
    private(set) Collection $students;

    public function __construct()
    {
        $this->students = new ArrayCollection();
    }
}
