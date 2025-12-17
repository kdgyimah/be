<?php

namespace App\Tests\Unit\Service\School;

use App\Dto\Output\School\StudentListedOutput;
use App\Entity\School\Grade;
use App\Entity\School\Level;
use App\Entity\School\Student;
use App\Entity\School\StudentYear;
use App\Service\School\StudentService;
use PHPUnit\Framework\TestCase;

class StudentServiceTest extends TestCase
{
    public function testGenerateStudentListedOutput(): void
    {
        $service = new StudentService();

        $student = new Student();
        $student->setFirstname('John');
        $student->setLastname('Doe');

        $grade = new Grade($this->createMock(Level::class));
        $refGradeName = new \ReflectionProperty(Grade::class, 'name');
        $refGradeName->setValue($grade, '1st Grade');

        $studentYear = $this->createMock(StudentYear::class);
        // We can't set readonly properties on mock easily if they are public readonly?
        // StudentYear properties accessed: $studentYear->student, $studentYear->grade.
        // If they are public readonly, we can try to set them on a real object via reflection.

        $year = $this->createMock(\App\Entity\School\Year::class);
        $studentYearReal = new StudentYear($year, $grade, $student);

        $iterable = [$studentYearReal];

        $result = $service->generateStudentListedOutput($iterable);
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
