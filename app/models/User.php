<?php
/**
 * Modelo User - Gestión de usuarios
 * Sistema de Tickets de Cancelación
 * 
 * @author José Ernesto Ruiz Valdivia
 * @version 1.0
 */

namespace App\Models;

use App\Core\Database;
use App\Helpers\AuthHelper;

class User
{
    private Database $db;
    private string $table = 'usuarios';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Buscar usuario por ID
     * 
     * @param int $id ID del usuario
     * @return array|null
     */
    public function find(int $id): ?array
    {
        $sql = "SELECT id, username, email, nombre_completo, empresa, departamento, 
                       activo, fecha_creacion, ultimo_login
                FROM {$this->table} WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }

    /**
     * Buscar usuario por username
     * 
     * @param string $username Username
     * @return array|null
     */
    public function findByUsername(string $username): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE username = ? AND activo = 1";
        return $this->db->fetchOne($sql, [$username]);
    }

    /**
     * Buscar usuario por email
     * 
     * @param string $email Email
     * @return array|null
     */
    public function findByEmail(string $email): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = ? AND activo = 1";
        return $this->db->fetchOne($sql, [$email]);
    }

    /**
     * Autenticar usuario
     * 
     * @param string $username Username o email
     * @param string $password Contraseña
     * @return array|null Usuario si credenciales válidas
     */
    public function authenticate(string $username, string $password): ?array
    {
        // Buscar por username o email
        $sql = "SELECT * FROM {$this->table} 
                WHERE (username = ? OR email = ?) AND activo = 1";
        
        $user = $this->db->fetchOne($sql, [$username, $username]);
        
        if (!$user) {
            error_log("Auth Debug: Usuario no encontrado o inactivo: {$username}");
            return null;
        }

        // Verificar contraseña
        if (!AuthHelper::verifyPassword($password, $user['password_hash'])) {
            error_log("Auth Debug: Hash mismatch para usuario: {$username}. Input length: " . strlen($password) . ", Stored Hash starts with: " . substr($user['password_hash'], 0, 10));
            return null;
        }

        // Actualizar último login
        $this->updateLastLogin($user['id']);

        // Remover password_hash del resultado
        unset($user['password_hash']);
        
        return $user;
    }

    /**
     * Actualizar último login
     * 
     * @param int $userId ID del usuario
     */
    public function updateLastLogin(int $userId): void
    {
        $sql = "UPDATE {$this->table} SET ultimo_login = CURRENT_TIMESTAMP WHERE id = ?";
        $this->db->query($sql, [$userId]);
    }

    /**
     * Crear nuevo usuario
     * 
     * @param array $data Datos del usuario
     * @return int|null ID del usuario creado
     */
    public function create(array $data): ?int
    {
        $sql = "INSERT INTO {$this->table} 
                (username, email, password_hash, nombre_completo, empresa, departamento, activo)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $passwordHash = AuthHelper::hashPassword($data['password']);
        
        $this->db->query($sql, [
            $data['username'],
            $data['email'],
            $passwordHash,
            $data['nombre_completo'],
            $data['empresa'],
            $data['departamento'] ?? null,
            $data['activo'] ?? 1
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Actualizar usuario
     * 
     * @param int $id ID del usuario
     * @param array $data Datos a actualizar
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $values = [];

        foreach ($data as $key => $value) {
            if ($key === 'password') {
                $fields[] = 'password_hash = ?';
                $values[] = AuthHelper::hashPassword($value);
            } elseif (in_array($key, ['username', 'email', 'nombre_completo', 'empresa', 'departamento', 'activo'])) {
                $fields[] = "{$key} = ?";
                $values[] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $id;
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";
        
        $this->db->query($sql, $values);
        return true;
    }

    /**
     * Eliminar usuario (desactivar)
     * 
     * @param int $id ID del usuario
     * @return bool
     */
    public function delete(int $id): bool
    {
        // Soft delete - solo desactivar
        $sql = "UPDATE {$this->table} SET activo = 0 WHERE id = ?";
        $this->db->query($sql, [$id]);
        return true;
    }

    /**
     * Obtener todos los usuarios
     * 
     * @param array $filters Filtros opcionales
     * @param int $page Página
     * @param int $limit Límite por página
     * @return array
     */
    public function getAll(array $filters = [], int $page = 1, int $limit = ITEMS_PER_PAGE): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['empresa'])) {
            $where[] = 'empresa = ?';
            $params[] = $filters['empresa'];
        }

        if (isset($filters['activo'])) {
            $where[] = 'activo = ?';
            $params[] = $filters['activo'];
        }

        if (!empty($filters['search'])) {
            $where[] = '(username LIKE ? OR email LIKE ? OR nombre_completo LIKE ?)';
            $search = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$search, $search, $search]);
        }

        $whereClause = implode(' AND ', $where);
        $offset = ($page - 1) * $limit;

        // Contar total
        $countSql = "SELECT COUNT(*) FROM {$this->table} WHERE {$whereClause}";
        $total = (int) $this->db->fetchColumn($countSql, $params);

        // Obtener registros
        $sql = "SELECT id, username, email, nombre_completo, empresa, departamento, 
                       activo, fecha_creacion, ultimo_login
                FROM {$this->table} 
                WHERE {$whereClause}
                ORDER BY nombre_completo ASC
                LIMIT {$limit} OFFSET {$offset}";
        
        $users = $this->db->fetchAll($sql, $params);

        return [
            'data' => $users,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }

    /**
     * Obtener roles del usuario
     * 
     * @param int $userId ID del usuario
     * @return array
     */
    public function getRoles(int $userId): array
    {
        $sql = "SELECT r.id, r.nombre, r.descripcion, r.nivel
                FROM roles r
                INNER JOIN usuario_rol ur ON r.id = ur.rol_id
                WHERE ur.usuario_id = ?
                ORDER BY r.nivel DESC";
        
        return $this->db->fetchAll($sql, [$userId]);
    }

    /**
     * Asignar rol al usuario
     * 
     * @param int $userId ID del usuario
     * @param int $roleId ID del rol
     * @param int|null $assignedBy ID de quien asigna
     * @return bool
     */
    public function assignRole(int $userId, int $roleId, ?int $assignedBy = null): bool
    {
        $sql = "INSERT IGNORE INTO usuario_rol (usuario_id, rol_id, asignado_por) VALUES (?, ?, ?)";
        $this->db->query($sql, [$userId, $roleId, $assignedBy]);
        return true;
    }

    /**
     * Remover rol del usuario
     * 
     * @param int $userId ID del usuario
     * @param int $roleId ID del rol
     * @return bool
     */
    public function removeRole(int $userId, int $roleId): bool
    {
        $sql = "DELETE FROM usuario_rol WHERE usuario_id = ? AND rol_id = ?";
        $this->db->query($sql, [$userId, $roleId]);
        return true;
    }

    /**
     * Sincronizar roles del usuario
     * 
     * @param int $userId ID del usuario
     * @param array $roleIds IDs de roles
     * @param int|null $assignedBy ID de quien asigna
     */
    public function syncRoles(int $userId, array $roleIds, ?int $assignedBy = null): void
    {
        // Eliminar roles actuales
        $this->db->query("DELETE FROM usuario_rol WHERE usuario_id = ?", [$userId]);

        // Asignar nuevos roles
        foreach ($roleIds as $roleId) {
            $this->assignRole($userId, $roleId, $assignedBy);
        }
    }

    /**
     * Verificar si username existe
     * 
     * @param string $username Username
     * @param int|null $excludeId ID a excluir (para edición)
     * @return bool
     */
    public function usernameExists(string $username, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE username = ?";
        $params = [$username];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        return (int) $this->db->fetchColumn($sql, $params) > 0;
    }

    /**
     * Verificar si email existe
     * 
     * @param string $email Email
     * @param int|null $excludeId ID a excluir (para edición)
     * @return bool
     */
    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE email = ?";
        $params = [$email];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        return (int) $this->db->fetchColumn($sql, $params) > 0;
    }

    /**
     * Generar token de recuperación de contraseña
     * 
     * @param string $email Email del usuario
     * @return string|null Token generado o null si no existe
     */
    public function generateResetToken(string $email): ?string
    {
        $user = $this->findByEmail($email);
        
        if (!$user) {
            return null;
        }

        $token = AuthHelper::generateResetToken();
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $sql = "UPDATE {$this->table} SET reset_token = ?, reset_expiry = ? WHERE id = ?";
        $this->db->query($sql, [$token, $expiry, $user['id']]);

        return $token;
    }

    /**
     * Validar token de recuperación
     * 
     * @param string $token Token
     * @return array|null Usuario si token válido
     */
    public function validateResetToken(string $token): ?array
    {
        $sql = "SELECT id, email, nombre_completo FROM {$this->table} 
                WHERE reset_token = ? AND reset_expiry > NOW()";
        
        return $this->db->fetchOne($sql, [$token]);
    }

    /**
     * Resetear contraseña con token
     * 
     * @param string $token Token de recuperación
     * @param string $newPassword Nueva contraseña
     * @return bool
     */
    public function resetPassword(string $token, string $newPassword): bool
    {
        $user = $this->validateResetToken($token);
        
        if (!$user) {
            return false;
        }

        $hash = AuthHelper::hashPassword($newPassword);
        
        $sql = "UPDATE {$this->table} 
                SET password_hash = ?, reset_token = NULL, reset_expiry = NULL 
                WHERE id = ?";
        
        $this->db->query($sql, [$hash, $user['id']]);
        return true;
    }
}
