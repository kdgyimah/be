<?php

namespace App\DataFixtures\School;

use App\Entity\School\Grade;
use App\Entity\School\GradeYear;
use App\Entity\School\LevelYear;
use App\Entity\School\School;
use App\Entity\School\Student;
use App\Entity\School\StudentTutor;
use App\Entity\School\Tutor;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;

class StudentFixtures extends Fixture implements DependentFixtureInterface
{
    private readonly Faker\Generator $faker;

    public function __construct()
    {
        $this->faker = Faker\Factory::create();
    }

    public function getDependencies(): array
    {
        return [
            LevelFixtures::class,
            SchoolFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $pm = $this->getReference(SchoolFixtures::PM, School::class);

        foreach (LevelFixtures::LEVELS as $levelName) {
            $levelYear = $this->getReference("levelYear-$levelName", LevelYear::class);
            $grade = $this->getReference("grade-$levelName", Grade::class);

            for ($i = 0; $i < 10; ++$i) {
                $student = new Student($pm)
                    ->setFirstname($this->faker->firstName())
                    ->setLastname($this->faker->lastName())
                    ->setMale($this->faker->boolean());

                $gradeYear = new GradeYear($levelYear, $grade, $student);

                foreach (range(1, $this->faker->numberBetween(1, 2)) as $ignored) {
                    $tutor = new Tutor($pm)
                        ->setFirstname($this->faker->firstName())
                        ->setLastname($this->faker->lastName())
                        ->setPhoneNumber($this->faker->e164PhoneNumber())
                    ;

                    $studentTutor = new StudentTutor($tutor, $student)
                        ->setCanContact($this->faker->boolean());

                    $manager->persist($studentTutor);
                    $manager->persist($tutor);
                }

                $manager->persist($gradeYear);
                $manager->persist($student);
            }
        }

        $manager->flush();
    }
}
