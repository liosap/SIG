<?php
declare(strict_types=1);

namespace App\View;

class View
{
    /**
     * Renderiza una plantilla dentro del layout principal.
     */
    public static function render(string $template, array $data = [], $layout = 'main'): string
    {
        // Evitar directory traversal
        if (str_contains($template, '..')) {
            http_response_code(400);
            return "<h1>Error 400</h1><p>Ruta de vista inválida.</p>";
        }

        // Nombre de vista sólo con caracteres permitidos
        if (!preg_match('#^[A-Za-z0-9/_-]+$#', $template)) {
            http_response_code(400);
            return "<h1>Error 400</h1><p>Nombre de vista no permitido.</p>";
        }

        $templatePath = BASE_PATH . '/app/Views/' . $template. '.php';
        $layoutPath   = BASE_PATH . '/app/Views/layouts/' . $layout . '.php';

        if (!is_file($templatePath)) {
            http_response_code(500);
            return "<h1>Error 500</h1><p>La vista <strong>{$template}</strong> no existe.</p>";
        }

        extract($data, EXTR_SKIP);

        // 1. Renderizar la vista y capturar su contenido
        ob_start();
        include $templatePath;
        $content = ob_get_clean();

        // 2. Cargar el layout con $content disponible
        ob_start();
        include $layoutPath;
        return ob_get_clean();

    }

}
