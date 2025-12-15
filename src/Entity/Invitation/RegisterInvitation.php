<?php

namespace App\Entity\Invitation;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'disc', type: 'string')]
#[ORM\DiscriminatorMap([
    'school' => SchoolInvitation::class,
])]
abstract class RegisterInvitation
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private(set) ?Uuid $id = null;

    #[Assert\Email]
    #[ORM\Column(type: Types::STRING)]
    private(set) string $email;

    public function __construct(string $email)
    {
        $this->email = $email;
    }

    abstract function getModule(): mixed;
}
