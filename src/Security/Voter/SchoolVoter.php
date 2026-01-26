<?php

namespace App\Security\Voter;

use App\Constant\SchoolScope;
use App\Entity\School\School;
use App\Entity\School\UserScope;
use App\Entity\User;
use App\Repository\UserScopeRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SchoolVoter extends Voter
{
    public function __construct(private readonly UserScopeRepository $userScopeRepository)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $this->supportsAttribute($attribute) && $this->supportsType(get_debug_type($subject));
    }

    public function supportsAttribute(string $attribute): bool
    {
        return in_array($attribute, SchoolScope::getScopes());
    }

    public function supportsType(string $subjectType): bool
    {
        return is_a($subjectType, School::class, true);
    }

    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token,
        ?Vote $vote = null,
    ): bool {
        $user = $token->getUser();

        if (!$user instanceof User) {
            $vote?->addReason('user is not logged in');

            return false;
        }

        if (!$subject instanceof School) {
            $vote?->addReason('subject is not a school');

            return false;
        }

        $userScope = $this->userScopeRepository->findByUserAndSchool($user, $subject);

        if (!$userScope instanceof UserScope) {
            $vote?->addReason('user has no scope for this school');

            return false;
        }

        return in_array($attribute, $userScope->scopes);
    }
}
