<?php

namespace App\Service;

use App\Entity\School\School;
use App\Entity\User;
use App\Enum\SchoolMemberScope;
use BackedEnum;
use RuntimeException;

readonly class RoleManager
{

    public function addRole(User $user, School $school, BackedEnum $memberScope): void
    {
        $user->addRole($this->getRoleName($school, $memberScope));
    }

    /**
     * @return list<BackedEnum>
     */
    public function getRoles(User $user, object $entity): array
    {
        if ($entity->id === null) {
            throw new RuntimeException();
        }

        if ($entity instanceof School) {
            $roles = $user->getRoles();
            return array_filter(
                SchoolMemberScope::cases(),
                static fn(SchoolMemberScope $memberScope): bool => in_array($this->getRoleName($entity, $memberScope), $roles, true)
            );
        }

        throw new RuntimeException();
    }

    private function getRoleName(School $school, BackedEnum $memberScope): string
    {
        if ($memberScope instanceof SchoolMemberScope) {
            return sprintf('ROLE_%s_%s', $school->id, $memberScope->value);
        }

        throw new RuntimeException();
    }
}
