<?php
/**
 * Modelo EmailConfig - Whitelist, Blacklist de dominios y Log de envíos
 * Sistema de Tickets de Cancelación
 *
 * Lógica de validación (en orden de prioridad):
 *  1. ¿Está en whitelist y activo?           → PERMITIDO (prioridad sobre blacklist)
 *  2. ¿Su dominio está en blacklist?          → BLOQUEADO
 *  3. ¿No está en whitelist?                  → BLOQUEADO (política restrictiva)
 *
 * @author José Ernesto Ruiz Valdivia
 * @version 1.0
 */

namespace App\Models;

use App\Core\Database;

class EmailConfig
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // =========================================================
    // VALIDACIÓN PRINCIPAL
    // =========================================================

    /**
     * Verifica si un correo puede recibir facturas.
     * Prioridad: whitelist > blacklist de dominio > política restrictiva.
     *
     * @param string $email
     * @return array ['allowed' => bool, 'motivo' => string]
     */
    public function isEmailAllowed(string $email): array
    {
        $email = strtolower(trim($email));

        // 1. ¿Está en whitelist activa? → siempre permitido
        $inWhitelist = $this->db->fetchOne(
            "SELECT id FROM email_whitelist WHERE email = ? AND activo = 1",
            [$email]
        );
        if ($inWhitelist) {
            return ['allowed' => true, 'motivo' => 'Correo en whitelist'];
        }

        // 2. ¿Su dominio está en blacklist?
        $domain = substr(strrchr($email, '@'), 1);
        $blockedDomain = $this->db->fetchOne(
            "SELECT dominio, motivo FROM email_domain_blacklist WHERE dominio = ?",
            [$domain]
        );
        if ($blockedDomain) {
            return [
                'allowed' => false,
                'motivo'  => 'Dominio bloqueado: ' . $domain . ($blockedDomain['motivo'] ? ' – ' . $blockedDomain['motivo'] : '')
            ];
        }

        // 3. No está en whitelist → bloqueado (política restrictiva)
        return ['allowed' => false, 'motivo' => 'Correo no autorizado en whitelist'];
    }

    // =========================================================
    // WHITELIST
    // =========================================================

    /**
     * Obtener todos los correos en whitelist
     */
    public function getWhitelist(): array
    {
        return $this->db->fetchAll(
            "SELECT w.*, u.nombre_completo as creado_por_nombre
             FROM email_whitelist w
             LEFT JOIN usuarios u ON w.creado_por = u.id
             ORDER BY w.email ASC"
        );
    }

    /**
     * Agregar correo a la whitelist
     */
    public function addToWhitelist(string $email, string $descripcion, int $userId): ?int
    {
        $email = strtolower(trim($email));
        $this->db->query(
            "INSERT INTO email_whitelist (email, descripcion, activo, creado_por) VALUES (?, ?, 1, ?)",
            [$email, $descripcion ?: null, $userId]
        );
        return (int) $this->db->lastInsertId();
    }

    /**
     * Activar / desactivar correo en whitelist
     */
    public function toggleWhitelist(int $id): bool
    {
        $this->db->query(
            "UPDATE email_whitelist SET activo = IF(activo = 1, 0, 1) WHERE id = ?",
            [$id]
        );
        return true;
    }

    /**
     * Eliminar correo de la whitelist
     */
    public function removeFromWhitelist(int $id): bool
    {
        $this->db->query("DELETE FROM email_whitelist WHERE id = ?", [$id]);
        return true;
    }

    /**
     * Buscar entrada de whitelist por ID
     */
    public function findWhitelist(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM email_whitelist WHERE id = ?",
            [$id]
        );
    }

    // =========================================================
    // BLACKLIST DE DOMINIOS
    // =========================================================

    /**
     * Obtener todos los dominios bloqueados
     */
    public function getDomainBlacklist(): array
    {
        return $this->db->fetchAll(
            "SELECT b.*, u.nombre_completo as bloqueado_por_nombre
             FROM email_domain_blacklist b
             LEFT JOIN usuarios u ON b.bloqueado_por = u.id
             ORDER BY b.dominio ASC"
        );
    }

    /**
     * Agregar dominio a la blacklist
     */
    public function addDomainBlacklist(string $domain, string $motivo, int $userId): ?int
    {
        $domain = strtolower(trim($domain));
        $this->db->query(
            "INSERT INTO email_domain_blacklist (dominio, motivo, bloqueado_por) VALUES (?, ?, ?)",
            [$domain, $motivo ?: null, $userId]
        );
        return (int) $this->db->lastInsertId();
    }

    /**
     * Eliminar dominio de la blacklist
     */
    public function removeDomainBlacklist(int $id): bool
    {
        $this->db->query("DELETE FROM email_domain_blacklist WHERE id = ?", [$id]);
        return true;
    }

    /**
     * Buscar entrada de blacklist por ID
     */
    public function findBlacklist(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM email_domain_blacklist WHERE id = ?",
            [$id]
        );
    }

    // =========================================================
    // LOG DE ENVÍOS
    // =========================================================

    /**
     * Registrar un intento de envío
     */
    public function logEnvio(array $data): void
    {
        $this->db->query(
            "INSERT INTO factura_envios_email
             (factura_id, usuario_id, email_destino, asunto, resultado, detalle, id_operacion_api)
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                $data['factura_id'],
                $data['usuario_id'],
                $data['email_destino'],
                $data['asunto'],
                $data['resultado'],
                $data['detalle'] ?? null,
                $data['id_operacion_api'] ?? null,
            ]
        );
    }

    /**
     * Obtener historial de envíos de una factura
     */
    public function getEnviosByFactura(int $facturaId): array
    {
        return $this->db->fetchAll(
            "SELECT e.*, u.nombre_completo as usuario_nombre
             FROM factura_envios_email e
             LEFT JOIN usuarios u ON e.usuario_id = u.id
             WHERE e.factura_id = ?
             ORDER BY e.enviado_en DESC",
            [$facturaId]
        );
    }
}
