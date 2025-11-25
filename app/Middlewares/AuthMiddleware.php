<?php
declare(strict_types=1);

namespace App\Middlewares;

use Core\Http\Request;
use Core\Http\Response;

final class AuthMiddleware
{
    /**
     * Verifica que el usuario esté autenticado.
     */
    public function handle(Request $request): void
    {
        $session = $request->session;

        if (empty($session['ID_Usuario'])) {

            if ($request->isAjax() || $request->acceptsJson()) {
                Response::json(['error' => 'No autorizado'], 401);
                return;
            }

            // Navegación normal: mensaje y redirect al login
            flash('error', 'Debes iniciar sesión para acceder a esta página.');
            Response::redirect(url('login'));
            return;
        }
    }
}
