<?php

namespace App\Entity\Construction;

use App\Entity\Interface\TimestampableEntityInterface;
use App\Entity\Trait\PrimaryKeyTrait;
use App\Entity\Trait\TimestampableEntityTrait;
use App\Listener\TimestampEntityListener;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Table;

#[ORM\Entity]
#[Table(name: 'construction_company')]
#[ORM\EntityListeners([TimestampEntityListener::class])]
class Company implements TimestampableEntityInterface
{
    use PrimaryKeyTrait;
    use TimestampableEntityTrait;

    #[ORM\Column(type: Types::STRING, length: 255)]
    public private(set) string $name;

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
