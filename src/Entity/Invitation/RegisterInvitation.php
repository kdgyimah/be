<?php

namespace App\Entity\Invitation;

use App\Entity\Trait\PrimaryKeyTrait;
use App\Listener\TimestampEntityListener;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'disc', type: 'string')]
#[ORM\DiscriminatorMap([
    'school' => SchoolInvitation::class,
])]
#[ORM\EntityListeners([TimestampEntityListener::class])]
abstract class RegisterInvitation
{
    use PrimaryKeyTrait;

    #[Assert\Email]
    #[ORM\Column(type: Types::STRING)]
    public private(set) string $email;

    public function __construct(string $email)
    {
        $this->email = $email;
    }

    abstract public function getModule(): mixed;
}
