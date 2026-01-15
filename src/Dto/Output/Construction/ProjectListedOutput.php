<?php

namespace App\Dto\Output\Construction;

use Symfony\Component\JsonStreamer\Attribute\JsonStreamable;

#[JsonStreamable]
class ProjectListedOutput
{
    public string $id;
    public string $name;
}
