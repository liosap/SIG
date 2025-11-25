<?php
declare(strict_types=1);

namespace App\Middlewares;

use Core\Http\Request;
use Core\Http\Response;
use App\Helpers\Csrf;

final class CsrfMiddleware
{
    /**
     * Valida el token CSRF para solicitudes POST.
     */
    public function handle(Request $request): void
    {
        if ($request->method !== 'POST') {
            return;
        }

        $token = $request->post['_csrf']
            ?? $request->header('x-csrf-token');

        if (!Csrf::validate($token)) {

            if ($request->isAjax() || $request->acceptsJson()) {
                Response::json(['error' => 'Token CSRF inválido'], 403);
                return;
            }

            Response::error(403, 'Token CSRF inválido o ausente.');
            return;
        }
    }
}