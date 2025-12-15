<?php

namespace App\Entity\School;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[UniqueEntity(fields: ['name', 'school'])]
#[ORM\UniqueConstraint(fields: ['name', 'school'])]
#[ORM\Table(name: 'school_level')]
class Level
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private(set) ?Uuid $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private(set) string $name;

    #[ORM\ManyToOne(targetEntity: School::class)]
    #[ORM\JoinColumn(nullable: false)]
    private(set) School $school;

    #[ORM\Column(type: Types::SMALLINT)]
    private(set) int $position;

    public function __construct(School $school)
    {
        $this->school = $school;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }
}
