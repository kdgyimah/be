<?php

namespace App\Entity\School;

use App\Entity\Interface\TimestampableEntityInterface;
use App\Entity\Trait\PrimaryKeyTrait;
use App\Entity\Trait\TimestampableEntityTrait;
use App\Listener\TimestampEntityListener;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity]
#[UniqueEntity(fields: ['name', 'school'])]
#[ORM\UniqueConstraint(fields: ['name', 'school'])]
#[ORM\Table(name: 'school_level')]
#[ORM\EntityListeners([TimestampEntityListener::class])]
class Level implements TimestampableEntityInterface
{
    use PrimaryKeyTrait;
    use TimestampableEntityTrait;

    #[ORM\Column(type: Types::STRING, length: 255)]
    public private(set) string $name;

    #[ORM\ManyToOne(targetEntity: School::class)]
    #[ORM\JoinColumn(nullable: false)]
    public private(set) School $school;

    #[ORM\Column(type: Types::SMALLINT)]
    public private(set) int $position;

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
