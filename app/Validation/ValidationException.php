<?php
declare(strict_types=1);

namespace App\Validation;

use RuntimeException;

class ValidationException extends RuntimeException
{
    /**
     * Array asociativo de errores: campo => [mensajes...]
     *
     * @var array<string, string[]>
     */
    private array $errors = [];

    /**
     * @param array<string,string[]> $errors
     */
    public function __construct(array $errors)
    {
        parent::__construct('Validation failed');
        $this->errors = $errors;
    }

    /**
     * Devuelve los errores.
     *
     * @return array<string,string[]>
     */
    public function errors(): array
    {
        return $this->errors;
    }
}
