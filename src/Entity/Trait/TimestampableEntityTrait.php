<?php

namespace App\Entity\Trait;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\DatePointType;
use Symfony\Component\Clock\DatePoint;

trait TimestampableEntityTrait
{
    #[ORM\Column(type: DatePointType::NAME)]
    private DatePoint $createdAt {
        get => $this->createdAt;
    }

    #[ORM\Column(type: DatePointType::NAME, nullable: true)]
    private ?DatePoint $updatedAt = null {
        get => $this->updatedAt;
    }
}
