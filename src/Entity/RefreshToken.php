<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\DatePointType;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
class RefreshToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\Column(type: UuidType::NAME)]
    private(set) Uuid $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private(set) User $user;

    #[ORM\Column(type: DatePointType::NAME)]
    private DatePoint $exp;

    public function __construct(int $ttl, User $user)
    {
        $this->user = $user;
        $this->exp = new DatePoint($ttl < 0 ? "$ttl seconds" : "+$ttl seconds");
    }

    public function isValid(): bool
    {
        return new DatePoint() < $this->exp;
    }
}
