<?php
declare(strict_types=1);

namespace App\Validation;

use App\Validation\Rules\RequiredRule;
use App\Validation\Rules\MinRule;
use App\Validation\Rules\MaxRule;
use App\Validation\Rules\AlphaNumRule;

/**
 * Validator simple, extensible y sin dependencias externas.
 *
 * Reglas disponibles por defecto:
 *  - required
 *  - min:N
 *  - max:N
 *  - alpha_num
 *
 * Uso rápido:
 *   Validator::make($data, [
 *       'username' => 'required|min:3|alpha_num',
 *       'password' => 'required|min:6'
 *   ]);
 *
 * Si falla lanza ValidationException con errors() => [field => [messages...]]
 */
class Validator
{
    /**
     * Mapping de nombre de regla => clase que implementa RuleInterface
     *
     * Podés extender este mapa añadiendo más reglas.
     *
     * @var array<string, class-string<RuleInterface>>
     */
    private static array $ruleMap = [
        'required'  => RequiredRule::class,
        'min'       => MinRule::class,
        'max'       => MaxRule::class,
        'alpha_num' => AlphaNumRule::class,
    ];

    /**
     * Valida un array de datos contra un set de reglas.
     *
     * @param array<string,mixed> $data
     * @param array<string,string|array<string>> $rules
     *      Regla puede ser 'required|min:3' o ['required', 'min:3']
     * @throws ValidationException
     * @return array<string,mixed> Datos sanitizados (por ahora: valores iniciales)
     */
    public static function make(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $spec) {
            // Normalizar spec a array de strings
            $ruleList = is_array($spec) ? $spec : explode('|', (string)$spec);
            $value = $data[$field] ?? null;

            foreach ($ruleList as $ruleRaw) {
                $ruleRaw = trim((string)$ruleRaw);
                if ($ruleRaw === '') {
                    continue;
                }

                [$ruleName, $parameter] = self::parseRule($ruleRaw);

                if (!isset(self::$ruleMap[$ruleName])) {
                    // Regla no registrada -> saltar o lanzar? Elegimos reportar error técnico
                    throw new \InvalidArgumentException("Regla de validación desconocida: {$ruleName}");
                }

                $ruleClass = self::$ruleMap[$ruleName];
                /** @var RuleInterface $rule */
                $rule = new $ruleClass();

                if (!$rule->passes($value, $parameter)) {
                    $errors[$field][] = $rule->message($field, $parameter);
                }
            }
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        // Por ahora devolvemos $data sin cambios; se puede añadir sanitización por campo
        return $data;
    }

    /**
     * Registra (o reemplaza) una regla personalizada.
     *
     * @param string $name
     * @param class-string<RuleInterface> $class
     * @return void
     */
    public static function extend(string $name, string $class): void
    {
        if (!is_a($class, RuleInterface::class, true)) {
            throw new \InvalidArgumentException("La clase {$class} debe implementar RuleInterface");
        }

        self::$ruleMap[$name] = $class;
    }

    /**
     * Parsea 'min:3' -> ['min', '3']
     *
     * @param string $raw
     * @return array{0:string,1:string|null}
     */
    private static function parseRule(string $raw): array
    {
        if (!str_contains($raw, ':')) {
            return [$raw, null];
        }

        [$name, $param] = explode(':', $raw, 2);
        return [$name, $param];
    }
}
