<?php
declare(strict_types=1);

namespace Core;

use Core\Http\Request;
use Core\Http\Response;
use DI\Container;

final class Router
{
    private Container $container;

    /** @var array<string, array<string, array>> */
    private array $routes = [
        'GET'  => [],
        'POST' => [],
    ];

    private string $groupPrefix = '';

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    // ---------------------------------------------
    // Registrar Rutas
    // ---------------------------------------------
    public function get(string $uri, string $action, array $middlewares = []): void
    {
        $this->addRoute('GET', $uri, $action, $middlewares);
    }

    public function post(string $uri, string $action, array $middlewares = []): void
    {
        $this->addRoute('POST', $uri, $action, $middlewares);
    }

    public function group(string $prefix, callable $callback): void
    {
        $previous = $this->groupPrefix;

        $prefix = '/' . trim($prefix, '/');
        $this->groupPrefix .= $prefix;

        $callback($this);

        $this->groupPrefix = $previous;
    }

    private function addRoute(string $method, string $uri, string $action, array $middlewares): void
    {
        $uri = '/' . trim($this->groupPrefix . '/' . trim($uri, '/'), '/');

        $this->routes[$method][$uri] = [
            'action'      => $action,
            'middlewares' => $middlewares,
            'pattern'     => $this->compilePattern($uri)
        ];
    }

    // ---------------------------------------------
    // Parámetros dinámicos
    // ---------------------------------------------
    private function compilePattern(string $uri): string
    {
        $pattern = preg_replace(
            ['#\{(\w+):int\}#', '#\{(\w+):string\}#'],
            ['(?P<$1>\d+)', '(?P<$1>[A-Za-z0-9_-]+)'],
            $uri
        );

        return "#^" . $pattern . "$#";
    }

    // ---------------------------------------------
    // Normalización de la URI usando APP_URL
    // ---------------------------------------------
    private function stripBaseUrl(string $uri): string
    {
        $baseUrl = $_ENV['APP_URL'] ?? '';
        if ($baseUrl === '') {
            return $uri;
        }

        // Si $uri es una URL absoluta, extraer path
        if (preg_match('#^https?://#i', $uri)) {
            $parsedUri = parse_url($uri);
            $path = $parsedUri['path'] ?? '/';
        } else {
            $path = $uri;
        }

        $parsedBase = parse_url($baseUrl);
        $prefix = rtrim($parsedBase['path'] ?? '', '/');

        if ($prefix !== '' && str_starts_with($path, $prefix)) {
            $path = substr($path, strlen($prefix));
            if ($path === '') {
                $path = '/';
            }
        }

        return '/' . ltrim($path, '/');
    }

    // ---------------------------------------------
    // Ejecutar router
    // ---------------------------------------------
    public function run(Request $request): void
    {
        $method = $request->method;

        if (!isset($this->routes[$method])) {
            Response::error(405, "Método no permitido: $method");
        }

        $uri = $this->stripBaseUrl($request->uri);

        foreach ($this->routes[$method] as $routeUri => $route) {

            if (!preg_match($route['pattern'], $uri, $matches)) {
                continue;
            }

            // Ejecutar middlewares instanciados
            foreach ($route['middlewares'] as $name) {
                $mwClass = "App\\Middlewares\\" . ucfirst($name) . "Middleware";

                if (!class_exists($mwClass)) {
                    Response::error(500, "Middleware $mwClass no encontrado.");
                }

                $middleware = $this->container->get($mwClass);

                $middleware->handle($request); // <— instancia real
            }

            // Resolver controlador
            [$controllerName, $methodName] = explode('@', $route['action']);
            $class = "\\App\\Controllers\\$controllerName";

            if (!class_exists($class)) {
                Response::error(500, "Controlador $class no encontrado.");
            }

            $controller = $this->container->get($class);

            if (!method_exists($controller, $methodName)) {
                Response::error(500, "Método $methodName no existe en $class.");
            }

            $params = array_filter($matches, fn($k) => !is_int($k), ARRAY_FILTER_USE_KEY);

            call_user_func_array([$controller, $methodName], array_merge([$request], array_values($params)));

            return;
        }

        Response::error(404, "Ruta no encontrada: $uri");
    }
}
