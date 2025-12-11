<?php

namespace App\ObjectMapper;

use App\Entity\School\SchoolUserScope;
use App\Entity\User;
use App\Enum\SchoolScope;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\ObjectMapper\TransformCallableInterface;

readonly class SchoolRolesTransformer implements TransformCallableInterface
{
    private User $currentUser;

    public function __construct(
        private EntityManagerInterface $entityManager,
        Security $security
    ) {
        $user = $security->getUser();
        if (!$user instanceof User) {
            throw new \LogicException();
        }
        $this->currentUser = $user;
    }

    /**
     * @param mixed $value
     * @param object $source
     * @param object|null $target
     * @return list<string>
     */
    public function __invoke(mixed $value, object $source, ?object $target): array
    {
        $scopes = $this->entityManager
            ->getRepository(SchoolUserScope::class)->findBy(['user' => $this->currentUser, 'school' => $source]);

        return array_map(static fn (SchoolUserScope $scope) => $scope->getScope()->value, $scopes);
    }
}
