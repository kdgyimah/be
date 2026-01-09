<?php

namespace App\Entity\Construction;

use App\Entity\Interface\TimestampableEntityInterface;
use App\Entity\Trait\PrimaryKeyTrait;
use App\Entity\Trait\TimestampableEntityTrait;
use App\Listener\TimestampEntityListener;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity]
#[ORM\Table(name: 'construction_client')]
#[UniqueEntity(fields: ['name'], message: 'Client with name {{ value }} already exists!')]
#[ORM\EntityListeners([TimestampEntityListener::class])]
class Client implements TimestampableEntityInterface
{
    use PrimaryKeyTrait;
    use TimestampableEntityTrait;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    public private(set) string $name;

    #[ORM\ManyToOne(targetEntity: Company::class)]
    #[JoinColumn(nullable: false)]
    public private(set) Company $company;

    public function __construct(Company $company, string $name)
    {
        $this->company = $company;
        $this->name = $name;
    }
}
