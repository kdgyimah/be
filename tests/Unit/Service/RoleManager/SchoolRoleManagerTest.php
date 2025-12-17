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

class SchoolRoleManagerTest extends TestCase
{
    private MockObject|EntityManagerInterface $entityManager;
    private MockObject|LoggerInterface $logger;
    private SchoolRoleManager $manager;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->manager = new SchoolRoleManager($this->entityManager, $this->logger);
    }

    public function testSetDirectorAlreadyDirector(): void
    {
        $school = new School();
        $user = new User();
        $userScope = new UserScope($user, $school, SchoolScope::DIRECTOR);

        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->once())
            ->method('findBy')
            ->with(['school' => $school, 'scope' => SchoolScope::DIRECTOR])
            ->willReturn([$userScope]);

        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->with(UserScope::class)
            ->willReturn($repository);

        $this->entityManager->expects($this->never())->method('remove');
        $this->entityManager->expects($this->never())->method('persist');

        $this->manager->setDirector($school, $user);
    }

    public function testSetDirectorNewDirectorReplacesOld(): void
    {
        $school = new School();
        // Since we cannot easily set ID on School if it's protected, lets hope Logger doesn't crash on null ID if it uses it.
        // The logger call: sprintf('school %s has many directors', $school->id)
        // If ID is uninitialized, it might be an issue. But usually UUIDs are strings or Uuid objects.
        // Let's assume it's fine, or we mock School if needed.

        $user = new User();
        $oldUser = new User();
        $oldScope = new UserScope($oldUser, $school, SchoolScope::DIRECTOR);

        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->once())
            ->method('findBy')
            ->with(['school' => $school, 'scope' => SchoolScope::DIRECTOR])
            ->willReturn([$oldScope]);

        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->with(UserScope::class)
            ->willReturn($repository);

        $this->entityManager->expects($this->once())->method('remove')->with($oldScope);
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (UserScope $us) use ($user, $school) {
                return $us->user === $user && $us->school === $school && SchoolScope::DIRECTOR === $us->getScope();
            }));

        $this->manager->setDirector($school, $user);
    }
}
