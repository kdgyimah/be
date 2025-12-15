<?php

namespace App\Entity\School;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table('school_payment')]
class Payment
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private(set) ?Uuid $id = null;

    #[ORM\ManyToOne(targetEntity: StudentYear::class, inversedBy: 'payments')]
    #[ORM\JoinColumn(nullable: false)]
    private(set) StudentYear $studentYear;

    #[ORM\Column(type: Types::FLOAT)]
    private(set) float $amount;

    #[ORM\Column(type: Types::STRING)]
    private(set) string $fees;

    public function __construct(StudentYear $studentYear, float $amount, string $fees)
    {
        $this->studentYear = $studentYear;
        $this->amount = $amount;
        $this->fees = $fees;
    }
}
