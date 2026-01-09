<?php

namespace App\Tests\Unit\Service\School;

use App\Dto\Input\ListInput;
use App\Dto\Output\School\StudentListedOutput;
use App\Entity\School\Grade;
use App\Entity\School\Level;
use App\Entity\School\School;
use App\Entity\School\Student;
use App\Entity\School\GradeYear;
use App\Entity\School\Year;
use App\Entity\User;
use App\Repository\School\GradeYearRepository;
use App\Service\School\StudentService;
use PHPUnit\Framework\TestCase;

class StudentServiceTest extends TestCase
{
    public function testGetAllByYear(): void
    {
        $gradeYearRepository = $this->createMock(GradeYearRepository::class);
        $service = new StudentService($gradeYearRepository);

        $school = new School(new User());
        $student = new Student($school);
        $student->setFirstname('John');
        $student->setLastname('Doe');

        $grade = new Grade($this->createMock(Level::class));
        $refGradeName = new \ReflectionProperty(Grade::class, 'name');
        $refGradeName->setValue($grade, '1st Grade');

        $year = $this->createMock(Year::class);
        $levelYear = $this->createMock(\App\Entity\School\LevelYear::class);
        $refLevelYearYear = new \ReflectionProperty(\App\Entity\School\LevelYear::class, 'year');
        $refLevelYearYear->setValue($levelYear, $year);
        $studentYearReal = new GradeYear($levelYear, $grade, $student);

        $listInput = new ListInput(1, 10);

        $paginator = $this->createMock(\Doctrine\ORM\Tools\Pagination\Paginator::class);
        $paginator->method('getIterator')->willReturn(new \ArrayIterator([$studentYearReal]));

        $gradeYearRepository->expects($this->once())
            ->method('findByYear')
            ->with($year, 1, 10)
            ->willReturn($paginator);

        $result = $service->getAllByYear($year, $listInput);
        $resultArray = iterator_to_array($result);

        $this->assertCount(1, $resultArray);
        $output = $resultArray[0];
        $this->assertInstanceOf(StudentListedOutput::class, $output);
        $this->assertEquals('John Doe', $output->name);
        $this->assertEquals('1st Grade', $output->grade);
        $this->assertTrue($output->paymentUpdated);
        $this->assertEquals(1, $output->absenceHours);
    }
}
