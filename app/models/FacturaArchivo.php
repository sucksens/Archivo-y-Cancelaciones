<?php
/**
 * Modelo FacturaArchivo - Gestión de archivos de facturas
 * Sistema de Tickets de Cancelación
 *
 * @author José Ernesto Ruiz Valdivia
 * @version 1.0
 */

namespace App\Models;

use App\Core\Database;
use App\Helpers\AuthHelper;

class FacturaArchivo
{
    private Database $db;
    private string $table = 'facturas_archivo';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Buscar factura por ID
     *
     * @param int $id ID de la factura
     * @return array|null
     */
    public function find(int $id): ?array
    {
        $sql = "SELECT fa.*, u.nombre_completo as usuario_nombre, u.email as usuario_email
                FROM {$this->table} fa
                LEFT JOIN usuarios u ON fa.usuario_id = u.id
                WHERE fa.id = ? AND fa.estado = 'activo'";
        return $this->db->fetchOne($sql, [$id]);
    }

    /**
     * Buscar factura por UUID
     *
     * @param string $uuid UUID de la factura
     * @return array|null
     */
    public function findByUuid(string $uuid): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE uuid_factura = ? AND estado = 'activo'";
        return $this->db->fetchOne($sql, [$uuid]);
    }

    /**
     * Crear nueva factura archivo
     *
     * @param array $data Datos de la factura
     * @return int|null ID de la factura creada
     */
    public function create(array $data): ?int
    {
        $sql = "INSERT INTO {$this->table}
                (usuario_id, empresa, tipo_factura, uuid_factura, archivo_xml, archivo_pdf,
                 serie, folio, total, fecha_emision, rfc_emisor, rfc_receptor,
                 id_suc, fecfac, inventario, id_vendedor, datos_extra)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $this->db->query($sql, [
            $data['usuario_id'],
            $data['empresa'],
            $data['tipo_factura'],
            $data['uuid_factura'],
            $data['archivo_xml'],
            $data['archivo_pdf'] ?? null,
            $data['serie'] ?? null,
            $data['folio'] ?? null,
            $data['total'] ?? null,
            $data['fecha_emision'] ?? null,
            $data['rfc_emisor'] ?? null,
            $data['rfc_receptor'] ?? null,
            $data['id_suc'] ?? null,
            $data['fecfac'] ?? null,
            $data['inventario'] ?? null,
            $data['id_vendedor'] ?? null,
            $data['datos_extra'] ?? null
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Actualizar factura
     *
     * @param int $id ID de la factura
     * @param array $data Datos a actualizar
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $allowedFields = [
            'archivo_pdf', 'serie', 'folio', 'total', 'fecha_emision',
            'rfc_emisor', 'rfc_receptor', 'datos_extra'
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
     * Eliminar factura (soft delete)
     *
     * @param int $id ID de la factura
     * @return bool
     */
    public function delete(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET estado = 'eliminado' WHERE id = ?";
        $this->db->query($sql, [$id]);
        return true;
    }

    /**
     * Obtener facturas del usuario con paginación
     *
     * @param int $userId ID del usuario
     * @param int $page Página
     * @param int $limit Límite
     * @param array $filters Filtros adicionales
     * @return array
     */
    public function getByUser(int $userId, int $page = 1, int $limit = ITEMS_PER_PAGE, array $filters = []): array
    {
        $where = ['fa.usuario_id = ?', "fa.estado = 'activo'"];
        $params = [$userId];

        if (!empty($filters['empresa'])) {
            $where[] = 'fa.empresa = ?';
            $params[] = $filters['empresa'];
        }

        if (!empty($filters['tipo_factura'])) {
            $where[] = 'fa.tipo_factura = ?';
            $params[] = $filters['tipo_factura'];
        }

        if (!empty($filters['search'])) {
            $where[] = '(fa.uuid_factura LIKE ? OR fa.serie LIKE ? OR fa.folio LIKE ? OR fa.rfc_receptor LIKE ?)';
            $search = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$search, $search, $search, $search]);
        }

        $whereClause = implode(' AND ', $where);
        $offset = ($page - 1) * $limit;

        $countSql = "SELECT COUNT(*) FROM {$this->table} fa WHERE {$whereClause}";
        $total = (int) $this->db->fetchColumn($countSql, $params);

        $sql = "SELECT fa.*, u.nombre_completo as usuario_nombre
                FROM {$this->table} fa
                LEFT JOIN usuarios u ON fa.usuario_id = u.id
                WHERE {$whereClause}
                ORDER BY fa.fecha_subida DESC
                LIMIT {$limit} OFFSET {$offset}";

        $facturas = $this->db->fetchAll($sql, $params);

        return [
            'data' => $facturas,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }

    /**
     * Obtener facturas por empresa (para rol Consulta)
     *
     * @param string $empresa Empresa del usuario
     * @param int $page Página
     * @param int $limit Límite
     * @param array $filters Filtros adicionales
     * @return array
     */
    public function getByEmpresa(string $empresa, int $page = 1, int $limit = ITEMS_PER_PAGE, array $filters = []): array
    {
        $where = ['fa.empresa = ?', "fa.estado = 'activo'"];
        $params = [$empresa];

        if (!empty($filters['tipo_factura'])) {
            $where[] = 'fa.tipo_factura = ?';
            $params[] = $filters['tipo_factura'];
        }

        if (!empty($filters['search'])) {
            $where[] = '(fa.uuid_factura LIKE ? OR fa.serie LIKE ? OR fa.folio LIKE ? OR fa.rfc_receptor LIKE ?)';
            $search = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$search, $search, $search, $search]);
        }

        $whereClause = implode(' AND ', $where);
        $offset = ($page - 1) * $limit;

        $countSql = "SELECT COUNT(*) FROM {$this->table} fa WHERE {$whereClause}";
        $total = (int) $this->db->fetchColumn($countSql, $params);

        $sql = "SELECT fa.*, u.nombre_completo as usuario_nombre
                FROM {$this->table} fa
                LEFT JOIN usuarios u ON fa.usuario_id = u.id
                WHERE {$whereClause}
                ORDER BY fa.fecha_subida DESC
                LIMIT {$limit} OFFSET {$offset}";

        $facturas = $this->db->fetchAll($sql, $params);

        return [
            'data' => $facturas,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }

    /**
     * Verificar si el usuario es propietario de la factura
     *
     * @param int $facturaId ID de la factura
     * @param int $userId ID del usuario
     * @return bool
     */
    public function isOwner(int $facturaId, int $userId): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE id = ? AND usuario_id = ? AND estado = 'activo'";
        return (int) $this->db->fetchColumn($sql, [$facturaId, $userId]) > 0;
    }

    /**
     * Obtener estadísticas de facturas
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
                    SUM(CASE WHEN archivo_pdf IS NOT NULL THEN 1 ELSE 0 END) as con_pdf
                FROM {$this->table}
                WHERE estado = 'activo'
                {$where}";

        return $this->db->fetchOne($sql, $params) ?: [
            'total' => 0, 'con_pdf' => 0
        ];
    }

    /**
     * Contar facturas por empresa y tipo
     *
     * @param string $empresa Empresa
     * @param string|null $tipoFactura Tipo de factura
     * @return int
     */
    public function countByEmpresaAndTipo(string $empresa, ?string $tipoFactura = null): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE empresa = ? AND estado = 'activo'";
        $params = [$empresa];

        if ($tipoFactura) {
            $sql .= ' AND tipo_factura = ?';
            $params[] = $tipoFactura;
        }

        return (int) $this->db->fetchColumn($sql, $params);
    }
}
