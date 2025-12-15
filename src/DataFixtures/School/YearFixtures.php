<?php

namespace App\DataFixtures\School;

use App\Entity\School\School;
use App\Entity\School\Year;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class YearFixtures extends Fixture implements DependentFixtureInterface
{
    public const string YEAR = 'year';

    /**
     * @inheritDoc
     */
    public function getDependencies(): array
    {
        return [
          SchoolFixtures::class,
        ];
    }

    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager): void
    {
        $year = new Year($this->getReference(SchoolFixtures::PM, School::class))
            ->setActive(true);
        $this->setReference(YearFixtures::YEAR, $year);
        $manager->persist($year);
        $manager->flush();
    }
}
