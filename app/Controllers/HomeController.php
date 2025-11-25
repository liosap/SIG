<?php
declare(strict_types=1);

namespace App\Controllers;

use Core\Http\Request;
use Core\Http\Response;

class HomeController
{
    public function index(Request $request): void
    {
        // Sesión del Request (sólo lectura en Fase 1)
        $session = $request->session;

        // Si está logueado, redirigir al dashboard
        if (!empty($session['ID_Usuario'])) {
            Response::redirect(url('dashboard'));
        }

        // Página inicial pública
        Response::view('home/index', ['title' => 'SIG - Soporte Técnico']);
    }
}
