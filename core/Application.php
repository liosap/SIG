<?php
declare(strict_types=1);

namespace Core;

use DI\Container;
use Core\Http\Request;

final class Application
{
    private Container $container;
    private Router $router;

    /**
     * @param array<int, array{0:string,1:string,2:string,3?:array}> $routes
     */
    public function __construct(Container $container, array $routes)
    {
        $this->container = $container;
        $this->router = new Router($container);

        // Registrar rutas desde el array que viene de routes/web.php
        foreach ($routes as $route) {
            [$method, $path, $handler, $middlewares] = array_pad($route, 4, []);

            $methodLower = strtolower($method); // "GET" -> "get", "POST" -> "post"

            if (!method_exists($this->router, $methodLower)) {
                throw new \RuntimeException("Método HTTP no soportado en Router: $method");
            }

            $this->router->{$methodLower}($path, $handler, $middlewares);
        }
    }

    public function run(): void
    {
        $request = new Request();
        $this->router->run($request);
    }
}