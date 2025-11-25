<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;
use PDOException;
use RuntimeException;
use InvalidArgumentException;

class UsuarioRepository
{
    private PDO $db;

    /**
     * Constructor del repositorio.
     * 
     * Fuerza PDO a usar modo de errores por excepciones.
     *
     * @param PDO $db
     */
    public function __construct(PDO $db)
    {
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db = $db;
    }

    /* ================================================================
     * BÚSQUEDAS
     * ================================================================ */

    /**
     * Buscar usuario por ID.
     *
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM usuarios WHERE ID_Usuario = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Buscar usuario por Username exacto.
     *
     * @param string $username
     * @return array|null
     */
    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM usuarios WHERE Username = :username LIMIT 1'
        );
        $stmt->execute([':username' => $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Verifica si un usuario existe por username.
     *
     * @param string $username
     * @return bool
     */
    public function exists(string $username): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM usuarios WHERE Username = :username LIMIT 1'
        );
        $stmt->execute([':username' => $username]);
        return (bool)$stmt->fetchColumn();
    }

    /* ================================================================
     * CREACIÓN
     * ================================================================ */

    /**
     * Crear usuario desde registro público.
     *
     * @param array $data ['Username' => string, 'PasswordHash' => string]
     * @return int ID del usuario creado
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function create(array $data): int
    {
        $username = trim($data['Username'] ?? '');
        $passwordHash = $data['PasswordHash'] ?? null;

        if ($username === '' || !$passwordHash) {
            throw new InvalidArgumentException('Username o PasswordHash inválidos.');
        }

        try {
            $stmt = $this->db->prepare(
                'INSERT INTO usuarios (Username, PasswordHash)
                 VALUES (:username, :passwordHash)'
            );
            $stmt->execute([
                ':username' => $username,
                ':passwordHash' => $passwordHash
            ]);

            return (int)$this->db->lastInsertId();

        } catch (PDOException $e) {
            throw new RuntimeException('Error al crear usuario', 0, $e);
        }
    }

    /**
     * Crear usuario desde el panel interno.
     *
     * @param array $data ['Username', 'PasswordHash', 'Activo' => int]
     * @return int
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function createInternal(array $data): int
    {
        $username = trim($data['Username'] ?? '');
        $passwordHash = $data['PasswordHash'] ?? null;
        $activo = $data['Activo'] ?? 1;

        if ($username === '' || !$passwordHash) {
            throw new InvalidArgumentException('Username o PasswordHash inválidos.');
        }

        try {
            $stmt = $this->db->prepare(
                'INSERT INTO usuarios (Username, PasswordHash, Activo)
                 VALUES (:username, :passwordHash, :activo)'
            );
            $stmt->execute([
                ':username' => $username,
                ':passwordHash' => $passwordHash,
                ':activo' => $activo,
            ]);

            return (int)$this->db->lastInsertId();

        } catch (PDOException $e) {
            throw new RuntimeException('Error al crear usuario interno', 0, $e);
        }
    }

    /* ================================================================
     * LISTADO
     * ================================================================ */

    /**
     * Listado completo de usuarios.
     *
     * @return array
     */
    public function findAll(): array
    {
        $stmt = $this->db->query(
            'SELECT ID_Usuario, Username, Activo, FechaRegistro, FechaUltimaAutenticacion
             FROM usuarios ORDER BY ID_Usuario DESC'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ================================================================
     * UPDATE DE DATOS
     * ================================================================ */

    /**
     * Actualizar Username.
     *
     * @param int $id
     * @param string $username
     * @return bool
     *
     * @throws InvalidArgumentException
     */
    public function updateUsername(int $id, string $username): bool
    {
        $username = trim($username);

        if ($username === '') {
            throw new InvalidArgumentException('Username inválido.');
        }

        $stmt = $this->db->prepare(
            'UPDATE usuarios SET Username = :username WHERE ID_Usuario = :id'
        );

        return $stmt->execute([
            ':username' => $username,
            ':id' => $id
        ]);
    }

    /**
     * Actualizar contraseña del usuario.
     *
     * @param int $id
     * @param string $newHash
     * @return bool
     */
    public function updatePassword(int $id, string $newHash): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE usuarios
             SET PasswordHash = :passwordHash,
                 FechaCambioPassword = NOW()
             WHERE ID_Usuario = :id'
        );

        return $stmt->execute([
            ':passwordHash' => $newHash,
            ':id' => $id
        ]);
    }

    /**
     * Activar usuario.
     *
     * @param int $id
     * @return bool
     */
    public function activateUser(int $id): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE usuarios SET Activo = 1 WHERE ID_Usuario = :id'
        );
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Desactivar usuario.
     *
     * @param int $id
     * @return bool
     */
    public function deactivateUser(int $id): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE usuarios SET Activo = 0 WHERE ID_Usuario = :id'
        );
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Actualizar fecha del último login.
     *
     * @param int $id
     * @return bool
     */
    public function updateLastLoginDate(int $id): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE usuarios
             SET FechaUltimaAutenticacion = NOW()
             WHERE ID_Usuario = :id'
        );
        return $stmt->execute([':id' => $id]);
    }

    /* ================================================================
     * SEGURIDAD: INTENTOS Y BLOQUEOS
     * ================================================================ */

    /**
     * Obtener cantidad de intentos fallidos.
     *
     * @param string $username
     * @return int
     */
    public function getFailedAttempts(string $username): int
    {
        $stmt = $this->db->prepare(
            'SELECT IntentosFallidos FROM usuarios WHERE Username = :username'
        );
        $stmt->execute([':username' => $username]);
        return (int)($stmt->fetchColumn() ?: 0);
    }

    /**
     * Obtener fecha/hora hasta la cual está bloqueado el usuario.
     *
     * @param string $username
     * @return string|null
     */
    public function getLockUntil(string $username): ?string
    {
        $stmt = $this->db->prepare(
            'SELECT LockUntil FROM usuarios WHERE Username = :username'
        );
        $stmt->execute([':username' => $username]);

        $value = $stmt->fetchColumn();
        return $value ?: null;
    }

    /**
     * Determina si un usuario está bloqueado actualmente.
     *
     * @param string $username
     * @return bool
     */
    public function isUserLocked(string $username): bool
    {
        $stmt = $this->db->prepare(
            'SELECT LockUntil FROM usuarios WHERE Username = :username LIMIT 1'
        );
        $stmt->execute([':username' => $username]);

        $lockUntil = $stmt->fetchColumn();
        if (!$lockUntil) {
            return false;
        }

        return strtotime($lockUntil) > time();
    }

    /**
     * Bloquear usuario por X minutos.
     *
     * @param string $username
     * @param int $minutes
     * @return bool
     */
    public function lockUserUntil(string $username, int $minutes): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE usuarios
             SET LockUntil = DATE_ADD(NOW(), INTERVAL :m MINUTE)
             WHERE Username = :username'
        );
        return $stmt->execute([
            ':m' => $minutes,
            ':username' => $username
        ]);
    }

    /**
     * Desbloquear un usuario.
     *
     * @param int $id
     * @return bool
     */
    public function unlockUser(int $id): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE usuarios
             SET LockUntil = NULL,
                 IntentosFallidos = 0
             WHERE ID_Usuario = :id'
        );
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Incrementar en +1 los intentos fallidos de login.
     *
     * @param string $username
     * @return bool
     */
    public function increaseFailedAttempts(string $username): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE usuarios
             SET IntentosFallidos = IntentosFallidos + 1
             WHERE Username = :username'
        );
        return $stmt->execute([':username' => $username]);
    }

    /**
     * Resetear intentos fallidos a 0.
     *
     * @param int $id
     * @return bool
     */
    public function resetFailedAttempts(int $id): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE usuarios
             SET IntentosFallidos = 0
             WHERE ID_Usuario = :id'
        );
        return $stmt->execute([':id' => $id]);
    }
}
