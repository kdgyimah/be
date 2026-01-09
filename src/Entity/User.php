<?php

namespace App\Entity;

use App\Entity\Interface\TimestampableEntityInterface;
use App\Entity\Trait\TimestampableEntityTrait;
use App\Listener\TimestampEntityListener;
use App\Repository\UserRepository;
use App\Validator\IsPhoneNumber;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\EntityListeners;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['email'])]
#[EntityListeners([TimestampEntityListener::class])]
class User implements UserInterface, PasswordAuthenticatedUserInterface, TimestampableEntityInterface
{
    use TimestampableEntityTrait;

    public const string ROLE_ADMIN = 'ROLE_ADMIN';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Column(type: UuidType::NAME, unique: true)]
    public private(set) ?Uuid $id = null;

    #[Column(type: Types::STRING, length: 255)]
    public private(set) string $firstname;

    #[Column(type: Types::STRING, length: 255)]
    public private(set) string $lastname;

    #[Column(type: Types::STRING, length: 180, unique: true)]
    public private(set) string $email;

    #[Column(type: Types::STRING, length: 255)]
    public private(set) string $password;

    #[IsPhoneNumber]
    #[Column(type: Types::STRING, length: 15)]
    public private(set) string $phoneNumber;

    #[Column(type: Types::SIMPLE_ARRAY, nullable: true)]
    private array $roles = [];

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

    #[\Deprecated]
    public function eraseCredentials(): void
    {
    }

    public function setPhoneNumber(string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }
}
