<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly ValidatorInterface $validator
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User()
            ->setFirstname('Michael')
            ->setLastname('Jordan')
            ->setEmail('mj@yesman.com')
            ->addRole(User::ROLE_ADMIN);

        $user->setPassword($this->userPasswordHasher->hashPassword($user, 'password'));

        $constraint = $this->validator->validate($user);

        if ($constraint->count() > 0) {
            throw new ConstraintDefinitionException($constraint);
        }

        $manager->persist($user);

        $faker = Faker\Factory::create();

        for ($i = 0; $i < 10; $i++) {
            $user = new User()
                ->setFirstname($faker->firstName())
                ->setLastname($faker->lastName())
                ->setEmail($faker->email());

            $user->setPassword($this->userPasswordHasher->hashPassword($user, 'password'));

            $constraint = $this->validator->validate($user);

            if ($constraint->count() > 0) {
                throw new ConstraintDefinitionException($constraint);
            }

            $manager->persist($user);
        }
        $manager->flush();
    }
}
