<?php

namespace App\Service\RoleManager;

use App\Entity\School\School;
use App\Entity\School\UserScope;
use App\Entity\User;
use App\Enum\SchoolScope;
use App\Repository\School\UserScopeRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class SchoolRoleManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserScopeRepository $userScopeRepository,
    ) {
    }

    public function setDirector(School $school, User $user): void
    {
        if (null !== $user->id && $school->director->id === $user->id) {
            return;
        }

        $this->userScopeRepository->deleteRoles($school, $school->director);

        foreach (SchoolScope::cases() as $schoolScope) {
            $scope = new UserScope($user, $school, $schoolScope);
            $this->entityManager->persist($scope);
        }

        $school->setDirector($user);

        $this->entityManager->flush();
    }
}
