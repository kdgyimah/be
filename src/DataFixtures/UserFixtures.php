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
    public const string USER = 'USER';

    private readonly Faker\Generator $faker;

    public function __construct(
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly ValidatorInterface $validator,
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
            'user firstname',
            'user lastname Dir',
            'user@company.com',
        );

        $manager->persist($user);

        $constraint = $this->validator->validate($user);

        if ($constraint->count() > 0) {
            throw new ConstraintDefinitionException($constraint);
        }

        $this->addReference(UserFixtures::USER, $user);

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
            ->setEmail($email ?? $this->faker->email())
            ->setPhoneNumber($this->faker->e164PhoneNumber())
        ;

        foreach ($roles as $role) {
            $user->addRole($role);
        }

        $user->setPassword($this->userPasswordHasher->hashPassword($user, 'password'));

        return $user;
    }
}
