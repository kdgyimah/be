<?php

namespace App\DataFixtures\School;

use App\Entity\School\Grade;
use App\Entity\School\Level;
use App\Entity\School\LevelYear;
use App\Entity\School\School;
use App\Entity\School\Year;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;

class LevelFixtures extends Fixture implements DependentFixtureInterface
{
    /** @var array<string> */
    public const array LEVELS = ['CP', 'CE1', 'CE2', 'CM1', 'CM2'];

    private Faker\Generator $faker;

    public function __construct()
    {
        $this->faker = Faker\Factory::create();
    }

    public function getDependencies(): array
    {
        return [
            SchoolFixtures::class,
            YearFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $school = $this->getReference(SchoolFixtures::PM, School::class);
        $year = $this->getReference(YearFixtures::YEAR, Year::class);

        foreach (LevelFixtures::LEVELS as $position => $levelName) {
            $level = new Level($school)
                ->setName($levelName)
                ->setPosition($position);

            $grade = new Grade($level)
                ->setName($levelName);

            $levelYear = new LevelYear($level, $year)
                ->setInscriptionFees($this->faker->numberBetween(25_000, 50_000))
                ->setTuitionFees($this->faker->numberBetween(250_000, 500_000));

            $this->setReference("levelYear-$levelName", $levelYear);
            $this->setReference("grade-$levelName", $grade);

            $manager->persist($grade);
            $manager->persist($levelYear);
            $manager->persist($level);
        }

        $manager->flush();
    }
}
