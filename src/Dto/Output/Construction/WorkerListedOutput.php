<?php

namespace App\Dto\Output\Construction;

use Symfony\Component\JsonStreamer\Attribute\JsonStreamable;

#[JsonStreamable]
class WorkerListedOutput
{
    public string $firstname;
    public string $lastname;

}
