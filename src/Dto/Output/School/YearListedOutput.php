<?php

namespace App\Dto\Output\School;

use App\Entity\School\Year;
use Symfony\Component\ObjectMapper\Attribute\Map;

#[Map(source: Year::class)]
class YearListedOutput
{
    public string $id;
    public string $name;
    public bool $active;
}
