<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\UsuarioRepository;
use InvalidArgumentException;
use RuntimeException;
use Psr\Log\LoggerInterface;

class UsuarioService
{
    private UsuarioRepository $repo;
    private LoggerInterface $logger;

    /** Máximo de intentos fallidos antes de bloqueo */
    private const MAX_FAILED_ATTEMPTS = 5;

    /** Minutos de bloqueo cuando se supera el límite */
    private const LOCK_MINUTES = 10;

    /**
     * Constructor del servicio de usuarios.
     *
     * @param UsuarioRepository $repo
     * @param LoggerInterface   $logger
     */
    public function __construct(UsuarioRepository $repo, LoggerInterface $logger)
    {
        $this->repo   = $repo;
        $this->logger = $logger;
    }

    /* =====================================================================
     * CONSULTAS Y CRUD
     * ===================================================================== */

    /**
     * Obtener un usuario por ID.
     *
     * @param int $id
     * @return array|null
     */
    public function find(int $id): ?array
    {
        return $this->repo->findById($id);
    }

    /**
     * Listar todos los usuarios.
     *
     * @return array<int,array<string,mixed>>
     */
    public function all(): array
    {
        return $this->repo->findAll();
    }

    /**
     * Registrar usuario público (formulario externo).
     *
     * @param array{Username:string, Password:string} $payload
     * @return int  ID del usuario creado
     *
     * @throws InvalidArgumentException
     */
    public function register(array $payload): int
    {
        $username = trim($payload['Username'] ?? '');
        $password = (string)($payload['Password'] ?? '');

        if ($username === '' || $password === '') {
            throw new InvalidArgumentException('Username y password son requeridos.');
        }

        if (strlen($username) < 3) {
            throw new InvalidArgumentException('El username debe tener al menos 3 caracteres.');
        }

        if ($this->repo->exists($username)) {
            throw new InvalidArgumentException('El nombre de usuario ya existe.');
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        return $this->repo->create([
            'Username'     => $username,
            'PasswordHash' => $hash,
        ]);
    }

    /**
     * Crear usuario desde el panel interno.
     *
     * @param array{Username:string, Password:string, Activo?:int} $payload
     * @return int
     *
     * @throws InvalidArgumentException
     */
    public function createInternal(array $payload): int
    {
        $username = trim($payload['Username'] ?? '');
        $password = (string)($payload['Password'] ?? '');
        $active   = (int)($payload['Activo'] ?? 1);

        if ($username === '' || $password === '') {
            throw new InvalidArgumentException('Username y password son requeridos.');
        }

        if (strlen($username) < 3) {
            throw new InvalidArgumentException('El username debe tener al menos 3 caracteres.');
        }

        if ($this->repo->exists($username)) {
            throw new InvalidArgumentException('El nombre de usuario ya existe.');
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        return $this->repo->createInternal([
            'Username'     => $username,
            'PasswordHash' => $hash,
            'Activo'       => $active,
        ]);
    }

    /**
     * Actualizar nombre de usuario.
     *
     * @param int   $id
     * @param array{Username:string} $payload
     * @return bool
     *
     * @throws InvalidArgumentException
     */
    public function update(int $id, array $payload): bool
    {
        $username = trim($payload['Username'] ?? '');

        if ($username === '') {
            throw new InvalidArgumentException('El nombre de usuario no puede estar vacío.');
        }

        if (strlen($username) < 3) {
            throw new InvalidArgumentException('El username debe tener al menos 3 caracteres.');
        }

        $existing = $this->repo->findByUsername($username);

        if ($existing && (int)$existing['ID_Usuario'] !== $id) {
            throw new InvalidArgumentException('El nombre de usuario ya está en uso.');
        }

        return $this->repo->updateUsername($id, $username);
    }

    /**
     * Cambiar contraseña del usuario.
     *
     * @param int    $id
     * @param string $newPassword
     * @return bool
     *
     * @throws InvalidArgumentException
     */
    public function changePassword(int $id, string $newPassword): bool
    {
        if (strlen($newPassword) < 6) {
            throw new InvalidArgumentException('La contraseña debe tener al menos 6 caracteres.');
        }

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);

        return $this->repo->updatePassword($id, $hash);
    }

    /* =====================================================================
     * AUTENTICACIÓN + PROTECCIÓN ANTI-FUERZA-BRUTA
     * ===================================================================== */

    /**
     * Autenticar un usuario aplicando límites de intentos fallidos y bloqueo temporal.
     *
     * @param string $username
     * @param string $password
     * @return array|null  Devuelve datos del usuario en caso de éxito o null si falla
     *
     * @throws RuntimeException
     */
    public function authenticate(string $username, string $password): ?array
    {
        $username = trim($username);

        /** Buscar usuario */
        $user = $this->repo->findByUsername($username);

        if (!$user) {
            $this->logger->warning("Intento de login con usuario inexistente: {$username}");
            return null;
        }

        /** Si está inactivo */
        if ((int)$user['Activo'] !== 1) {
            $this->logger->info("Intento de login para usuario inactivo: {$username}");
            return null;
        }

        /** Si está bloqueado */
        if ($this->repo->isUserLocked($username)) {
            $this->logger->notice("Usuario bloqueado intentando ingresar: {$username}");
            return null;
        }

        /** Validar password */
        if (!password_verify($password, $user['PasswordHash'])) {

            $this->repo->increaseFailedAttempts($username);
            $failed = $this->repo->getFailedAttempts($username);

            $this->logger->warning(
                "Password incorrecto para {$username} (Intentos: {$failed})"
            );

            /** Si excedió límite, bloquear */
            if ($failed >= self::MAX_FAILED_ATTEMPTS) {
                $this->repo->lockUserUntil($username, self::LOCK_MINUTES);
                $this->logger->alert(
                    "Usuario {$username} bloqueado por fuerza bruta durante " .
                    self::LOCK_MINUTES . " minutos."
                );
            }

            return null;
        }

        /** Login correcto → resetear fallidos y desbloquear */
        $this->repo->resetFailedAttempts((int)$user['ID_Usuario']);
        $this->repo->unlockUser((int)$user['ID_Usuario']);
        $this->repo->updateLastLoginDate((int)$user['ID_Usuario']);

        unset($user['PasswordHash']);

        $this->logger->info("Login exitoso: {$username}");

        return $user;
    }

    /* =====================================================================
     * ACTIVAR / DESACTIVAR
     * ===================================================================== */

    /**
     * Desactivar usuario (borrado lógico).
     *
     * @param int $id
     * @return bool
     */
    public function deactivate(int $id): bool
    {
        return $this->repo->deactivateUser($id);
    }

    /**
     * Activar usuario.
     *
     * @param int $id
     * @return bool
     */
    public function activate(int $id): bool
    {
        return $this->repo->activateUser($id);
    }
}
