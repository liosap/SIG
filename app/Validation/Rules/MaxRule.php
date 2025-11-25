<?php
declare(strict_types=1);

namespace App\Validation\Rules;

use App\Validation\RuleInterface;

class MaxRule implements RuleInterface
{
    public function passes(mixed $value, ?string $parameter = null): bool
    {
        if ($parameter === null) {
            return false;
        }

        $max = (int)$parameter;

        if (is_string($value)) {
            return mb_strlen($value) <= $max;
        }

        if (is_numeric($value)) {
            return $value <= $max;
        }

        if (is_array($value)) {
            return count($value) <= $max;
        }

        return false;
    }

    public function message(string $field, ?string $parameter = null): string
    {
        return sprintf('El campo %s no puede tener más de %s caracteres.', $field, $parameter);
    }
}
