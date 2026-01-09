<?php

namespace App\Entity\Construction;

use App\Entity\Interface\TimestampableEntityInterface;
use App\Entity\Trait\PrimaryKeyTrait;
use App\Entity\Trait\TimestampableEntityTrait;
use App\Enum\ConstructionProjectState;
use App\Listener\TimestampEntityListener;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\DatePointType;
use Symfony\Component\Clock\DatePoint;

#[ORM\Entity]
#[ORM\Table(name: 'construction_project')]
#[ORM\EntityListeners([TimestampEntityListener::class])]
class Project implements TimestampableEntityInterface
{
    use PrimaryKeyTrait;
    use TimestampableEntityTrait;

    #[ORM\Column(type: Types::STRING, length: 255)]
    public private(set) string $name;

    #[ORM\ManyToOne(targetEntity: Client::class)]
    #[ORM\JoinColumn(nullable: false)]
    public private(set) Client $client;

    #[ORM\Column(type: DatePointType::NAME)]
    public private(set) DatePoint $deliveryDate;

    #[ORM\ManyToOne(targetEntity: Company::class)]
    #[ORM\JoinColumn(nullable: false)]
    public private(set) Company $company;

    #[ORM\ManyToOne(targetEntity: Engineer::class)]
    public private(set) Engineer $engineer;

    #[ORM\Column(type: Types::STRING, enumType: ConstructionProjectState::class)]
    public private(set) ConstructionProjectState $state = ConstructionProjectState::STARTING;

    public function __construct(Company $company, Client $client, Engineer $engineer)
    {
        $this->company = $company;
        $this->engineer = $engineer;
        $this->client = $client;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setEngineer(Engineer $engineer): self
    {
        $this->engineer = $engineer;

        return $this;
    }

    public function setDeliveryDate(DatePoint $deliveryDate): self
    {
        $this->deliveryDate = $deliveryDate;

        return $this;
    }

    public function setState(ConstructionProjectState $state): self
    {
        $this->state = $state;

        return $this;
    }
}
