<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class IsPhoneNumber extends Constraint
{
    public string $message = 'The phone number is not a valid e164 phone number.';

    public function __construct(
        mixed $options = null,
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct($options, $groups, $payload);
    }
}
