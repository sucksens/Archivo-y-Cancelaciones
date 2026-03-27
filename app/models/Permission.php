<?php
/**
 * Modelo Permission - Gestión de permisos
 * Sistema de Tickets de Cancelación
 * 
 * @author Sistema
 * @version 1.0
 */

namespace App\Models;

use App\Core\Database;

class Permission
{
    private Database $db;
    private string $table = 'permisos';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Buscar permiso por ID
     * 
     * @param int $id ID del permiso
     * @return array|null
     */
    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }

    /**
     * Buscar permiso por código
     * 
     * @param string $codigo Código del permiso
     * @return array|null
     */
    public function findByCode(string $codigo): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE codigo = ?";
        return $this->db->fetchOne($sql, [$codigo]);
    }

    /**
     * Obtener todos los permisos
     * 
     * @return array
     */
    public function getAll(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY modulo, nombre";
        return $this->db->fetchAll($sql);
    }

    /**
     * Obtener permisos agrupados por módulo
     * 
     * @return array
     */
    public function getAllGrouped(): array
    {
        $permissions = $this->getAll();
        
        $grouped = [];
        foreach ($permissions as $permission) {
            $grouped[$permission['modulo']][] = $permission;
        }
        
        return $grouped;
    }

    /**
     * Obtener permisos por módulo
     * 
     * @param string $modulo Nombre del módulo
     * @return array
     */
    public function getByModule(string $modulo): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE modulo = ? ORDER BY nombre";
        return $this->db->fetchAll($sql, [$modulo]);
    }

    /**
     * Crear nuevo permiso
     * 
     * @param array $data Datos del permiso
     * @return int|null ID del permiso creado
     */
    public function create(array $data): ?int
    {
        $sql = "INSERT INTO {$this->table} (nombre, codigo, descripcion, modulo) VALUES (?, ?, ?, ?)";
        
        $this->db->query($sql, [
            $data['nombre'],
            $data['codigo'],
            $data['descripcion'] ?? null,
            $data['modulo']
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Actualizar permiso
     * 
     * @param int $id ID del permiso
     * @param array $data Datos a actualizar
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $values = [];

        foreach (['nombre', 'codigo', 'descripcion', 'modulo'] as $field) {
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
     * Eliminar permiso
     * 
     * @param int $id ID del permiso
     * @return bool
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $this->db->query($sql, [$id]);
        return true;
    }

    /**
     * Obtener módulos disponibles
     * 
     * @return array
     */
    public function getModules(): array
    {
        $sql = "SELECT DISTINCT modulo FROM {$this->table} ORDER BY modulo";
        $result = $this->db->fetchAll($sql);
        return array_column($result, 'modulo');
    }

    /**
     * Verificar si código existe
     * 
     * @param string $codigo Código
     * @param int|null $excludeId ID a excluir
     * @return bool
     */
    public function codeExists(string $codigo, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE codigo = ?";
        $params = [$codigo];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        return (int) $this->db->fetchColumn($sql, $params) > 0;
    }
}
