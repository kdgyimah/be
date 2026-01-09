<?php

namespace App\Security\Voter;

use App\Entity\Construction\Company;
use App\Entity\User;
use App\Enum\ConstructionScope;
use App\Repository\Construction\UserScopeRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ConstructionVoter extends Voter
{
    public function __construct(private UserScopeRepository $userScopeRepository)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $this->supportsType(get_debug_type($subject)) && $this->supportsAttribute($attribute);
    }

    public function supportsAttribute(string $attribute): bool
    {
        return null !== ConstructionScope::tryFrom($attribute);
    }

    public function supportsType(string $subjectType): bool
    {
        return is_a($subjectType, Company::class, true) || 'null' === $subjectType;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            $vote?->addReason('user is not logged in');

            return false;
        }

        if (!$subject instanceof Company) {
            $vote?->addReason('subject is not a company');

            return false;
        }

        return $this->userScopeRepository->count(['company' => $subject, 'user' => $user, 'scope' => $attribute]) > 0;
    }
}
