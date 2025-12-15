<?php

namespace App\Dto\Output\School;

use Symfony\Component\JsonStreamer\Attribute\JsonStreamable;

#[JsonStreamable]
class StudentListedOutput
{
    public string $name;
    public string $grade;
    public bool $paymentUpdated;
    public float $absenceHours;
}
