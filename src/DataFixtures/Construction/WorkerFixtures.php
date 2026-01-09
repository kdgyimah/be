<?php

namespace App\DataFixtures\Construction;

use App\Entity\Construction\Company;
use App\Entity\Construction\Worker;
use App\Enum\WorkerProfession;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class WorkerFixtures extends Fixture implements DependentFixtureInterface
{
    private readonly Faker\Generator $faker;

    public function __construct(private readonly ValidatorInterface $validator)
    {
        $this->faker = Faker\Factory::create('fr_FR');
    }

    public function getDependencies(): array
    {
        return [
            CompanyFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $company = $this->getReference(CompanyFixtures::COMPANY, Company::class);

        for ($i = 0; $i < 25; ++$i) {
            $worker = new Worker($company)
                ->setFirstname($this->faker->firstName())
                ->setLastname($this->faker->lastName())
                ->setPhoneNumber($this->faker->e164PhoneNumber())
                ->setDailySalary($this->faker->numberBetween(3000, 7000))
                ->setProfession($this->faker->randomElement(WorkerProfession::cases()))
            ;
            $manager->persist($worker);
            $constraints = $this->validator->validate($worker);
            if ($constraints->count() > 0) {
                throw new ValidationException($constraints);
            }
        }

        $manager->flush();
    }
}
