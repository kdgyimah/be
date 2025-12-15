<?php

namespace App\Command;

use App\Entity\School\School;
use App\Entity\User;
use App\Service\RoleManager\SchoolRoleManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand('app:setup:env')]
class CreateAdminCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly SchoolRoleManager $schoolRoleManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $schoolRepository = $this->entityManager->getRepository(School::class);

        $user = $userRepository->findOneBy(['email' => 'directeur@email.com']) ?? new User();

        $user->setFirstname('direc')
            ->setLastname('teur')
            ->setEmail('directeur@email.com');

        $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));

        $school = $schoolRepository->findOneBy(['name' => 'Pierre et marie currie']) ?? new School()
            ->setName('Pierre et marie currie');

        $this->entityManager->persist($school);
        $this->entityManager->persist($user);

        $this->entityManager->flush();
        $this->schoolRoleManager->setDirector($school, $user);

        return Command::SUCCESS;
    }
}
