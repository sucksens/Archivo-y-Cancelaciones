<?php
/**
 * Modelo Role - Gestión de roles
 * Sistema de Tickets de Cancelación
 * 
 * @author José Ernesto Ruiz Valdivia
 * @version 1.0
 */

namespace App\Models;

use App\Core\Database;

class Role
{
    private Database $db;
    private string $table = 'roles';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Buscar rol por ID
     * 
     * @param int $id ID del rol
     * @return array|null
     */
    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }

    /**
     * Buscar rol por nombre
     * 
     * @param string $nombre Nombre del rol
     * @return array|null
     */
    public function findByName(string $nombre): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE nombre = ?";
        return $this->db->fetchOne($sql, [$nombre]);
    }

    /**
     * Crear nuevo rol
     * 
     * @param array $data Datos del rol
     * @return int|null ID del rol creado
     */
    public function create(array $data): ?int
    {
        $sql = "INSERT INTO {$this->table} (nombre, descripcion, nivel) VALUES (?, ?, ?)";
        
        $this->db->query($sql, [
            $data['nombre'],
            $data['descripcion'] ?? null,
            $data['nivel'] ?? 0
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Actualizar rol
     * 
     * @param int $id ID del rol
     * @param array $data Datos a actualizar
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $values = [];

        foreach (['nombre', 'descripcion', 'nivel'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = ?";
                $values[] = $data[$field];
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
     * Eliminar rol
     * 
     * @param int $id ID del rol
     * @return bool
     */
    public function delete(int $id): bool
    {
        // No permitir eliminar roles del sistema (nivel >= 100)
        $role = $this->find($id);
        if ($role && $role['nivel'] >= 100) {
            return false;
        }

        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $this->db->query($sql, [$id]);
        return true;
    }

    /**
     * Obtener todos los roles
     * 
     * @return array
     */
    public function getAll(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY nivel DESC, nombre ASC";
        return $this->db->fetchAll($sql);
    }

    /**
     * Obtener permisos de un rol
     * 
     * @param int $roleId ID del rol
     * @return array
     */
    public function getPermissions(int $roleId): array
    {
        $sql = "SELECT p.*
                FROM permisos p
                INNER JOIN rol_permiso rp ON p.id = rp.permiso_id
                WHERE rp.rol_id = ?
                ORDER BY p.modulo, p.nombre";
        
        return $this->db->fetchAll($sql, [$roleId]);
    }

    /**
     * Obtener IDs de permisos de un rol
     * 
     * @param int $roleId ID del rol
     * @return array
     */
    public function getPermissionIds(int $roleId): array
    {
        $sql = "SELECT permiso_id FROM rol_permiso WHERE rol_id = ?";
        $result = $this->db->fetchAll($sql, [$roleId]);
        return array_column($result, 'permiso_id');
    }

    /**
     * Asignar permiso a rol
     * 
     * @param int $roleId ID del rol
     * @param int $permissionId ID del permiso
     * @param int|null $grantedBy ID de quien otorga
     * @return bool
     */
    public function assignPermission(int $roleId, int $permissionId, ?int $grantedBy = null): bool
    {
        $sql = "INSERT IGNORE INTO rol_permiso (rol_id, permiso_id, concedido_por) VALUES (?, ?, ?)";
        $this->db->query($sql, [$roleId, $permissionId, $grantedBy]);
        return true;
    }

    /**
     * Remover permiso de rol
     * 
     * @param int $roleId ID del rol
     * @param int $permissionId ID del permiso
     * @return bool
     */
    public function removePermission(int $roleId, int $permissionId): bool
    {
        $sql = "DELETE FROM rol_permiso WHERE rol_id = ? AND permiso_id = ?";
        $this->db->query($sql, [$roleId, $permissionId]);
        return true;
    }

    /**
     * Sincronizar permisos de un rol
     * 
     * @param int $roleId ID del rol
     * @param array $permissionIds IDs de permisos
     * @param int|null $grantedBy ID de quien otorga
     */
    public function syncPermissions(int $roleId, array $permissionIds, ?int $grantedBy = null): void
    {
        // Eliminar permisos actuales
        $this->db->query("DELETE FROM rol_permiso WHERE rol_id = ?", [$roleId]);

        // Asignar nuevos
        foreach ($permissionIds as $permissionId) {
            $this->assignPermission($roleId, $permissionId, $grantedBy);
        }
    }

    /**
     * Obtener usuarios con este rol
     * 
     * @param int $roleId ID del rol
     * @return array
     */
    public function getUsers(int $roleId): array
    {
        $sql = "SELECT u.id, u.username, u.nombre_completo, u.email
                FROM usuarios u
                INNER JOIN usuario_rol ur ON u.id = ur.usuario_id
                WHERE ur.rol_id = ? AND u.activo = 1
                ORDER BY u.nombre_completo";
        
        return $this->db->fetchAll($sql, [$roleId]);
    }

    /**
     * Contar usuarios con este rol
     * 
     * @param int $roleId ID del rol
     * @return int
     */
    public function countUsers(int $roleId): int
    {
        $sql = "SELECT COUNT(*) FROM usuario_rol ur
                INNER JOIN usuarios u ON ur.usuario_id = u.id
                WHERE ur.rol_id = ? AND u.activo = 1";
        return (int) $this->db->fetchColumn($sql, [$roleId]);
    }
}
