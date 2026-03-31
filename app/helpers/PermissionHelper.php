<?php
/**
 * PermissionHelper - Verificación de permisos
 * Sistema de Tickets de Cancelación
 * 
 * @author José Ernesto Ruiz Valdivia
 * @version 1.0
 */

namespace App\Helpers;

use App\Core\Database;

class PermissionHelper
{
    private static ?array $cachedPermissions = null;

    /**
     * Cargar permisos del usuario en sesión
     * 
     * @param int $userId ID del usuario
     * @return array Permisos del usuario
     */
    public static function loadUserPermissions(int $userId): array
    {
        $db = Database::getInstance();
        
        $sql = "SELECT DISTINCT p.codigo 
                FROM permisos p
                INNER JOIN rol_permiso rp ON p.id = rp.permiso_id
                INNER JOIN usuario_rol ur ON rp.rol_id = ur.rol_id
                WHERE ur.usuario_id = ?";
        
        $permissions = $db->fetchAll($sql, [$userId]);
        
        $codes = array_column($permissions, 'codigo');
        
        // Guardar en sesión para cache
        $_SESSION['permissions'] = $codes;
        self::$cachedPermissions = $codes;
        
        return $codes;
    }

    /**
     * Verificar si el usuario tiene un permiso
     * 
     * @param string $permission Código del permiso
     * @param int|null $userId ID del usuario (opcional, usa sesión)
     * @return bool
     */
    public static function hasPermission(string $permission, ?int $userId = null): bool
    {
        // Obtener permisos de cache o sesión
        $permissions = self::getPermissions($userId);
        
        // Admin tiene todos los permisos
        if (in_array('admin.all', $permissions)) {
            return true;
        }
        
        return in_array($permission, $permissions);
    }

    /**
     * Verificar si tiene alguno de varios permisos
     * 
     * @param array $permissions Lista de permisos
     * @return bool
     */
    public static function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (self::hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Verificar si tiene todos los permisos
     * 
     * @param array $permissions Lista de permisos
     * @return bool
     */
    public static function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!self::hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Obtener permisos del usuario
     * 
     * @param int|null $userId ID del usuario
     * @return array
     */
    private static function getPermissions(?int $userId = null): array
    {
        // Si hay cache en memoria estática
        if (self::$cachedPermissions !== null && $userId === null) {
            return self::$cachedPermissions;
        }
        
        // Obtener de sesión
        if ($userId === null && isset($_SESSION['permissions'])) {
            self::$cachedPermissions = $_SESSION['permissions'];
            return self::$cachedPermissions;
        }
        
        // Cargar de base de datos si hay userId
        if ($userId !== null) {
            return self::loadUserPermissions($userId);
        }
        
        return [];
    }

    /**
     * Obtener roles del usuario
     * 
     * @param int $userId ID del usuario
     * @return array
     */
    public static function getUserRoles(int $userId): array
    {
        $db = Database::getInstance();
        
        $sql = "SELECT r.id, r.nombre, r.descripcion, r.nivel
                FROM roles r
                INNER JOIN usuario_rol ur ON r.id = ur.rol_id
                WHERE ur.usuario_id = ?
                ORDER BY r.nivel DESC";
        
        return $db->fetchAll($sql, [$userId]);
    }

    /**
     * Verificar si el usuario tiene un rol específico
     * 
     * @param string $roleName Nombre del rol
     * @param int|null $userId ID del usuario
     * @return bool
     */
    public static function hasRole(string $roleName, ?int $userId = null): bool
    {
        if ($userId === null) {
            $userId = $_SESSION['user_id'] ?? null;
        }
        
        if (!$userId) {
            return false;
        }
        
        $roles = self::getUserRoles($userId);
        
        foreach ($roles as $role) {
            if (strtolower($role['nombre']) === strtolower($roleName)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Verificar si es administrador
     * 
     * @return bool
     */
    public static function isAdmin(): bool
    {
        return self::hasRole('Administrador') || self::hasPermission('admin.all');
    }

    /**
     * Verificar si es supervisor
     * 
     * @return bool
     */
    public static function isSupervisor(): bool
    {
        return self::hasRole('Supervisor') || self::isAdmin();
    }

    /**
     * Limpiar cache de permisos
     */
    public static function clearCache(): void
    {
        self::$cachedPermissions = null;
        unset($_SESSION['permissions']);
    }

    /**
     * Obtener todos los permisos disponibles agrupados por módulo
     * 
     * @return array
     */
    public static function getAllPermissionsGrouped(): array
    {
        $db = Database::getInstance();
        
        $sql = "SELECT id, nombre, codigo, descripcion, modulo 
                FROM permisos 
                ORDER BY modulo, nombre";
        
        $permissions = $db->fetchAll($sql);
        
        $grouped = [];
        foreach ($permissions as $permission) {
            $grouped[$permission['modulo']][] = $permission;
        }
        
        return $grouped;
    }

    /**
     * Verificar permiso o redirigir a 403
     * 
     * @param string $permission Código del permiso
     */
    public static function requirePermission(string $permission): void
    {
        if (!self::hasPermission($permission)) {
            http_response_code(403);
            include VIEWS_PATH . '/errors/403.php';
            exit;
        }
    }

    /**
     * Generar HTML de checkbox de permisos para un rol
     * 
     * @param array $rolePermissions Permisos actuales del rol
     * @return string HTML
     */
    public static function renderPermissionCheckboxes(array $rolePermissions = []): string
    {
        $grouped = self::getAllPermissionsGrouped();
        $html = '';
        
        foreach ($grouped as $module => $permissions) {
            $html .= "<div class=\"mb-4\">";
            $html .= "<h4 class=\"font-semibold text-gray-700 mb-2\">" . ucfirst($module) . "</h4>";
            $html .= "<div class=\"grid grid-cols-2 gap-2\">";
            
            foreach ($permissions as $perm) {
                $checked = in_array($perm['id'], $rolePermissions) ? 'checked' : '';
                $html .= "<label class=\"flex items-center space-x-2\">";
                $html .= "<input type=\"checkbox\" name=\"permissions[]\" value=\"{$perm['id']}\" {$checked} class=\"rounded\">";
                $html .= "<span class=\"text-sm\">{$perm['nombre']}</span>";
                $html .= "</label>";
            }
            
            $html .= "</div></div>";
        }
        
        return $html;
    }
}
