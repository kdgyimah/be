<?php

namespace App\Entity\School;

use App\Entity\Interface\TimestampableEntityInterface;
use App\Entity\Trait\TimestampableEntityTrait;
use App\Listener\TimestampEntityListener;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table('school_payment')]
#[ORM\EntityListeners([TimestampEntityListener::class])]
class Payment implements TimestampableEntityInterface
{
    use TimestampableEntityTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    public private(set) ?Uuid $id = null;

    #[ORM\Column(type: Types::FLOAT)]
    public private(set) float $amount;

    #[ORM\Column(type: Types::STRING)]
    public private(set) string $type;

    #[ORM\ManyToOne(targetEntity: GradeYear::class, inversedBy: 'payments')]
    public private(set) GradeYear $gradeYear;

    public function __construct(GradeYear $gradeYear, float $amount, string $type)
    {
        $this->gradeYear = $gradeYear;
        $this->amount = $amount;
        $this->type = $type;
    }
}
