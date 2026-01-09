<?php

namespace App\Security\Voter;

use App\Entity\School\School;
use App\Entity\User;
use App\Enum\SchoolScope;
use App\Repository\School\UserScopeRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SchoolVoter extends Voter
{
    public const string SHOW = SchoolVoter::class.':show';
    public const string MANAGE_STUDENTS = SchoolVoter::class.':manageStudents';

    public function __construct(private readonly UserScopeRepository $schoolUserScope)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $this->supportsAttribute($attribute) && $this->supportsType(get_debug_type($subject));
    }

    public function supportsAttribute(string $attribute): bool
    {
        return in_array($attribute, [SchoolVoter::SHOW, SchoolVoter::MANAGE_STUDENTS]);
    }

    public function supportsType(string $subjectType): bool
    {
        return is_a($subjectType, School::class, true) || 'null' === $subjectType;
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

        return match ($attribute) {
            SchoolVoter::SHOW => $this->schoolUserScope->count(['user' => $user, 'school' => $subject->id]) > 0,
            SchoolVoter::MANAGE_STUDENTS => $this->schoolUserScope->count([
                'user' => $user,
                'school' => $subject->id,
                'scope' => SchoolScope::MANAGE_STUDENTS,
            ]) > 0,
            default => false,
        };
    }
}
