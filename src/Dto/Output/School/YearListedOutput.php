<?php

namespace App\Dto\Output\School;

use Symfony\Component\JsonStreamer\Attribute\JsonStreamable;

#[JsonStreamable]
class YearListedOutput
{
    public string $id;
    public string $name;
    public bool $active;
}
