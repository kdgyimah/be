<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\DatePointType;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[UniqueEntity(fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const string ROLE_SCHOOL = 'ROLE_SCHOOL';
    public const string ROLE_ADMIN = 'ROLE_ADMIN';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: UuidType::NAME)]
    private(set) ?Uuid $id = null;

    #[ORM\Column(type: Types::STRING, length: 180, unique: true)]
    private(set) string $email;

    #[ORM\Column(type: Types::STRING, length: 50)]
    private(set) string $password;

    #[Assert\Choice(callback: 'getAllRoles', multiple: true)]
    #[ORM\Column(type: Types::SIMPLE_ARRAY)]
    private array $roles = [];

    #[ORM\Column(type: DatePointType::NAME)]
    private(set) DatePoint $createdAt;

    #[ORM\Column(type: DatePointType::NAME, nullable: true)]
    private(set) ?DatePoint $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new DatePoint();
    }

    /**
     * @inheritDoc
     */
    public function getRoles(): array
    {
        return array_unique([...$this->roles, 'ROLE_USER']);
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @return list<string>
     */
    public static function getAllRoles(): array
    {
        return [User::ROLE_SCHOOL, User::ROLE_ADMIN];
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = new DatePoint();
    }
}
