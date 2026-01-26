<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Embeddable]
class Money
{
    #[Assert\PositiveOrZero]
    #[ORM\Column(type: Types::INTEGER)]
    public private(set) int $amount = 0;

    #[Assert\Currency]
    #[ORM\Column(type: Types::STRING, length: 3)]
    public private(set) string $currency;

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }
}
