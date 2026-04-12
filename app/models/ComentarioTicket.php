<?php

namespace App\Models;

use App\Core\Database;
use App\Helpers\AuthHelper;

/**
 * Modelo ComentarioTicket - Gestión de comentarios en tickets
 * Sistema de Tickets de Cancelación
 * 
 * @author José Ernesto Ruiz Valdivia
 * @version 1.0
 */

class ComentarioTicket
{
    private Database $db;
    private string $table = 'comentarios_ticket';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Obtener comentarios de un ticket con datos de usuario y rol
     * 
     * @param int $ticketId ID del ticket
     * @return array
     */
    public function getByTicket(int $ticketId): array
    {
        $sql = "SELECT c.*,
                       u.nombre_completo as usuario_nombre,
                       u.email as usuario_email,
                       (SELECT r.nombre 
                        FROM roles r 
                        INNER JOIN usuario_rol ur ON r.id = ur.rol_id 
                        WHERE ur.usuario_id = u.id 
                        ORDER BY r.nivel DESC 
                        LIMIT 1) as rol_nombre
                FROM {$this->table} c
                INNER JOIN usuarios u ON c.usuario_id = u.id
                WHERE c.ticket_id = ?
                ORDER BY c.fecha_creacion DESC";
        
        return $this->db->fetchAll($sql, [$ticketId]);
    }

    /**
     * Crear nuevo comentario
     * 
     * @param array $data Datos del comentario
     * @return int|false ID del comentario creado o false en caso de error
     */
    public function create(array $data)
    {
        $sql = "INSERT INTO {$this->table} (ticket_id, usuario_id, comentario) 
                VALUES (?, ?, ?)";
        
        $this->db->query($sql, [
            $data['ticket_id'],
            $data['usuario_id'],
            $data['comentario']
        ]);
        
        $lastId = $this->db->lastInsertId();
        
        return $lastId ? (int) $lastId : false;
    }

    /**
     * Eliminar comentario
     * 
     * @param int $id ID del comentario
     * @return bool
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        
        return $this->db->query($sql, [$id]) !== false;
    }

    /**
     * Obtener un comentario por ID con datos de usuario y rol
     * 
     * @param int $id ID del comentario
     * @return array|null
     */
    public function find(int $id): ?array
    {
        $sql = "SELECT c.*,
                       u.nombre_completo as usuario_nombre,
                       u.email as usuario_email,
                       (SELECT r.nombre 
                        FROM roles r 
                        INNER JOIN usuario_rol ur ON r.id = ur.rol_id 
                        WHERE ur.usuario_id = u.id 
                        ORDER BY r.nivel DESC 
                        LIMIT 1) as rol_nombre
                FROM {$this->table} c
                INNER JOIN usuarios u ON c.usuario_id = u.id
                WHERE c.id = ?";
        
        $result = $this->db->fetchOne($sql, [$id]);
        
        return $result ?: null;
    }

    /**
     * Verificar si el usuario es el autor del comentario
     * 
     * @param int $comentarioId ID del comentario
     * @param int $userId ID del usuario
     * @return bool
     */
    public function isOwner(int $comentarioId, int $userId): bool
    {
        $sql = "SELECT id FROM {$this->table} WHERE id = ? AND usuario_id = ?";
        
        return $this->db->fetchOne($sql, [$comentarioId, $userId]) !== null;
    }

    /**
     * Contar comentarios de un ticket
     * 
     * @param int $ticketId ID del ticket
     * @return int
     */
    public function countByTicket(int $ticketId): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE ticket_id = ?";
        
        $result = $this->db->fetchOne($sql, [$ticketId]);
        
        return (int) ($result['total'] ?? 0);
    }
}
