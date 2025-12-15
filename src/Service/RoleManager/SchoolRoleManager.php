<?php

namespace App\Service\RoleManager;

use App\Entity\School\School;
use App\Entity\School\UserScope;
use App\Entity\User;
use App\Enum\SchoolScope;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

final readonly class SchoolRoleManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    ) {
    }

    public function setDirector(School $school, User $user): void
    {
        $currentUserScopes = $this->entityManager
            ->getRepository(UserScope::class)
            ->findBy(['school' => $school, 'scope' => SchoolScope::DIRECTOR]);

        $exists = false;

        if (count($currentUserScopes) > 1) {
            $this->logger->error(sprintf('school %s has many directors', $school->id));
        }

        foreach ($currentUserScopes as $currentUserScope) {
            if ($currentUserScope->user === $user) {
                $exists = true;
                break;
            }
        }

        if ($exists) {
            return;
        }

        foreach ($currentUserScopes as $currentUserScope) {
            $this->entityManager->remove($currentUserScope);
        }

        $currentUserScope = new UserScope($user, $school, SchoolScope::DIRECTOR);
        $this->entityManager->persist($currentUserScope);
        $this->entityManager->flush();
    }
}
