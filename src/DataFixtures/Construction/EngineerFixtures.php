<?php

namespace App\DataFixtures\Construction;

use App\Entity\Construction\Company;
use App\Entity\Construction\Engineer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EngineerFixtures extends Fixture implements DependentFixtureInterface
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
            $engineer = new Engineer($company)
                ->setFirstname($this->faker->firstName())
                ->setLastname($this->faker->lastName())
                ->setPhoneNumber($this->faker->e164PhoneNumber());
            $manager->persist($engineer);
            $constraints = $this->validator->validate($engineer);
            if ($constraints->count() > 0) {
                throw new ValidationException($constraints);
            }

            $this->addReference("engineer$i", $engineer);
        }

        $manager->flush();
    }
}
