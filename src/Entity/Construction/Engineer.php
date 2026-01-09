<?php

namespace App\Entity\Construction;

use App\Entity\Interface\TimestampableEntityInterface;
use App\Entity\Trait\PrimaryKeyTrait;
use App\Entity\Trait\TimestampableEntityTrait;
use App\Listener\TimestampEntityListener;
use App\Repository\Construction\EngineerRepository;
use App\Validator\IsPhoneNumber;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[ORM\Entity(repositoryClass: EngineerRepository::class)]
#[Vich\Uploadable]
#[ORM\Table(name: 'construction_engineer')]
#[ORM\EntityListeners([TimestampEntityListener::class])]
class Engineer implements TimestampableEntityInterface
{
    use PrimaryKeyTrait;
    use TimestampableEntityTrait;

    #[ORM\Column(type: Types::STRING, length: 255)]
    public private(set) string $firstname;

    #[ORM\Column(type: Types::STRING, length: 255)]
    public private(set) string $lastname;

    #[ORM\ManyToOne(targetEntity: Company::class)]
    #[ORM\JoinColumn(nullable: false)]
    public private(set) Company $company;

    #[IsPhoneNumber]
    #[ORM\Column(type: Types::STRING, length: 255)]
    public private(set) string $phoneNumber;

    #[Vich\UploadableField(mapping: 'engineers_pp', fileNameProperty: 'profilePictureFilename')]
    public private(set) ?File $profilePicture = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    public private(set) ?string $profilePictureFilename = null;

    public function __construct(Company $company)
    {
        $this->company = $company;
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

    public function setPhoneNumber(string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function setProfilePicture(?File $profilePicture): self
    {
        $this->profilePicture = $profilePicture;

        return $this;
    }

    public function setProfilePictureFilename(?string $profilePictureFilename): self
    {
        $this->profilePictureFilename = $profilePictureFilename;

        return $this;
    }
}
