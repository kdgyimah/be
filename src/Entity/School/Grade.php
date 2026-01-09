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
#[ORM\Table(name: 'school_grade')]
#[ORM\UniqueConstraint(fields: ['name', 'level'])]
#[UniqueEntity(fields: ['name', 'level'])]
#[ORM\EntityListeners([TimestampEntityListener::class])]
class Grade implements TimestampableEntityInterface
{
    use PrimaryKeyTrait;
    use TimestampableEntityTrait;

    #[ORM\Column(type: Types::STRING, length: 255)]
    public private(set) string $name;

    #[ORM\ManyToOne(targetEntity: Level::class)]
    #[ORM\JoinColumn(nullable: false)]
    public private(set) Level $level;

    public function __construct(Level $level)
    {
        $this->level = $level;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
