<?php
declare(strict_types=1);

namespace Core\Http;

final class Request
{
    public string $method;
    public string $uri;
    public array $get;
    public array $post;
    public array $files;
    public array $cookies;
    public array $headers;
    public array $server;
    /** @var array<string,mixed> & referencia directa a $_SESSION si existe */
    public array $session;

    public function __construct()
    {
        $this->method  = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->server  = $_SERVER ?? [];
        $this->get     = $_GET ?? [];
        $this->post    = $_POST ?? [];
        $this->files   = $_FILES ?? [];
        $this->cookies = $_COOKIE ?? [];

        // No arrancamos session_start() aquí: lo hace public/index.php.
        // Si la sesión está activa, vinculamos por referencia para lectura/escritura.
        if (session_status() === PHP_SESSION_ACTIVE) {
            // Por escritura/lectura directa usar $_SESSION
            $this->session = &$_SESSION;
        } else {
            // Si no hay sesión activa, exponemos array vacío (no iniciamos sesión aquí).
            $this->session = [];
        }

        // recolectar headers limpiando CRLF para evitar header injection
        $this->headers = $this->collectHeaders();

        // construir URI limpia (sin querystring)
        $this->uri = $this->resolveUri();
    }

    /**
     * Devuelve todos los datos (POST + GET), POST sobrescribe GET si existe la llave.
     *
     * @return array<string,mixed>
     */
    public function all(): array
    {
        return array_merge($this->get, $this->post);
    }

    /**
     * Obtener un input (post o get) con valor por defecto.
     */
    public function input(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $this->post)) {
            return $this->post[$key];
        }
        if (array_key_exists($key, $this->get)) {
            return $this->get[$key];
        }
        return $default;
    }

    public function header(string $name, mixed $default = null): mixed
    {
        $name = strtolower($name);
        return $this->headers[$name] ?? $default;
    }

    public function cookie(string $name, mixed $default = null): mixed
    {
        return $this->cookies[$name] ?? $default;
    }

    public function file(string $key): array|null
    {
        return $this->files[$key] ?? null;
    }

    /** Devuelve true si parece ser una petición AJAX (X-Requested-With) */
    public function isAjax(): bool
    {
        return isset($this->headers['x-requested-with'])
            && strtolower($this->headers['x-requested-with']) === 'xmlhttprequest';
    }

    /** Devuelve true si acepta JSON (header Accept) */
    public function acceptsJson(): bool
    {
        return isset($this->headers['accept'])
            && str_contains(strtolower($this->headers['accept']), 'application/json');
    }

    /**
     * Detecta IP cliente respetando proxy inverso cuando está configurado.
     * Para usar proxys confiables, define TRUSTED_PROXIES en .env con CSV de IPs.
     */
    public function getClientIp(): string
    {
        // Trust proxies configuradas en env como CSV: "10.0.0.1,10.0.0.2"
        $trusted = array_filter(array_map('trim', explode(',', ($_ENV['TRUSTED_PROXIES'] ?? ''))));

        // Si la petición contiene X-Forwarded-For y venimos desde proxy confiable:
        $remote = $this->server['REMOTE_ADDR'] ?? '';

        $xff = $this->headers['x-forwarded-for'] ?? null;
        if ($xff && in_array($remote, $trusted, true)) {
            // X-Forwarded-For puede contener varias IPs separadas por coma, la última es la más cercana al cliente.
            $parts = array_map('trim', explode(',', $xff));
            return $parts[0] ?? $remote;
        }

        // Fallback
        return $remote;
    }

    /* -------------------------
     * Helpers internos
     * ------------------------- */

    /**
     * Limpiar y normalizar headers desde $_SERVER.
     *
     * @return array<string,string>
     */
    protected function collectHeaders(): array
    {
        $headers = [];

        foreach ($this->server as $key => $value) {
            if (!is_string($value)) {
                continue;
            }
            if (str_starts_with($key, 'HTTP_')) {
                $name = strtolower(str_replace('_', '-', substr($key, 5)));
                // Eliminar CRLF para prevenir header injection
                $safe = str_replace(["\r", "\n"], '', $value);
                $headers[$name] = $safe;
            }

            // Common non-HTTP_ headers
            if ($key === 'CONTENT_TYPE') {
                $headers['content-type'] = str_replace(["\r", "\n"], '', $value);
            }
            if ($key === 'CONTENT_LENGTH') {
                $headers['content-length'] = str_replace(["\r", "\n"], '', $value);
            }
        }

        return $headers;
    }

    /**
     * Resuelve la URI sin querystring y normaliza la barra final.
     */
    protected function resolveUri(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        // eliminar querystring
        $uri = explode('?', $uri, 2)[0];

        // Normalizar múltiples slashes
        $uri = preg_replace('#/+#', '/', $uri) ?: '/';

        // Evitar trailing slash inconsistente: mantener '/' solo si es la raíz
        if ($uri !== '/' && str_ends_with($uri, '/')) {
            $uri = rtrim($uri, '/');
        }

        return $uri;
    }
}
