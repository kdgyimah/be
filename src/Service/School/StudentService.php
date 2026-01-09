<?php

namespace App\Service\School;

use App\Dto\Input\ListInput;
use App\Dto\Output\School\StudentListedOutput;
use App\Entity\School\GradeYear;
use App\Entity\School\Year;
use App\Repository\School\GradeYearRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

readonly class StudentService
{
    public function __construct(private GradeYearRepository $gradeYearRepository)
    {
    }

    /**
     * @return iterable<StudentListedOutput>
     */
    public function getAllByYear(Year $year, ListInput $listInput): iterable
    {
        $gradeYears = $this->gradeYearRepository->findByYear($year, $listInput->page, $listInput->count);

        foreach ($gradeYears as $gradeYear) {
            $res = new StudentListedOutput();
            $res->name = "{$gradeYear->student->firstname} {$gradeYear->student->lastname}";
            $res->grade = $gradeYear->grade->name;
            $res->paymentUpdated = true;
            $res->absenceHours = 1;
            yield $res;
        }
    }

    public function countAllByYear(Year $year): int
    {
        return $this->gradeYearRepository->countByYear($year);
    }
}
