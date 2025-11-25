<?php
declare(strict_types=1);

namespace App\Validation\Rules;

use App\Validation\RuleInterface;

class MinRule implements RuleInterface
{
    public function passes(mixed $value, ?string $parameter = null): bool
    {
        if ($parameter === null) {
            return false;
        }

        $min = (int)$parameter;

        if (is_string($value)) {
            return mb_strlen($value) >= $min;
        }

        if (is_numeric($value)) {
            return $value >= $min;
        }

        if (is_array($value)) {
            return count($value) >= $min;
        }

        return false;
    }

    public function message(string $field, ?string $parameter = null): string
    {
        return sprintf('El campo %s debe tener al menos %s caracteres.', $field, $parameter);
    }
}
