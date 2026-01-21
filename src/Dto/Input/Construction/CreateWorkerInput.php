<?php

namespace App\Dto\Input\Construction;

use App\Enum\WorkerProfession;
use App\Validator\IsPhoneNumber;
use Symfony\Component\Validator\Constraints as Assert;

class CreateWorkerInput
{
    #[Assert\NotBlank(message: 'firstname is empty')]
    public private(set) string $firstname;

    #[Assert\NotBlank(message: 'lastname is empty')]
    public private(set) string $lastname;

    #[Assert\NotBlank(message: 'phone number is empty')]
    #[IsPhoneNumber]
    public private(set) string $phoneNumber;

    #[Assert\Positive]
    #[Assert\NotBlank(message: 'daily salary is empty')]
    public private(set) float $dailySalary;

    public private(set) WorkerProfession $profession;
}
