<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\DatePointType;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['email'])]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const string ROLE_SCHOOL = 'ROLE_SCHOOL';
    public const string ROLE_ADMIN = 'ROLE_ADMIN';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private(set) ?Uuid $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private(set) string $firstname;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private(set) string $lastname;

    #[ORM\Column(type: Types::STRING, length: 180, unique: true)]
    private(set) string $email;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private(set) string $password;

    #[Assert\Choice(choices: [User::ROLE_SCHOOL, User::ROLE_ADMIN], multiple: true)]
    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true)]
    private array $roles = [];

    #[ORM\Column(type: DatePointType::NAME)]
    private(set) DatePoint $createdAt;

    #[ORM\Column(type: DatePointType::NAME, nullable: true)]
    private(set) ?DatePoint $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new DatePoint();
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRoles(): array
    {
        return array_unique([...$this->roles, 'ROLE_USER']);
    }

    public function addRole(string $role): self
    {
        $this->roles = array_unique(array_merge($this->roles, [$role]));

        return $this;
    }

    public function removeRole(string $role): self
    {
        $this->roles = array_diff($this->roles, [$role]);

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = new DatePoint();
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
    }
}
