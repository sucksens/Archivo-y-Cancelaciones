-- ============================================================
-- Migración: Módulo de Envío de Facturas por Correo
-- Sistema de Tickets de Cancelación
-- Fecha: 2026-03-06
-- ============================================================

USE cancelaciones;

-- ============================================================
-- TABLA: email_whitelist
-- Correos autorizados para recibir facturas
-- ============================================================
CREATE TABLE IF NOT EXISTS email_whitelist (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(150) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NULL,
    activo TINYINT(1) DEFAULT 1,
    creado_por INT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_activo (activo),
    CONSTRAINT fk_ewl_usuario FOREIGN KEY (creado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: email_domain_blacklist
-- Dominios de correo prohibidos
-- ============================================================
CREATE TABLE IF NOT EXISTS email_domain_blacklist (
    id INT PRIMARY KEY AUTO_INCREMENT,
    dominio VARCHAR(150) NOT NULL UNIQUE,
    motivo VARCHAR(255) NULL,
    bloqueado_por INT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_dominio (dominio),
    CONSTRAINT fk_edbl_usuario FOREIGN KEY (bloqueado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dominios internos bloqueados por defecto
-- (la whitelist individual tiene prioridad sobre esta blacklist)
INSERT INTO email_domain_blacklist (dominio, motivo) VALUES
('motormexa.mx',      'Dominio interno – solo acceso por whitelist explícita'),
('grupomotormexa.com','Dominio interno – solo acceso por whitelist explícita');

-- ============================================================
-- TABLA: factura_envios_email
-- Registro histórico de todos los envíos de facturas por correo
-- ============================================================
CREATE TABLE IF NOT EXISTS factura_envios_email (
    id INT PRIMARY KEY AUTO_INCREMENT,
    factura_id INT NOT NULL,
    usuario_id INT NOT NULL,
    email_destino VARCHAR(150) NOT NULL,
    asunto VARCHAR(255) NOT NULL,
    resultado ENUM('enviado','error','bloqueado') NOT NULL,
    detalle TEXT NULL,
    id_operacion_api VARCHAR(36) NULL,
    enviado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_factura (factura_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_resultado (resultado),
    INDEX idx_enviado_en (enviado_en),
    CONSTRAINT fk_fee_factura FOREIGN KEY (factura_id) REFERENCES facturas_archivo(id) ON DELETE CASCADE,
    CONSTRAINT fk_fee_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- NUEVOS PERMISOS: Módulo Email
-- ============================================================
INSERT IGNORE INTO permisos (nombre, codigo, descripcion, modulo) VALUES
('Enviar Factura por Email',     'facturas.email.send',             'Enviar factura (PDF+XML) al correo del cliente', 'facturas'),
('Gestionar Whitelist de Email', 'facturas.email.manage_whitelist', 'Agregar/eliminar correos autorizados y dominios bloqueados', 'facturas');

-- Asignar permiso de envío a Administrador (nivel 100) y Supervisor (nivel 75)
INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r, permisos p
WHERE r.nivel >= 75
  AND p.codigo = 'facturas.email.send';

-- Solo Administrador gestiona whitelist/blacklist
INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r, permisos p
WHERE r.nivel >= 100
  AND p.codigo = 'facturas.email.manage_whitelist';

-- ============================================================
-- FIN DE MIGRACIÓN
-- ============================================================
