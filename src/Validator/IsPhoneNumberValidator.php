<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class IsPhoneNumberValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof IsPhoneNumber) {
            throw new UnexpectedTypeException($constraint, IsPhoneNumber::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        if (preg_match('/^\+[1-9]\d{1,14}$/', $value)) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->addViolation();
    }
}
