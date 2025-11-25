<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Psr\Log\LoggerInterface;

use App\Repositories\UsuarioRepository;
use App\Services\UsuarioService;

use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\UsuarioController;

return (function () {

    $builder = new ContainerBuilder();

    // Activar autowiring para facilitar futuras extensiones
    $builder->useAutowiring(true);
    $builder->useAttributes(false);

    $builder->addDefinitions([

        /**
         * SETTINGS
         */
        'settings' => require BASE_PATH . '/config/settings.php',

        /**
         * PDO (singleton)
         */
        PDO::class => function (ContainerInterface $c): PDO {
            $settings = $c->get('settings')['db'];

            $pdo = new PDO(
                sprintf(
                    'mysql:host=%s;dbname=%s;charset=utf8mb4',
                    $settings['host'],
                    $settings['database']
                ),
                $settings['username'],
                $settings['password'],
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );

            return $pdo;
        },

        /**
         * LOGGER (Monolog singleton)
         */
        Logger::class => function (): Logger {

            $logDir = BASE_PATH . '/bootstrap/logs';
            if (!is_dir($logDir)) {
                mkdir($logDir, 0777, true);
            }

            $logFile = $logDir . '/app.log';

            $formatter = new LineFormatter(
                "[%datetime%] %level_name%: %message%\n",
                "Y-m-d H:i:s",
                true,
                true
            );

            $handler = new StreamHandler($logFile, Logger::DEBUG);
            $handler->setFormatter($formatter);

            $logger = new Logger('SIG');
            $logger->pushHandler($handler);

            return $logger;
        },

        // Exponer el logger también como LoggerInterface
        LoggerInterface::class => function (ContainerInterface $c): LoggerInterface {
            return $c->get(Logger::class);
        },

        /**
         * REPOSITORIES
         */
        UsuarioRepository::class => function (ContainerInterface $c): UsuarioRepository {
            return new UsuarioRepository($c->get(PDO::class));
        },

        /**
         * SERVICES
         */
        UsuarioService::class => function (ContainerInterface $c): UsuarioService {
            return new UsuarioService(
                $c->get(UsuarioRepository::class),
                $c->get(LoggerInterface::class)
            );
        },

        /**
         * CONTROLLERS
         */
        AuthController::class => function (ContainerInterface $c): AuthController {
            return new AuthController(
                $c->get(UsuarioService::class),
                $c->get(LoggerInterface::class)
            );
        },

        HomeController::class => fn() => new HomeController(),

        UsuarioController::class => fn(ContainerInterface $c) =>
            new UsuarioController($c->get(UsuarioService::class)),
    ]);

    return $builder->build();
})();