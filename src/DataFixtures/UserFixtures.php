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
    public const string DIRECTOR = 'DIRECTOR';

    private readonly Faker\Generator $faker;

    public function __construct(
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly ValidatorInterface $validator
    ) {
        $this->faker = Faker\Factory::create();
    }

    public function load(ObjectManager $manager): void
    {
        $user = $this->createUser(
            'Michael',
            'Jordan',
            'mj@yesman.com',
            roles: [User::ROLE_ADMIN],
        );

        $manager->persist($user);

        $user = $this->createUser(
            'firstname Dir',
            'lastname Dir',
            'dir@mariecurrie.com',
        );

        $manager->persist($user);
        $this->addReference(UserFixtures::DIRECTOR, $user);

        for ($i = 0; $i < 10; $i++) {
            $user = $this->createUser();
            $manager->persist($user);
        }
        $manager->flush();
    }

    private function createUser(
        ?string $firstname = null,
        ?string $lastname = null,
        ?string $email = null,
        array $roles = [],
    ): User {
        $user = new User()
            ->setFirstname($firstname ?? $this->faker->firstName())
            ->setLastname($lastname ?? $this->faker->lastName())
            ->setEmail($email ?? $this->faker->email());

        foreach ($roles as $role) {
            $user->addRole($role);
        }

        $user->setPassword($this->userPasswordHasher->hashPassword($user,'password'));

        $constraint = $this->validator->validate($user);

        if ($constraint->count() > 0) {
            throw new ConstraintDefinitionException($constraint);
        }

        return $user;
    }
}
