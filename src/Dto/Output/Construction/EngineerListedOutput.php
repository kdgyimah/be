<?php

namespace App\Dto\Output\Construction;

use Symfony\Component\JsonStreamer\Attribute\JsonStreamable;

#[JsonStreamable]
class EngineerListedOutput
{
    public string $firstname;
    public string $lastname;
    public ?ProjectListedOutput $project;
}
