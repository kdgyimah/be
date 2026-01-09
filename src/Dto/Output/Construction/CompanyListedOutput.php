<?php

namespace App\Dto\Output\Construction;

use Symfony\Component\JsonStreamer\Attribute\JsonStreamable;

#[JsonStreamable]
class CompanyListedOutput
{
    public string $id;
    public string $name;
}
