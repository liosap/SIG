<?php
declare(strict_types=1);

use Core\Application;
use Core\Http\Response;
use Psr\Log\LoggerInterface;

// Cargar contenedor DI
$container = require BASE_PATH . '/config/container.php';

// Cargar definición de rutas
$routes = require BASE_PATH . '/routes/web.php';

/**
 * Manejadores globales de errores/excepciones
 * Solo se activan cuando APP_DEBUG = false
 */
$debug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';

if (!$debug) {
    /** @var LoggerInterface $logger */
    $logger = $container->get(LoggerInterface::class);

    // Convertir errores PHP en excepciones
    set_error_handler(function (
        int $severity,
        string $message,
        string $file,
        int $line
    ) use ($logger) {
        if (!(error_reporting() & $severity)) {
            // Respeta el operador @
            return false;
        }

        throw new \ErrorException($message, 0, $severity, $file, $line);
    });

    // Manejar excepciones no capturadas
    set_exception_handler(function (\Throwable $e) use ($logger) {
        // Log detallado del error
        $logger->error(sprintf(
            'Uncaught exception: %s in %s:%d | trace: %s',
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        ));

        // Respuesta genérica al usuario
        Response::error(500, 'Ha ocurrido un error interno. Inténtalo de nuevo más tarde.');
    });
}

// Devolver instancia de la aplicación lista para ejecutarse
return new Application($container, $routes);