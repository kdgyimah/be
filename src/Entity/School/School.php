<?php

namespace App\Entity\School;

use App\Entity\Interface\TimestampableEntityInterface;
use App\Entity\Trait\PrimaryKeyTrait;
use App\Entity\Trait\TimestampableEntityTrait;
use App\Entity\User;
use App\Listener\TimestampEntityListener;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[ORM\Entity]
#[Vich\Uploadable]
#[ORM\EntityListeners([TimestampEntityListener::class])]
class School implements TimestampableEntityInterface
{
    use PrimaryKeyTrait;
    use TimestampableEntityTrait;

    #[ORM\Column(type: Types::STRING, length: 255)]
    public private(set) string $name;

    #[Vich\UploadableField(mapping: 'school_logo', fileNameProperty: 'logoFilename')]
    public private(set) ?File $logo = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    public private(set) ?string $logoFilename = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    public private(set) string $address;

    #[Assert\Email]
    #[ORM\Column(type: Types::STRING, length: 255)]
    public private(set) string $email;

    #[ORM\Column(type: Types::STRING, length: 40)]
    public private(set) string $phone;

    /** @var Collection<Year> */
    #[Assert\Count(min: 1)]
    #[ORM\OneToMany(targetEntity: Year::class, mappedBy: 'school')]
    public private(set) Collection $years;

    #[Assert\Timezone]
    #[ORM\Column(type: Types::STRING, length: 50)]
    public private(set) string $timezone;

    #[Assert\Currency]
    #[ORM\Column(type: Types::STRING, length: 3)]
    public private(set) string $currency;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    public private(set) User $director;

    public function __construct(User $director)
    {
        $this->director = $director;
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

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function setDirector(User $director): self
    {
        $this->director = $director;

        return $this;
    }
}
