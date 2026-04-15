<?php
/**
 * Modelo Ticket - Gestión de tickets de cancelación
 * Sistema de Tickets de Cancelación
 * 
 * @author José Ernesto Ruiz Valdivia
 * @version 1.0
 */

namespace App\Models;

use App\Core\Database;
use App\Helpers\AuthHelper;

class Ticket
{
    private Database $db;
    private string $table = 'tickets_cancelacion';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Buscar ticket por ID
     * 
     * @param int $id ID del ticket
     * @return array|null
     */
    public function find(int $id): ?array
    {
        $sql = "SELECT t.*, u.nombre_completo as usuario_nombre, u.email as usuario_email
                FROM {$this->table} t
                LEFT JOIN usuarios u ON t.usuario_id = u.id
                WHERE t.id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }

    /**
     * Buscar ticket por UUID
     * 
     * @param string $uuid UUID del ticket
     * @return array|null
     */
    public function findByUuid(string $uuid): ?array
    {
        $sql = "SELECT t.*, u.nombre_completo as usuario_nombre
                FROM {$this->table} t
                LEFT JOIN usuarios u ON t.usuario_id = u.id
                WHERE t.uuid = ?";
        return $this->db->fetchOne($sql, [$uuid]);
    }

    /**
     * Buscar ticket por UUID de factura
     * 
     * @param string $uuid UUID de la factura
     * @return array|null
     */
    public function findByFacturaUuid(string $uuid): ?array
    {
        $sql = "SELECT t.*, u.nombre_completo as usuario_nombre
                FROM {$this->table} t
                LEFT JOIN usuarios u ON t.usuario_id = u.id
                WHERE t.uuid_factura = ?";
        return $this->db->fetchOne($sql, [$uuid]);
    }

    /**
     * Crear nuevo ticket
     * 
     * @param array $data Datos del ticket
     * @return int|null ID del ticket creado
     */
    public function create(array $data): ?int
    {
        $uuid = AuthHelper::generateUuid();
        
        $sql = "INSERT INTO {$this->table} 
                (uuid, usuario_id, empresa_solicitante, tipo_factura, uuid_factura, serie, folio, 
                 inventario, nombre_cliente, total_factura, rfc_receptor, 
                 tipo_cancelacion, motivo, archivo_autorizacion, estado,
                 fecfac, id_pedido, id_vendedor, id_suc)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente', ?, ?, ?, ?)";
        
        $this->db->query($sql, [
            $uuid,
            $data['usuario_id'],
            $data['empresa_solicitante'],
            $data['tipo_factura'],
            $data['uuid_factura'],
            $data['serie'],
            $data['folio'],
            $data['inventario'] ?? null,
            $data['nombre_cliente'],
            $data['total_factura'],
            $data['rfc_receptor'],
            $data['tipo_cancelacion'],
            $data['motivo'],
            $data['archivo_autorizacion'],
            $data['fecfac'] ?? null,
            $data['id_pedido'] ?? null,
            $data['id_vendedor'] ?? null,
            $data['id_suc'] ?? null
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Actualizar ticket
     * 
     * @param int $id ID del ticket
     * @param array $data Datos a actualizar
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $allowedFields = [
            'empresa_solicitante', 'tipo_factura', 'uuid_factura', 'serie', 'folio', 'inventario',
            'nombre_cliente', 'total_factura', 'rfc_receptor', 'tipo_cancelacion',
            'motivo', 'archivo_autorizacion', 'estado', 'fecha_envio_cancelacion',
            'fecha_cancelacion_sat', 'completado_por', 'fecfac', 'id_pedido', 
            'id_vendedor', 'id_suc'
        ];

        $fields = [];
        $values = [];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
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
     * Actualizar estado del ticket
     * 
     * @param int $id ID del ticket
     * @param string $estado Nuevo estado
     * @param int|null $userId Usuario que realiza el cambio
     * @return bool
     */
    public function updateStatus(int $id, string $estado, ?int $userId = null): bool
    {
        $updateData = ['estado' => $estado];

        // Actualizar fechas según el estado
        switch ($estado) {
            case 'proceso_cancelacion':
                $updateData['fecha_envio_cancelacion'] = date('Y-m-d H:i:s');
                break;
            case 'cancelado':
            case 'completado':
                $updateData['fecha_cancelacion_sat'] = date('Y-m-d H:i:s');
                $updateData['completado_por'] = $userId;
                break;
        }

        return $this->update($id, $updateData);
    }

    /**
     * Eliminar ticket
     * 
     * @param int $id ID del ticket
     * @return bool
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $this->db->query($sql, [$id]);
        return true;
    }

    /**
     * Obtener todos los tickets con paginación
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

        if (!empty($filters['usuario_id'])) {
            $where[] = 't.usuario_id = ?';
            $params[] = $filters['usuario_id'];
        }

        if (!empty($filters['empresa'])) {
            $where[] = 't.empresa_solicitante = ?';
            $params[] = $filters['empresa'];
        }

        if (!empty($filters['estado'])) {
            $where[] = 't.estado = ?';
            $params[] = $filters['estado'];
        }

        if (!empty($filters['tipo_cancelacion'])) {
            $where[] = 't.tipo_cancelacion = ?';
            $params[] = $filters['tipo_cancelacion'];
        }

        if (!empty($filters['tipo_factura'])) {
            $where[] = 't.tipo_factura = ?';
            $params[] = $filters['tipo_factura'];
        }

        if (!empty($filters['fecha_desde'])) {
            $where[] = 'DATE(t.fecha_creacion) >= ?';
            $params[] = $filters['fecha_desde'];
        }

        if (!empty($filters['fecha_hasta'])) {
            $where[] = 'DATE(t.fecha_creacion) <= ?';
            $params[] = $filters['fecha_hasta'];
        }

        if (!empty($filters['search'])) {
            $where[] = '(t.uuid_factura LIKE ? OR t.nombre_cliente LIKE ? OR t.rfc_receptor LIKE ? OR t.folio LIKE ?)';
            $search = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$search, $search, $search, $search]);
        }

        $whereClause = implode(' AND ', $where);
        $offset = ($page - 1) * $limit;

        // Contar total
        $countSql = "SELECT COUNT(*) FROM {$this->table} t WHERE {$whereClause}";
        $total = (int) $this->db->fetchColumn($countSql, $params);

        // Obtener registros
        $sql = "SELECT t.*, u.nombre_completo as usuario_nombre
                FROM {$this->table} t
                LEFT JOIN usuarios u ON t.usuario_id = u.id
                WHERE {$whereClause}
                ORDER BY t.fecha_creacion DESC
                LIMIT {$limit} OFFSET {$offset}";
        
        $tickets = $this->db->fetchAll($sql, $params);

        return [
            'data' => $tickets,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }

    /**
     * Obtener tickets del usuario
     * 
     * @param int $userId ID del usuario
     * @param int $page Página
     * @param int $limit Límite
     * @param array $filters Filtros adicionales
     * @return array
     */
    public function getByUser(int $userId, int $page = 1, int $limit = ITEMS_PER_PAGE, array $filters = []): array
    {
        $filters['usuario_id'] = $userId;
        return $this->getAll($filters, $page, $limit);
    }

    /**
     * Obtener tickets por empresa (para rol Consulta)
     * 
     * @param string $empresa Empresa del usuario
     * @param int $page Página
     * @param int $limit Límite
     * @param array $filters Filtros adicionales
     * @return array
     */
    public function getByEmpresa(string $empresa, int $page = 1, int $limit = ITEMS_PER_PAGE, array $filters = []): array
    {
        $filters['empresa'] = $empresa;
        return $this->getAll($filters, $page, $limit);
    }

    /**
     * Verificar si un ticket puede verificar status SAT
     * Solo puede verificar si está en estado pendiente o en_revision
     * 
     * @param int $ticketId ID del ticket
     * @return bool
     */
    public function canVerifySat(int $ticketId): bool
    {
        $sql = "SELECT estado FROM {$this->table} WHERE id = ?";
        $estado = $this->db->fetchColumn($sql, [$ticketId]);
        return in_array($estado, ['pendiente', 'en_revision']);
    }

    /**
     * Obtener ticket con operaciones
     * 
     * @param int $id ID del ticket
     * @return array|null
     */
    public function getWithOperaciones(int $id): ?array
    {
        $ticket = $this->find($id);
        
        if (!$ticket) {
            return null;
        }

        // Obtener operaciones relacionadas
        $sql = "SELECT * FROM factura_operaciones WHERE ticket_id = ? ORDER BY id";
        $ticket['operaciones'] = $this->db->fetchAll($sql, [$id]);

        // Obtener auditoría
        $sql = "SELECT a.*, u.nombre_completo as usuario_nombre
                FROM auditoria_tickets a
                LEFT JOIN usuarios u ON a.usuario_id = u.id
                WHERE a.ticket_id = ?
                ORDER BY a.fecha DESC";
        $ticket['auditoria'] = $this->db->fetchAll($sql, [$id]);

        return $ticket;
    }

    /**
     * Obtener estadísticas de tickets
     * 
     * @param int|null $userId ID del usuario (null para todos)
     * @return array
     */
    public function getStats(?int $userId = null): array
    {
        $where = $userId ? 'WHERE usuario_id = ?' : '';
        $params = $userId ? [$userId] : [];

        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                    SUM(CASE WHEN estado = 'en_revision' THEN 1 ELSE 0 END) as en_revision,
                    SUM(CASE WHEN estado = 'proceso_cancelacion' THEN 1 ELSE 0 END) as en_proceso,
                    SUM(CASE WHEN estado = 'cancelado' THEN 1 ELSE 0 END) as cancelados,
                    SUM(CASE WHEN estado = 'rechazado' THEN 1 ELSE 0 END) as rechazados,
                    SUM(CASE WHEN estado = 'completado' THEN 1 ELSE 0 END) as completados
                FROM {$this->table} {$where}";

        return $this->db->fetchOne($sql, $params) ?: [
            'total' => 0, 'pendientes' => 0, 'en_revision' => 0,
            'en_proceso' => 0, 'cancelados' => 0, 'rechazados' => 0, 'completados' => 0
        ];
    }

    /**
     * Obtener tickets recientes
     * 
     * @param int $limit Cantidad
     * @return array
     */
    public function getRecent(int $limit = 5): array
    {
        $sql = "SELECT t.id, t.uuid, t.nombre_cliente, t.estado, t.fecha_creacion,
                       u.nombre_completo as usuario_nombre
                FROM {$this->table} t
                LEFT JOIN usuarios u ON t.usuario_id = u.id
                ORDER BY t.fecha_creacion DESC
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$limit]);
    }

    /**
     * Registrar auditoría de cambio
     * 
     * @param int $ticketId ID del ticket
     * @param int $userId ID del usuario
     * @param string $accion Acción realizada
     * @param string|null $campo Campo modificado
     * @param mixed $valorAnterior Valor anterior
     * @param mixed $valorNuevo Valor nuevo
     */
    public function audit(int $ticketId, int $userId, string $accion, ?string $campo = null, $valorAnterior = null, $valorNuevo = null): void
    {
        $sql = "INSERT INTO auditoria_tickets 
                (ticket_id, usuario_id, accion, campo_modificado, valor_anterior, valor_nuevo, ip_address)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $this->db->query($sql, [
            $ticketId,
            $userId,
            $accion,
            $campo,
            is_array($valorAnterior) ? json_encode($valorAnterior) : $valorAnterior,
            is_array($valorNuevo) ? json_encode($valorNuevo) : $valorNuevo,
            $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    }

    /**
     * Verificar si el usuario es propietario del ticket
     * 
     * @param int $ticketId ID del ticket
     * @param int $userId ID del usuario
     * @return bool
     */
    public function isOwner(int $ticketId, int $userId): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE id = ? AND usuario_id = ?";
        return (int) $this->db->fetchColumn($sql, [$ticketId, $userId]) > 0;
    }
}
