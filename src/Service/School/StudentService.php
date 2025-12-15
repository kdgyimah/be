<?php

namespace App\Service\School;

use App\Dto\Output\School\StudentListedOutput;
use App\Entity\School\StudentYear;
use Generator;

readonly class StudentService
{
    /**
     * @param iterable<StudentYear> $studentYears
     * @return iterable<StudentListedOutput>
     */
    public function generateStudentListedOutput(iterable $studentYears): iterable
    {
        foreach ($studentYears as $studentYear) {
            $res = new StudentListedOutput();
            $res->name = "{$studentYear->student->firstname} {$studentYear->student->lastname}";
            $res->grade = $studentYear->grade->name;
            $res->paymentUpdated = true;
            $res->absenceHours = 1;
            yield $res;
        }
    }
}
