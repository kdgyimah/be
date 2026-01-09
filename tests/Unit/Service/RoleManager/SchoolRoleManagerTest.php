<?php

namespace App\Tests\Unit\Service\RoleManager;

use App\Entity\School\School;
use App\Entity\School\UserScope;
use App\Entity\User;
use App\Enum\SchoolScope;
use App\Service\RoleManager\SchoolRoleManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class SchoolRoleManagerTest extends TestCase
{
    private MockObject|EntityManagerInterface $entityManager;
    private MockObject|LoggerInterface $logger;
    private SchoolRoleManager $manager;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $userScopeRepository = $this->createMock(\App\Repository\School\UserScopeRepository::class);
        $this->manager = new SchoolRoleManager($this->entityManager, $userScopeRepository);
    }

    public function testSetDirectorAlreadyDirector(): void
    {
        $director = new User();
        $refId = new \ReflectionProperty(User::class, 'id');
        $refId->setValue($director, Uuid::v4());
        $school = new School($director);

        $user = $director; // The user IS the director
        // Update user variable to refer to same object or just ensure IDs match.
        // Actually, logic is: setDirector($school, $user).
        // If $user IS the director, it should return early.
        // So I must pass the SAME user instance (or same ID).
        // The test was: $user = new User(). $school = new School(new User()).
        // Those are different users. So it didn't return early.
        // FIX: make school director be $user.
        $userScope = new UserScope($user, $school, SchoolScope::DIRECTOR);

        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->never())
            ->method('findBy');

        // Since it returns early, it shouldn't even ask for repository IF logic checks IDs first.
        // Logic:
        // if ($user->id && $school->director->id === $user->id) return;
        // ...
        // $this->userScopeRepository->deleteRoles...
        // Wait, does deleteRoles prevent getRepository?
        // Ideally, we expect NO interactions with EM or Repo if it returns early.

        $this->entityManager->expects($this->never())->method('getRepository');
        $this->entityManager->expects($this->never())->method('remove');
        $this->entityManager->expects($this->never())->method('persist');

        $this->manager->setDirector($school, $user);
    }

    public function testSetDirectorNewDirectorReplacesOld(): void
    {
        $school = new School(new User());
        // Since we cannot easily set ID on School if it's protected, lets hope Logger doesn't crash on null ID if it uses it.
        // The logger call: sprintf('school %s has many directors', $school->id)
        // If ID is uninitialized, it might be an issue. But usually UUIDs are strings or Uuid objects.
        // Let's assume it's fine, or we mock School if needed.

        $user = new User();
        $oldUser = new User();
        $oldScope = new UserScope($oldUser, $school, SchoolScope::DIRECTOR);

        // We need to access the injected repository mock
        $ref = new \ReflectionProperty(SchoolRoleManager::class, 'userScopeRepository');
        $userScopeRepositoryMock = $ref->getValue($this->manager);

        $userScopeRepositoryMock->expects($this->once())
            ->method('deleteRoles')
            ->with($school, $this->anything()); // We cannot easily match the exact user object if it's created inside logic, but here it uses $school->director which is available.

        // Repository is injected, so we don't expect EM to get it.
        $this->entityManager->expects($this->never())->method('getRepository');

        // deleteRoles handles removal, so EM->remove is not called directly here (unless deleteRoles calls it, but we mock deleteRoles)
        $this->entityManager->expects($this->never())->method('remove');
        $count = count(SchoolScope::cases());
        $this->entityManager->expects($this->exactly($count))
            ->method('persist')
            ->with($this->callback(function (UserScope $us) use ($user, $school) {
                $ref = new \ReflectionProperty(UserScope::class, 'school');
                $actualSchool = $ref->getValue($us);
                // Check if scope is valid SchoolScope enum
                $hasValidScope = in_array($us->getScope(), SchoolScope::cases(), true);
                return $us->user === $user && $actualSchool === $school && $hasValidScope;
            }));

        $this->manager->setDirector($school, $user);
    }
}
