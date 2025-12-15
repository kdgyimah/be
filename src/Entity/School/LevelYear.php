<?php

namespace App\Entity\School;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;

#[ORM\Entity]
#[ORM\Table(name: 'school_level_year')]
class LevelYear
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Level::class)]
    #[JoinColumn(nullable: false)]
    private(set) Level $level;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Year::class)]
    #[ORM\JoinColumn(nullable: false)]
    private(set) Year $year;

    #[ORM\Column(type: Types::FLOAT)]
    private(set) float $inscriptionFees;

    #[ORM\Column(type: Types::FLOAT)]
    private(set) float $tuitionFees;

    /** @var array<string, float> */
    #[ORM\Column(type: Types::JSON)]
    private(set) array $additionalFees = [];

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
     * @return LevelYear
     */
    public function setAdditionalFees(array $additionalFees): self
    {
        $this->additionalFees = $additionalFees;

        return $this;
    }
}
