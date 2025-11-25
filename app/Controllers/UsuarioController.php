<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\UsuarioService;
use Core\Http\Request;
use Core\Http\Response;

class UsuarioController
{
    private UsuarioService $service;

    public function __construct(UsuarioService $service)
    {
        $this->service = $service;
    }

    /**
     * Dashboard del usuario autenticado.
     */
    public function dashboard(Request $request): void
    {
        $username = $request->session['Username'] ?? null;

        if ($username === null) {
            Response::redirect(url('login'));
        }

        Response::view('usuario/dashboard', [
            'title'    => 'SIG - Escritorio',
            'username' => $username,
        ]);
    }

    /**
     * Listado de usuarios.
     */
    public function index(Request $request): void
    {
        $users = $this->service->all();

        Response::view('usuario/index', [
            'title' => 'SIG - Usuarios',
            'users' => $users,
        ]);
    }

    /**
     * Mostrar un usuario por ID.
     */
    public function show(Request $request, int $id): void
    {
        $user = $this->service->find($id);

        if (!$user) {
            Response::error(404, 'Usuario no encontrado.');
        }

        Response::view('usuario/show', [
            'title' => 'SIG - Usuario Detalles',
            'user'  => $user,
        ]);
    }

    /**
     * Formulario para crear usuario interno.
     */
    public function create(Request $request): void
    {
        Response::view('usuario/create', ['title' => 'SIG - Crear Usuario']);
    }

    /**
     * Guardar nuevo usuario desde el panel interno.
     */
    public function store(Request $request): void
    {
        $username = trim((string)$request->input('username', ''));
        $password = trim((string)$request->input('password', ''));

        try {
            $this->service->createInternal([
                'Username' => $username,
                'Password' => $password,
                'Activo'   => 1,
            ]);

            flash('success', 'Usuario creado correctamente.');

            Response::redirect(url('usuarios'));
        } catch (\Throwable $e) {
            Response::view('usuario/create', [
                'title'    => 'SIG - Crear Usuario',
                'error'    => 'No se pudo crear el usuario: ' . $e->getMessage(),
                'username' => $username,
            ]);
        }
    }

    /**
     * Formulario de edición.
     */
    public function edit(Request $request, int $id): void
    {
        $user = $this->service->find($id);

        if (!$user) {
            Response::error(404, 'Usuario no encontrado');
        }

        Response::view('usuario/edit', [
            'title' => 'SIG - Editar Usuario',
            'user'  => $user,
        ]);
    }

    /**
     * Guardar cambios del usuario.
     */
    public function update(Request $request, int $id): void
    {
        $username = trim((string)$request->input('username', ''));

        if ($this->service->find($id) === null) {
            Response::error(404, 'Usuario no encontrado');
        }

        try {
            $this->service->update($id, ['Username' => $username]);

            flash('success', 'Usuario actualizado correctamente.');

            Response::redirect(url("usuarios/$id"));
        } catch (\Throwable $e) {
            Response::view('usuario/edit', [
                'title' => 'SIG - Editar Usuario',
                'error' => 'Error al actualizar: ' . $e->getMessage(),
                'user'  => ['ID_Usuario' => $id, 'Username' => $username],
            ]);
        }
    }

    /**
     * Formulario para cambiar contraseña.
     */
    public function changePasswordForm(Request $request, int $id): void
    {
        $user = $this->service->find($id);

        if (!$user) {
            Response::error(404, 'Usuario no encontrado');
        }

        Response::view('usuario/change_password', [
            'title' => 'SIG - Cambiar Contraseña',
            'user'  => $user,
        ]);
    }

    /**
     * Guardar nueva contraseña.
     */
    public function changePassword(Request $request, int $id): void
    {
        $pass1 = trim((string)$request->input('password', ''));
        $pass2 = trim((string)$request->input('password2', ''));

        $user = $this->service->find($id);

        if (!$user) {
            Response::error(404, 'Usuario no encontrado');
        }

        if ($pass1 === '' || $pass2 === '') {
            Response::view('usuario/change_password', [
                'title' => 'SIG - Cambiar Contraseña',
                'error' => 'La contraseña no puede estar vacía.',
                'user'  => $user,
            ]);
            return;
        }

        if ($pass1 !== $pass2) {
            Response::view('usuario/change_password', [
                'title' => 'SIG - Cambiar Contraseña',
                'error' => 'Las contraseñas no coinciden.',
                'user'  => $user,
            ]);
            return;
        }

        try {
            $this->service->changePassword($id, $pass1);

            flash('success', 'Contraseña actualizada correctamente.');

            Response::redirect(url("usuarios/$id"));
        } catch (\Throwable $e) {
            Response::view('usuario/change_password', [
                'title' => 'SIG - Cambiar Contraseña',
                'error' => 'No se pudo actualizar la contraseña: ' . $e->getMessage(),
                'user'  => $user,
            ]);
        }
    }

    /**
     * Desactivar usuario (Activo = 0).
     */
    public function deactivate(Request $request, int $id): void
    {
        if (!$this->service->find($id)) {
            Response::error(404, 'Usuario no encontrado');
        }

        $ok = $this->service->deactivate($id);

        if ($ok) {
            flash('success', 'Usuario desactivado.');
        } else {
            flash('error', 'No se pudo desactivar el usuario.');
        }

        Response::redirect(url("usuarios/$id"));
    }

    /**
     * Activar usuario (Activo = 1).
     */
    public function activate(Request $request, int $id): void
    {
        if (!$this->service->find($id)) {
            Response::error(404, 'Usuario no encontrado');
        }

        $ok = $this->service->activate($id);

        if ($ok) {
            flash('success', 'Usuario activado.');
        } else {
            flash('error', 'No se pudo activar el usuario.');
        }

        Response::redirect(url("usuarios/$id"));
    }
}