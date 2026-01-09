<?php

namespace App\Tests\Unit\Service\School;

use App\Dto\Output\School\SchoolListedOutput;
use App\Entity\School\School;
use App\Entity\User;
use App\Repository\School\SchoolRepository;
use App\Service\School\SchoolService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

class SchoolServiceTest extends TestCase
{
    public function testGetSchools(): void
    {
        $schoolRepository = $this->createMock(SchoolRepository::class);
        $objectMapper = $this->createMock(ObjectMapperInterface::class);
        $service = new SchoolService($schoolRepository, $objectMapper);

        $user = new User();
        $school = new School($user);
        $schoolOutput = new SchoolListedOutput();
        $schoolOutput->name = 'Test School'; // Assuming public property or setters

        $schoolRepository->expects($this->once())
            ->method('findByUser')
            ->with($user)
            ->willReturn([$school]);

        $objectMapper->expects($this->once())
            ->method('map')
            ->with($school, SchoolListedOutput::class)
            ->willReturn($schoolOutput);

        $result = $service->getSchools($user);
        $resultArray = iterator_to_array($result);

        $this->assertCount(1, $resultArray);
        $this->assertSame($schoolOutput, $resultArray[0]);
    }
}
