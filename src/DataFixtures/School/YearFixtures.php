<?php

namespace App\DataFixtures\School;

use App\Entity\School\School;
use App\Entity\School\Year;
use App\Listener\TimestampEntityListener;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Uuid;

class YearFixtures extends Fixture implements DependentFixtureInterface
{
    public const string YEAR = 'year';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TimestampEntityListener $timestampEntityListener
    ) {
    }

    public function getDependencies(): array
    {
        return [
            SchoolFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $year = new Year($this->getReference(SchoolFixtures::PM, School::class))
            ->setActive(true);
        $this->setReference(YearFixtures::YEAR, $year);

        $rp = new \ReflectionProperty($year, 'id');
        $rp->setValue($year, new Uuid('019b31bd-3f68-7e5d-97c2-5909a066d236'));

        $this->entityManager->getUnitOfWork()->scheduleForInsert($year);
        $this->timestampEntityListener->prePersist($year);
        $manager->flush();
    }
}
