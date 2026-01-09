<?php

namespace App\DataFixtures\Construction;

use App\DataFixtures\UserFixtures;
use App\Entity\Construction\Company;
use App\Entity\Construction\UserScope;
use App\Entity\User;
use App\Enum\ConstructionScope;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CompanyFixtures extends Fixture implements DependentFixtureInterface
{
    public const string COMPANY = 'company';

    public function __construct(private readonly ValidatorInterface $validator)
    {
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $ceo = $this->getReference(UserFixtures::USER, User::class);

        $company = new Company();
        $company->setName('Company Test');
        $manager->persist($company);
        $this->addReference(CompanyFixtures::COMPANY, $company);

        foreach (ConstructionScope::cases() as $scope) {
            $scope = new UserScope($ceo, $company, $scope);
            $manager->persist($scope);
        }

        $constraints = $this->validator->validate($company);
        if ($constraints->count() > 0) {
            throw new ValidationException($constraints);
        }

        $manager->flush();
    }
}
