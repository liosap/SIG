<?php
declare(strict_types=1);

namespace App\Validation\Rules;

use App\Validation\RuleInterface;

class RequiredRule implements RuleInterface
{
    public function passes(mixed $value, ?string $parameter = null): bool
    {
        if ($value === null) {
            return false;
        }
        if (is_string($value)) {
            return trim($value) !== '';
        }
        if (is_array($value)) {
            return count($value) > 0;
        }
        return $value !== '';
    }

    public function message(string $field, ?string $parameter = null): string
    {
        return sprintf('El campo %s es obligatorio.', $field);
    }
}
