<?php
/**
 * Modelo Log - Registro de actividades del sistema
 * Sistema de Tickets de Cancelación
 * 
 * @author Sistema
 * @version 1.0
 */

namespace App\Models;

use App\Core\Database;

class Log
{
    private Database $db;
    private string $table = 'logs_sistema';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Registrar una entrada de log
     * 
     * @param string $tipoLog Tipo de log
     * @param string $modulo Módulo
     * @param string $accion Acción
     * @param string|null $detalles Detalles adicionales
     * @param int|null $userId ID del usuario
     * @return int|null ID del log
     */
    public function log(string $tipoLog, string $modulo, string $accion, ?string $detalles = null, ?int $userId = null): ?int
    {
        $sql = "INSERT INTO {$this->table} 
                (usuario_id, tipo_log, modulo, accion, detalles, ip_address, user_agent)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $this->db->query($sql, [
            $userId ?? ($_SESSION['user_id'] ?? null),
            $tipoLog,
            $modulo,
            $accion,
            $detalles,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Registrar login
     * 
     * @param int $userId ID del usuario
     * @param bool $success Si fue exitoso
     */
    public function logLogin(int $userId, bool $success = true): void
    {
        $accion = $success ? 'Login exitoso' : 'Login fallido';
        $this->log('login', 'auth', $accion, null, $userId);
    }

    /**
     * Registrar logout
     * 
     * @param int $userId ID del usuario
     */
    public function logLogout(int $userId): void
    {
        $this->log('login', 'auth', 'Logout', null, $userId);
    }

    /**
     * Registrar acción
     * 
     * @param string $modulo Módulo
     * @param string $accion Acción
     * @param string|null $detalles Detalles
     */
    public function logAction(string $modulo, string $accion, ?string $detalles = null): void
    {
        $this->log('accion', $modulo, $accion, $detalles);
    }

    /**
     * Registrar error
     * 
     * @param string $modulo Módulo
     * @param string $mensaje Mensaje de error
     * @param string|null $trace Stack trace
     */
    public function logError(string $modulo, string $mensaje, ?string $trace = null): void
    {
        $this->log('error', $modulo, $mensaje, $trace);
    }

    /**
     * Obtener logs con paginación
     * 
     * @param array $filters Filtros
     * @param int $page Página
     * @param int $limit Límite
     * @return array
     */
    public function getAll(array $filters = [], int $page = 1, int $limit = ITEMS_PER_PAGE): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['tipo_log'])) {
            $where[] = 'l.tipo_log = ?';
            $params[] = $filters['tipo_log'];
        }

        if (!empty($filters['modulo'])) {
            $where[] = 'l.modulo = ?';
            $params[] = $filters['modulo'];
        }

        if (!empty($filters['usuario_id'])) {
            $where[] = 'l.usuario_id = ?';
            $params[] = $filters['usuario_id'];
        }

        if (!empty($filters['fecha_desde'])) {
            $where[] = 'DATE(l.fecha) >= ?';
            $params[] = $filters['fecha_desde'];
        }

        if (!empty($filters['fecha_hasta'])) {
            $where[] = 'DATE(l.fecha) <= ?';
            $params[] = $filters['fecha_hasta'];
        }

        if (!empty($filters['search'])) {
            $where[] = '(l.accion LIKE ? OR l.detalles LIKE ?)';
            $search = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$search, $search]);
        }

        $whereClause = implode(' AND ', $where);
        $offset = ($page - 1) * $limit;

        // Contar total
        $countSql = "SELECT COUNT(*) FROM {$this->table} l WHERE {$whereClause}";
        $total = (int) $this->db->fetchColumn($countSql, $params);

        // Obtener registros
        $sql = "SELECT l.*, u.username, u.nombre_completo
                FROM {$this->table} l
                LEFT JOIN usuarios u ON l.usuario_id = u.id
                WHERE {$whereClause}
                ORDER BY l.fecha DESC
                LIMIT {$limit} OFFSET {$offset}";
        
        $logs = $this->db->fetchAll($sql, $params);

        return [
            'data' => $logs,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }

    /**
     * Obtener logs recientes
     * 
     * @param int $limit Cantidad
     * @return array
     */
    public function getRecent(int $limit = 10): array
    {
        $sql = "SELECT l.*, u.nombre_completo
                FROM {$this->table} l
                LEFT JOIN usuarios u ON l.usuario_id = u.id
                ORDER BY l.fecha DESC
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$limit]);
    }

    /**
     * Obtener módulos con actividad
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
     * Limpiar logs antiguos
     * 
     * @param int $days Días a mantener
     * @return int Registros eliminados
     */
    public function cleanup(int $days = 90): int
    {
        $sql = "DELETE FROM {$this->table} WHERE fecha < DATE_SUB(NOW(), INTERVAL ? DAY)";
        $stmt = $this->db->query($sql, [$days]);
        return $stmt->rowCount();
    }

    /**
     * Obtener estadísticas de logs
     * 
     * @param int $days Días a analizar
     * @return array
     */
    public function getStats(int $days = 7): array
    {
        $sql = "SELECT 
                    tipo_log,
                    COUNT(*) as total
                FROM {$this->table}
                WHERE fecha >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY tipo_log";
        
        return $this->db->fetchAll($sql, [$days]);
    }
}
