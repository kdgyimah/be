<?php

namespace App\Entity\Construction;

use App\Entity\Interface\TimestampableEntityInterface;
use App\Entity\Trait\PrimaryKeyTrait;
use App\Entity\Trait\TimestampableEntityTrait;
use App\Enum\WorkerProfession;
use App\Listener\TimestampEntityListener;
use App\Validator\IsPhoneNumber;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[ORM\Entity]
#[Vich\Uploadable]
#[ORM\Table(name: 'construction_worker')]
#[ORM\EntityListeners([TimestampEntityListener::class])]
class Worker implements TimestampableEntityInterface
{
    use PrimaryKeyTrait;
    use TimestampableEntityTrait;

    #[ORM\Column(type: Types::STRING, length: 255)]
    public private(set) string $firstname;

    #[ORM\Column(type: Types::STRING, length: 255)]
    public private(set) string $lastname;

    #[IsPhoneNumber]
    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    public private(set) string $phoneNumber;

    #[ORM\ManyToOne(targetEntity: Company::class)]
    public private(set) Company $company;

    #[Vich\UploadableField(mapping: 'workers_pp', fileNameProperty: 'profilePictureFilename')]
    public private(set) ?File $profilePicture = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    public private(set) ?string $profilePictureFilename = null;

    #[ORM\Column(type: Types::FLOAT)]
    public private(set) float $dailySalary;

    #[ORM\Column(type: Types::STRING, enumType: WorkerProfession::class)]
    public private(set) WorkerProfession $profession;

    public function __construct(Company $company)
    {
        $this->company = $company;
    }

    public function setFirstname(string $firstname): Worker
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function setLastname(string $lastname): Worker
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function setPhoneNumber(string $phoneNumber): Worker
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

    public function setDailySalary(float $dailySalary): self
    {
        $this->dailySalary = $dailySalary;

        return $this;
    }

    public function setProfession(WorkerProfession $profession): self
    {
        $this->profession = $profession;

        return $this;
    }
}
