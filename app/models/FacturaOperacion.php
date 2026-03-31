<?php
/**
 * Modelo FacturaOperacion - Operaciones relacionadas a tickets
 * Sistema de Tickets de Cancelación
 * 
 * @author Sistema
 * @version 1.0
 */

namespace App\Models;

use App\Core\Database;

class FacturaOperacion
{
    private Database $db;
    private string $table = 'factura_operaciones';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Buscar operación por ID
     * 
     * @param int $id ID de la operación
     * @return array|null
     */
    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }

    /**
     * Crear nueva operación
     * 
     * @param array $data Datos de la operación
     * @return int|null ID de la operación creada
     */
    public function create(array $data): ?int
    {
        $sql = "INSERT INTO {$this->table} 
                (ticket_id, tipo_operacion,serie,id_compago, uuid_operacion, descripcion, monto, 
                 fecha_operacion, requiere_cancelacion, observaciones)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $this->db->query($sql, [
            $data['ticket_id'],
            $data['tipo_operacion'],
            $data['serie'] ?? null,
            $data['id_compago'] ?? null,
            $data['uuid_operacion'],
            $data['descripcion'] ?? null,
            $data['monto'] ?? null,
            $data['fecha_operacion'] ?? null,
            $data['requiere_cancelacion'] ?? 0,
            $data['observaciones'] ?? null
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Crear múltiples operaciones
     * 
     * @param int $ticketId ID del ticket
     * @param array $operaciones Lista de operaciones
     * @return bool
     */
    public function createMany(int $ticketId, array $operaciones): bool
    {
        foreach ($operaciones as $data) {
            $data['ticket_id'] = $ticketId;
            $this->create($data);
        }
        return true;
    }

    /**
     * Actualizar operación
     * 
     * @param int $id ID de la operación
     * @param array $data Datos a actualizar
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $allowedFields = [
            'tipo_operacion', 'uuid_operacion', 'descripcion', 'monto',
            'fecha_operacion', 'requiere_cancelacion', 'cancelada',
            'cancelado_sistema', 'cancelado_sat',
            'fecha_cancelacion', 'fecha_cancelacion_sat', 'observaciones'
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
     * Eliminar operación
     * 
     * @param int $id ID de la operación
     * @return bool
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $this->db->query($sql, [$id]);
        return true;
    }

    /**
     * Eliminar operaciones de un ticket
     * 
     * @param int $ticketId ID del ticket
     * @return bool
     */
    public function deleteByTicket(int $ticketId): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE ticket_id = ?";
        $this->db->query($sql, [$ticketId]);
        return true;
    }

    /**
     * Obtener operaciones de un ticket
     * 
     * @param int $ticketId ID del ticket
     * @return array
     */
    public function getByTicket(int $ticketId): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE ticket_id = ? ORDER BY id";
        return $this->db->fetchAll($sql, [$ticketId]);
    }

    /**
     * Marcar operación como cancelada
     * 
     * @param int $id ID de la operación
     * @return bool
     */
    public function markAsCancelled(int $id): bool
    {
        $sql = "UPDATE {$this->table} 
                SET cancelada = 1, fecha_cancelacion = CURRENT_TIMESTAMP 
                WHERE id = ?";
        $this->db->query($sql, [$id]);
        return true;
    }

    /**
     * Obtener operaciones pendientes de cancelación
     * 
     * @param int $ticketId ID del ticket
     * @return array
     */
    public function getPendingCancellation(int $ticketId): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE ticket_id = ? AND requiere_cancelacion = 1 AND cancelada = 0";
        return $this->db->fetchAll($sql, [$ticketId]);
    }

    /**
     * Sincronizar operaciones de un ticket
     * 
     * @param int $ticketId ID del ticket
     * @param array $operaciones Nuevas operaciones
     * @return bool
     */
    public function sync(int $ticketId, array $operaciones): bool
    {
        // Eliminar operaciones existentes
        $this->deleteByTicket($ticketId);
        
        // Crear nuevas
        if (!empty($operaciones)) {
            $this->createMany($ticketId, $operaciones);
        }
        
        return true;
    }

    /**
     * Contar operaciones de un ticket
     * 
     * @param int $ticketId ID del ticket
     * @return int
     */
    public function countByTicket(int $ticketId): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE ticket_id = ?";
        return (int) $this->db->fetchColumn($sql, [$ticketId]);
    }
}
