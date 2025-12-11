<?php

namespace App\Dto\Output\School;

use App\Entity\School\School;
use Symfony\Component\ObjectMapper\Attribute\Map;

#[Map(source: School::class)]
class SchoolListedOutput
{
    public string $id;

    public string $name;
}
