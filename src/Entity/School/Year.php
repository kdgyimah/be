<?php

namespace App\Entity\School;

use App\Entity\Interface\TimestampableEntityInterface;
use App\Entity\Trait\PrimaryKeyTrait;
use App\Entity\Trait\TimestampableEntityTrait;
use App\Listener\TimestampEntityListener;
use App\Repository\School\YearRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\DatePointType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Clock\DatePoint;

#[ORM\Entity(repositoryClass: YearRepository::class)]
#[UniqueEntity(fields: ['year'])]
#[ORM\Table('school_year')]
#[ORM\EntityListeners([TimestampEntityListener::class])]
class Year implements TimestampableEntityInterface
{
    use PrimaryKeyTrait;
    use TimestampableEntityTrait;

    #[ORM\Column(type: Types::STRING, unique: true, updatable: false)]
    public private(set) string $name;

    #[ORM\ManyToOne(targetEntity: School::class, inversedBy: 'years')]
    #[ORM\JoinColumn(nullable: false)]
    public private(set) School $school;

    #[ORM\Column(type: DatePointType::NAME)]
    public private(set) DatePoint $start;

    #[ORM\Column(type: DatePointType::NAME)]
    public private(set) DatePoint $finish;

    #[ORM\Column(type: Types::BOOLEAN)]
    public private(set) bool $active = false;

    public function __construct(School $school, ?int $year = null)
    {
        $this->school = $school;

        if (null === $year) {
            $year = new DatePoint()->format('Y');
        }

        $this->name = sprintf('%s - %d', $year, (int) $year + 1);
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
