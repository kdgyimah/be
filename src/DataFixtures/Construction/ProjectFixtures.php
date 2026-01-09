<?php

namespace App\DataFixtures\Construction;

use App\Entity\Construction\Client;
use App\Entity\Construction\Company;
use App\Entity\Construction\Engineer;
use App\Entity\Construction\Project;
use App\Enum\ConstructionProjectState;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProjectFixtures extends Fixture implements DependentFixtureInterface
{
    private readonly Faker\Generator $faker;

    public function __construct(private readonly ValidatorInterface $validator)
    {
        $this->faker = Faker\Factory::create('fr_FR');
    }

    public function getDependencies(): array
    {
        return [
            EngineerFixtures::class,
            CompanyFixtures::class,
            ClientFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $company = $this->getReference(CompanyFixtures::COMPANY, Company::class);

        $engineers = range(0, 24);

        for ($i = 0; $i < 25; ++$i) {
            $clientIndex = $this->faker->numberBetween(0, 4);
            $engineerIndex = $this->faker->randomKey($engineers);
            $engineer = $this->getReference("engineer$engineers[$engineerIndex]", Engineer::class);
            unset($engineers[$engineerIndex]);
            $project = new Project($company, $this->getReference("client$clientIndex", Client::class), $engineer);
            $project->setDeliveryDate(new DatePoint('+5 months'));
            $project->setState(ConstructionProjectState::RUNNING);
            $manager->persist($project);
            $constraints = $this->validator->validate($project);
            if ($constraints->count() > 0) {
                throw new ValidationException($constraints);
            }

            $this->addReference("engineer$i", $project);
        }

        $manager->flush();
    }
}
