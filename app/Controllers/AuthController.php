<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Csrf;
use App\Services\UsuarioService;
use Core\Http\Request;
use Core\Http\Response;
use Psr\Log\LoggerInterface;
use App\Validation\Validator;
use App\Validation\ValidationException;

/**
 * Controlador encargado de autenticación de usuarios.
 */
class AuthController
{
    /** @var UsuarioService Servicio de usuarios */
    private UsuarioService $service;

    /** @var LoggerInterface|null Logger opcional para auditoría */
    private ?LoggerInterface $logger;

    /**
     * Constructor.
     *
     * @param UsuarioService      $service
     * @param LoggerInterface|null $logger  Logger opcional
     */
    public function __construct(UsuarioService $service, ?LoggerInterface $logger = null)
    {
        $this->service = $service;
        $this->logger  = $logger;
    }

    /**
     * Mostrar formulario de login.
     *
     * @param Request $request
     * @return void
     */
    public function showLogin(Request $request): void
    {
        Response::view('auth/login', ['title' => 'SIG - Iniciar Sesión']);
    }

    /**
     * Procesar login:
     * - Valida campos requeridos
     * - Autentica mediante UsuarioService
     * - Evita session fixation
     * - Regenera token CSRF
     * - Registra logs si corresponde
     *
     * @param Request $request
     * @return void
     */
    public function login(Request $request): void
    {
        // Leer y normalizar datos de entrada
        $data = [
            'username' => trim((string)$request->input('username', '')),
            'password' => (string)$request->input('password', ''),
        ];

        // Validación
        try {
            Validator::make($data, [
                'username' => 'required|min:3|alpha_num',
                'password' => 'required|min:6',
            ]);
        } catch (ValidationException $e) {
            // Aplanar mensajes de error en un string sencillo
            $messages = [];
            foreach ($e->errors() as $fieldErrors) {
                $messages[] = implode(' ', $fieldErrors);
            }

            Response::view('auth/login', [
                'title' => 'SIG - Iniciar Sesión',
                'error' => implode(' ', $messages),
            ]);
            return;
        }

        // Intento de autenticación
        $user = $this->service->authenticate($data['username'], $data['password']);

        // Error: usuario inexistente, inactivo, bloqueado o password incorrecta
        if ($user === null) {

            if ($this->logger) {
                $this->logger->warning("Login fallido para usuario: {$data['username']}");
            }

            Response::view('auth/login', [
                'title' => 'SIG - Iniciar Sesión',
                'error' => 'Usuario o contraseña incorrecta.',
            ]);
            return;
        }

        // Login exitoso
        if ($this->logger) {
            $this->logger->info("Login exitoso de {$user['Username']}");
        }

        session_regenerate_id(true);
        Csrf::regenerate();

        $_SESSION['ID_Usuario'] = $user['ID_Usuario'];
        $_SESSION['Username']   = $user['Username'];

        Response::redirect(url('dashboard'));
    }

    /**
     * Cerrar sesión de forma segura:
     * - Limpia variables de sesión
     * - Elimina cookie de sesión
     * - Destruye sesión actual
     * - Regenera nueva sesión y token CSRF
     *
     * @param Request $request
     * @return void
     */
    public function logout(Request $request): void
    {
        if ($this->logger) {
            $this->logger->info("Logout del usuario: " . ($_SESSION['Username'] ?? 'desconocido'));
        }

        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        session_start();
        session_regenerate_id(true);

        Csrf::regenerate();

        Response::redirect(url('login'));
    }

    /**
     * Mostrar formulario de registro.
     *
     * @param Request $request
     * @return void
     */
    public function showRegister(Request $request): void
    {
        Response::view('auth/register', ['title' => 'SIG - Registrar Usuario']);
    }

    /**
     * Registrar un nuevo usuario (público):
     * - Valida inputs
     * - Usa UsuarioService::register()
     * - Registra logs
     *
     * @param Request $request
     * @return void
     */
    public function register(Request $request): void
    {
        $username = trim((string)($request->post['username'] ?? ''));
        $password = (string)($request->post['password'] ?? '');

        if ($username === '' || $password === '') {
            Response::view('auth/register', [
                'title' => 'SIG - Registrar Usuario',
                'error' => 'Debes completar usuario y contraseña.'
            ]);
        }

        try {
            $this->service->register([
                'Username'     => $username,
                'Password' => $password,
            ]);

            if ($this->logger) {
                $this->logger->info("Usuario registrado: {$username}");
            }

            Response::redirect(url('login?registered=1'));

        } catch (\Throwable $e) {

            if ($this->logger) {
                $this->logger->error("Error al registrar usuario {$username}: {$e->getMessage()}");
            }

            Response::view('auth/register', [
                'title' => 'SIG - Registrar Usuario',
                'error' => $e->getMessage()
            ]);
        }
    }
}
