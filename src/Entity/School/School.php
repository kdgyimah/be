<?php

namespace App\Entity\School;

use App\Entity\Interface\TimestampableEntityInterface;
use App\Entity\Trait\TimestampableEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[ORM\Entity]
#[Vich\Uploadable]
class School implements TimestampableEntityInterface
{
    use TimestampableEntityTrait;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private(set) Uuid $id;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private(set) string $name;

    #[Vich\UploadableField(mapping: 'school_logo', fileNameProperty: 'logoFilename')]
    private(set) ?File $logo = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private(set) ?string $logoFilename = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private(set) string $address;

    #[Assert\Email]
    #[ORM\Column(type: Types::STRING, length: 255)]
    private(set) string $email;

    #[ORM\Column(type: Types::STRING, length: 40)]
    private(set) string $phone;

    /** @var Collection<Year> */
    #[Assert\Count(min: 1)]
    #[ORM\OneToMany(targetEntity: Year::class, mappedBy: 'school')]
    private(set) Collection $years;

    #[Assert\Timezone]
    #[ORM\Column(type: Types::STRING, length: 50)]
    private(set) string $timezone;

    public function __construct()
    {
        $this->id = Uuid::v7();
        $this->createdAt = new DatePoint();
        $this->years = new ArrayCollection();
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setLogo(?File $logo): self
    {
        $this->logo = $logo;
        $this->updatedAt = new DatePoint();

        return $this;
    }

    public function setLogoFilename(?string $logoFilename): self
    {
        $this->logoFilename = $logoFilename;

        return $this;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function setTimezone(string $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }
}
