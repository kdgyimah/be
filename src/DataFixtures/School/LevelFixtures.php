<?php

namespace App\DataFixtures\School;

use App\Entity\School\Level;
use App\Entity\School\LevelYear;
use App\Entity\School\School;
use App\Entity\School\Year;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LevelFixtures extends Fixture implements DependentFixtureInterface
{

    /**
     * @inheritDoc
     */
    public function getDependencies(): array
    {
        return [
            SchoolFixtures::class,
            YearFixtures::class,
        ];
    }

    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager): void
    {
        $school = $this->getReference(SchoolFixtures::PM, School::class);
        $year = $this->getReference(YearFixtures::YEAR, Year::class);

        $levels = ['CP', 'CE1', 'CE2', 'CM1', 'CM2'];

        foreach ($levels as $position => $levelName) {
            $level = new Level($school)
                ->setName($levelName)
                ->setPosition($position);

            $levelYear = new LevelYear($level, $year)
                ->setInscriptionFees(30000)
                ->setTuitionFees(30000);

            $manager->persist($levelYear);
            $manager->persist($level);
        }

        $manager->flush();
    }
}
