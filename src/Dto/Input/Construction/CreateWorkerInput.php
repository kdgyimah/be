<?php

namespace App\Dto\Input\Construction;

use App\Dto\MoneyDto;
use App\Enum\WorkerProfession;
use App\Validator\IsPhoneNumber;
use Symfony\Component\Validator\Constraints as Assert;

class CreateWorkerInput
{
    #[Assert\NotBlank(message: 'firstname is empty')]
    public string $firstname;

    #[Assert\NotBlank(message: 'lastname is empty')]
    public string $lastname;

    #[Assert\NotBlank(message: 'phone number is empty')]
    #[IsPhoneNumber]
    public string $phoneNumber;

    #[Assert\Valid]
    public MoneyDto $dailySalary;

    public WorkerProfession $profession;

    public function __construct()
    {
        $this->dailySalary = new MoneyDto();
    }
}
