<?php
declare(strict_types=1);

namespace App\Helpers;

class Csrf
{
    private const TOKEN_KEY = '_csrf_token';

    /**
     * Devuelve el token CSRF existente o genera uno nuevo.
     * Se asume que la sesión YA está iniciada en public/index.php.
     */
    public static function token(): string
    {
        if (empty($_SESSION[self::TOKEN_KEY])) {
            $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::TOKEN_KEY];
    }

    /**
     * Valida el token recibido contra el almacenado en la sesión.
     */
    public static function validate(?string $token): bool
    {
        $sessionToken = $_SESSION[self::TOKEN_KEY] ?? null;
        if ($token === null || $sessionToken === null) {
            return false;
        }
        return hash_equals($sessionToken, $token);
    }

    /**
     * Devuelve un <input hidden> seguro para formularios.
     */
    public static function field(): string
    {
        $token = self::token();
        return '<input type="hidden" name="_csrf" value="' .
            htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Fuerza regeneración del token CSRF.
     * Útil después de login/logout.
     */
    public static function regenerate(): void
    {
        $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(32));
    }
}
