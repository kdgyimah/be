<?php

namespace App\Entity\School;

use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\DatePointType;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Uid\Uuid;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[ORM\Entity]
#[Vich\Uploadable]
class School
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private(set) ?Uuid $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private(set) ?string $name = null;

    #[Vich\UploadableField(mapping: 'school_logo', fileNameProperty: 'logoFilename')]
    private(set) ?File $logo = null;

    /** @noinspection PhpUnusedPrivateFieldInspection */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $logoFilename = null;

    #[ORM\Column(type: DatePointType::NAME)]
    private(set) DatePoint $createdAt;

    #[ORM\Column(type: DatePointType::NAME, nullable: true)]
    private(set) ?DatePoint $modifiedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private(set) User $director;

    #[ORM\ManyToMany(targetEntity: User::class)]
    private(set) Collection $members;

    public function __construct()
    {
        $this->createdAt = new DatePoint();
        $this->members = new ArrayCollection();
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setLogo(?File $logo): self
    {
        $this->logo = $logo;
        $this->modifiedAt = new DatePoint();

        return $this;
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->modifiedAt = new DatePoint();
    }
}
