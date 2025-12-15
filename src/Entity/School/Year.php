<?php

namespace App\Entity\School;

use App\Repository\School\YearRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\DatePointType;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: YearRepository::class)]
#[UniqueEntity(fields: ['year'])]
#[ORM\Table('school_year')]
#[ORM\HasLifecycleCallbacks]
class Year
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private(set) ?Uuid $id = null;

    #[ORM\Column(type: Types::STRING, unique: true, updatable: false)]
    private(set) string $name;

    #[ORM\ManyToOne(targetEntity: School::class, inversedBy: 'years')]
    #[ORM\JoinColumn(nullable: false)]
    private(set) School $school;

    #[ORM\Column(type: DatePointType::NAME)]
    private(set) DatePoint $start;

    #[ORM\Column(type: DatePointType::NAME)]
    private(set) DatePoint $finish;

    #[ORM\Column(type: Types::BOOLEAN)]
    private(set) bool $active = false;

    #[ORM\Column(type: DatePointType::NAME)]
    private(set) DatePoint $createdAt;

    public function __construct(School $school, ?int $year = null)
    {
        $this->school = $school;

        if ($year === null) {
            $year = new DatePoint()->format('Y');
        }

        $this->name = sprintf('%s - %d', $year, (int)$year + 1);
        $this->createdAt = new DatePoint();
        $this->start = new DatePoint("1st September $year");
        $this->finish = new DatePoint("30 June $year +1 year");
    }

    public function setStart(DatePoint $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function setFinish(DatePoint $finish): self
    {
        $this->finish = $finish;

        return $this;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }
}
