<?php

namespace App\Dto\Output\School;

use Symfony\Component\JsonStreamer\Attribute\JsonStreamable;

#[JsonStreamable]
class SchoolListedOutput
{
    public string $id;

    public string $name;
}
