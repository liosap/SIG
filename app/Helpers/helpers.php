<?php
declare(strict_types=1);

// El namespace NO debe estar si quieremos helpers globales
use App\Helpers\Csrf;

/**
 * Escape seguro de HTML.
 */
function e(string $str): string
{
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Genera una URL absoluta segura y normalizada.
 */
function url(string $path = ''): string
{
    // Tomar APP_URL sin el slash final
    $base = rtrim($_ENV['APP_URL'] ?? '', '/');

    // Normalizar path
    $path = '/' . ltrim((string)$path, '/');

    // Si no hay APP_URL configurada, devolver ruta relativa
    if ($base === '') {
        return $path;
    }

    return $base . $path;
}

/**
 * Wrapper del campo CSRF.
 */
function csrf_field(): string
{
    return Csrf::field();
}

/**
 * Mensajes flash basados en sesión.
 *
 * Uso:
 *   flash('success', 'Usuario creado correctamente'); // set
 *   $msg = flash('success'); // get + borrar
 */
function flash(string $key, ?string $message = null): ?string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        // Sesión no iniciada; no hacemos nada
        return null;
    }

    // Set
    if ($message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return null;
    }

    // Get + delete
    if (!isset($_SESSION['_flash'][$key])) {
        return null;
    }

    $value = $_SESSION['_flash'][$key];
    unset($_SESSION['_flash'][$key]);

    return is_string($value) ? $value : null;
}