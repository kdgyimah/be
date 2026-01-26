<?php

namespace App\Dto;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

class MoneyDto
{
    #[Assert\PositiveOrZero]
    public int $amount;
    #[Assert\Currency]
    #[OA\Property(description: 'check the currency code list from https://www.localeplanet.com/icu/currency.html', example: 'USD')]
    public string $currency;
}
