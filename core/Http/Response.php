<?php

declare(strict_types=1);

namespace Core\Http;

use App\View\View;

class Response
{
    /**
     * Enviar respuesta JSON.
     */
    public static function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        exit;
    }

    /**
     * Redirección HTTP segura (previene open redirects).
     */
    public static function redirect(string $url, int $status = 302): void
    {
        http_response_code($status);

        $url = trim((string)$url);

        // Si la URL es absoluta (http:// o https://) y apunta internamente a APP_URL, dejamos.
        if (preg_match('#^https?://#i', $url)) {
            $base = rtrim($_ENV['APP_URL'] ?? '', '/');
            if ($base !== '' && str_starts_with($url, $base)) {
                header('Location: ' . $url);
                exit;
            }
            // Si es absoluta externa → no permitimos (open redirect)
            header('Location: /');
            exit;
        }


        // Ahora url no es absoluta; convertir en absoluta con APP_URL si existe
        $base = rtrim($_ENV['APP_URL'] ?? '', '/');

        // Normalizar: si viene '/dashboard' o 'dashboard'
        if (!str_starts_with($url, '/')) {
            $url = '/' . ltrim($url, '/');
        }

        if ($base !== '') {
            $target = $base . $url;
        } else {
            $target = $url;
        }

        header('Location: ' . $target);
        exit;
        
    }


    /**
     * Renderizar una vista con el motor de templates seguro.
     */
    public static function view(string $template, array $data = []): void
    {
        echo View::render($template, $data);
        exit;
    }

    /**
     * Manejo de errores HTTP con fallback seguro.
     */
    public static function error(int $status, string $message = ''): void
    {
        http_response_code($status);

        $viewPath = "errors/{$status}";
        $fullPath = BASE_PATH . "/app/Views/{$viewPath}.php";

        $data = [
            'code'    => $status,
            'message' => $message,
            'title'   => "Error {$status}",
        ];

        if (is_file($fullPath)) {
            echo View::render($viewPath, $data);
            exit;
        }

        // Fallback seguro
        $safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        echo "<h1>Error {$status}</h1><p>{$safeMessage}</p>";
        exit;
    }
}
