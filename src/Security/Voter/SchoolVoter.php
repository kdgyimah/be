<?php

namespace App\Security\Voter;

use App\Entity\School\School;
use App\Entity\School\SchoolUserScope;
use App\Entity\User;
use App\Repository\SchoolUserScopeRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SchoolVoter extends Voter
{
    public const string SHOW = SchoolVoter::class.':show';

    public function __construct(private readonly SchoolUserScopeRepository $schoolUserScopeRepository)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === SchoolVoter::SHOW && $subject instanceof School;
    }

    public function supportsAttribute(string $attribute): bool
    {
        return $attribute === SchoolVoter::SHOW;
    }

    public function supportsType(string $subjectType): bool
    {
        return is_a($subjectType, School::class, true);
    }

    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token,
        ?Vote $vote = null
    ): bool {
        /** @var ?User $user */
        $user = $token->getUser();

        if (!$user instanceof User) {
            $vote?->addReason('user is not logged in');
            return false;
        }

        /** @var School $school */
        $school = $subject;

        $scope = $this->schoolUserScopeRepository->findOneBy(['user' => $user->id, 'school' => $school->id]);

        if (!$scope instanceof SchoolUserScope) {
            $vote?->addReason("user doesn't have role in school");
            return false;
        }

        return true;
    }
}
