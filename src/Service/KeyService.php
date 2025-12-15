<?php

namespace App\Service;

use App\Entity\School\School;

readonly class KeyService
{
    public function getYearActiveKey(School $school): string
    {
        return sprintf('%s-active-year', $school->id);
    }
}
