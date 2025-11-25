<?php
declare(strict_types=1);

namespace App\Validation;

interface RuleInterface
{
    /**
     * Valida un valor.
     *
     * @param mixed $value Valor a validar (puede ser null)
     * @param string|null $parameter Parámetro opcional (por ejemplo en min:3 -> '3')
     * @return bool  true si pasa, false si falla
     */
    public function passes(mixed $value, ?string $parameter = null): bool;

    /**
     * Mensaje de error para la regla.
     *
     * @param string $field Nombre del campo
     * @param string|null $parameter Parámetro de la regla
     * @return string
     */
    public function message(string $field, ?string $parameter = null): string;
}
