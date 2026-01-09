<?php

namespace App\Entity\School;

use App\Entity\Interface\TimestampableEntityInterface;
use App\Entity\Trait\PrimaryKeyTrait;
use App\Entity\Trait\TimestampableEntityTrait;
use App\Listener\TimestampEntityListener;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'school_level_year')]
#[ORM\UniqueConstraint(fields: ['level', 'year'])]
#[ORM\EntityListeners([TimestampEntityListener::class])]
class LevelYear implements TimestampableEntityInterface
{
    use PrimaryKeyTrait;
    use TimestampableEntityTrait;

    #[ORM\ManyToOne(targetEntity: Level::class)]
    #[ORM\JoinColumn(nullable: false)]
    public private(set) Level $level;

    #[ORM\ManyToOne(targetEntity: Year::class)]
    #[ORM\JoinColumn(nullable: false)]
    public private(set) Year $year;

    #[ORM\Column(type: Types::FLOAT)]
    public private(set) float $inscriptionFees;

    #[ORM\Column(type: Types::FLOAT)]
    public private(set) float $tuitionFees;

    /** @var array<string, float> */
    #[ORM\Column(type: Types::JSON)]
    public private(set) array $additionalFees = [];

    public function __construct(Level $level, Year $year)
    {
        $this->level = $level;
        $this->year = $year;
    }

    public function setInscriptionFees(float $inscriptionFees): self
    {
        $this->inscriptionFees = $inscriptionFees;

        return $this;
    }

    public function setTuitionFees(float $tuitionFees): self
    {
        $this->tuitionFees = $tuitionFees;

        return $this;
    }

    /**
     * @param array<string, float> $additionalFees
     */
    public function setAdditionalFees(array $additionalFees): self
    {
        $this->additionalFees = $additionalFees;

        return $this;
    }
}
