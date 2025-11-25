<?php
declare(strict_types=1);

/**
 * public/index.php
 * Entrada pública: carga autoload, .env, configura sesión segura y headers.
 */

define('BASE_PATH', dirname(__DIR__));

/*
 * -------------------------
 * Autoload + ENV (PRIMERO)
 * -------------------------
 */
require BASE_PATH . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->safeLoad();

/*
 * -------------------------
 * Timezone global
 * -------------------------
 */
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'UTC');

/*
 * -------------------------
 * PHP SESSION HARDENING (ini settings)
 * -------------------------
 */
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
// No forzamos cookie_httponly aquí: lo ponemos por session_set_cookie_params
// ini_set('session.cookie_httponly', '1'); // opcional, duplicado con params

/*
 * -------------------------
 * Configuración de Cookies / Sesión
 * -------------------------
 */
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

// Determinar cookie path desde APP_URL (si existe)
$baseUrl = $_ENV['APP_URL'] ?? '';
$cookiePath = '/';
if ($baseUrl !== '') {
    $parsed = parse_url($baseUrl);
    $cookiePath = rtrim($parsed['path'] ?? '/', '/');  // <— sin slash final

    if ($cookiePath === '') {
        $cookiePath = '/';
    }
}

// SESSION_DOMAIN: preferir vacío en local; si viene 'localhost' lo normalizamos a ''
$sessionDomain = $_ENV['SESSION_DOMAIN'] ?? '';
if ($sessionDomain === 'localhost') {
    $sessionDomain = '';
}

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => $cookiePath,
    'domain'   => $sessionDomain,
    'secure'   => $secure,
    'httponly' => true,
    'samesite' => 'Lax',
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
 * -------------------------
 * Security headers mínimos
 * -------------------------
 */
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:;");

if ($secure) {
    // Solo aplicar HSTS si estamos en HTTPS
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}

/*
 * -------------------------
 * Debug opcional (controlado por .env)
 * -------------------------
 */
$debug = (($_ENV['APP_DEBUG'] ?? 'false') === 'true');

if ($debug) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
    //error_log("DEBUG MODE ACTIVADO");
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(0);
}

/*
 * -------------------------
 * Bootstrap / Run app
 * -------------------------
 */
$app = require BASE_PATH . '/bootstrap/app.php';

$app->run();
