<?php
declare(strict_types=1);

namespace App\Validation\Rules;

use App\Validation\RuleInterface;

class AlphaNumRule implements RuleInterface
{
    public function passes(mixed $value, ?string $parameter = null): bool
    {
        if (!is_string($value)) {
            return false;
        }

        // permitir guiones bajos y guiones medios si se desea: ajusta la regex.
        return preg_match('/^[A-Za-z0-9_-]+$/', $value) === 1;
    }

    public function message(string $field, ?string $parameter = null): string
    {
        return sprintf('El campo %s sólo puede contener letras, números, guiones y guiones bajos.', $field);
    }
}
