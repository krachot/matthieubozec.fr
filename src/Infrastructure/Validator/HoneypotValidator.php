<?php

namespace App\Infrastructure\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class HoneypotValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof Honeypot) {
            throw new UnexpectedTypeException($constraint, Honeypot::class);
        }

        if (null !== $value && !\is_scalar($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        if ('' !== trim((string) $value)) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
