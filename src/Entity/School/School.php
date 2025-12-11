<?php

namespace App\Entity\School;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
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
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private(set) Uuid $id;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private(set) string $name;

    #[Vich\UploadableField(mapping: 'school_logo', fileNameProperty: 'logoFilename')]
    private(set) ?File $logo = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private(set) ?string $logoFilename = null;

    #[ORM\Column(type: DatePointType::NAME)]
    private(set) DatePoint $createdAt;

    #[ORM\Column(type: DatePointType::NAME, nullable: true)]
    private(set) ?DatePoint $modifiedAt = null;

    public function __construct()
    {
        $this->id = Uuid::v7();
        $this->createdAt = new DatePoint();
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

    public function setLogoFilename(?string $logoFilename): self
    {
        $this->logoFilename = $logoFilename;

        return $this;
    }
}
