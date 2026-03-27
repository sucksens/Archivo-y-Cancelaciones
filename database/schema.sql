-- ============================================
-- Script SQL para Sistema de Tickets de Cancelación
-- Base de datos: cancelaciones
-- MySQL 5.7+
-- ============================================

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS cancelaciones
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE cancelaciones;

-- ============================================
-- TABLA: usuarios
-- ============================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    nombre_completo VARCHAR(150) NOT NULL,
    empresa ENUM('grupo_motormexa', 'automotriz_motormexa') NOT NULL,
    departamento VARCHAR(100) NULL,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_login TIMESTAMP NULL,
    reset_token VARCHAR(100) NULL,
    reset_expiry TIMESTAMP NULL,
    UNIQUE KEY uk_username (username),
    UNIQUE KEY uk_email (email),
    INDEX idx_empresa (empresa),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: roles
-- ============================================
CREATE TABLE IF NOT EXISTS roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    descripcion TEXT NULL,
    nivel INT DEFAULT 0,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NULL,
    UNIQUE KEY uk_nombre (nombre),
    INDEX idx_nivel (nivel)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TRIGGER para actualizar actualizado_en en roles
-- ============================================
DELIMITER //
CREATE TRIGGER update_roles_timestamp 
BEFORE UPDATE ON roles 
FOR EACH ROW 
BEGIN
    SET NEW.actualizado_en = CURRENT_TIMESTAMP;
END//
DELIMITER ;
-- ============================================
-- TABLA: permisos
-- ============================================
CREATE TABLE IF NOT EXISTS permisos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    codigo VARCHAR(50) NOT NULL,
    descripcion TEXT NULL,
    modulo VARCHAR(50) NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_nombre (nombre),
    UNIQUE KEY uk_codigo (codigo),
    INDEX idx_modulo (modulo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: usuario_rol (relación muchos a muchos)
-- ============================================
CREATE TABLE IF NOT EXISTS usuario_rol (
    usuario_id INT NOT NULL,
    rol_id INT NOT NULL,
    asignado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    asignado_por INT NULL,
    PRIMARY KEY (usuario_id, rol_id),
    CONSTRAINT fk_ur_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_ur_rol FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE CASCADE,
    CONSTRAINT fk_ur_asignador FOREIGN KEY (asignado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: rol_permiso (relación muchos a muchos)
-- ============================================
CREATE TABLE IF NOT EXISTS rol_permiso (
    rol_id INT NOT NULL,
    permiso_id INT NOT NULL,
    concedido_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    concedido_por INT NULL,
    PRIMARY KEY (rol_id, permiso_id),
    CONSTRAINT fk_rp_rol FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE CASCADE,
    CONSTRAINT fk_rp_permiso FOREIGN KEY (permiso_id) REFERENCES permisos(id) ON DELETE CASCADE,
    CONSTRAINT fk_rp_concededor FOREIGN KEY (concedido_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: tickets_cancelacion
-- ============================================
CREATE TABLE IF NOT EXISTS tickets_cancelacion (
    id INT PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) NOT NULL,
    usuario_id INT NOT NULL,
    empresa_solicitante ENUM('grupo_motormexa', 'automotriz_motormexa') NOT NULL,
    uuid_factura CHAR(36) NOT NULL,
    serie VARCHAR(20) NOT NULL,
    folio VARCHAR(20) NOT NULL,
    inventario VARCHAR(50) NULL,
    nombre_cliente VARCHAR(200) NOT NULL,
    total_factura DECIMAL(15,2) NOT NULL,
    rfc_receptor VARCHAR(13) NOT NULL,
    tipo_cancelacion ENUM('cancelacion_total', 'refacturacion') NOT NULL,
    motivo TEXT NOT NULL,
    archivo_autorizacion VARCHAR(255) NOT NULL,
    estado ENUM('pendiente', 'en_revision', 'proceso_cancelacion', 'cancelado', 'rechazado', 'completado') DEFAULT 'pendiente',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_envio_cancelacion TIMESTAMP NULL,
    fecha_cancelacion_sat TIMESTAMP NULL,
    completado_por INT NULL,
    UNIQUE KEY uk_uuid (uuid),
    INDEX idx_usuario (usuario_id),
    INDEX idx_estado (estado),
    INDEX idx_empresa (empresa_solicitante),
    INDEX idx_fecha_creacion (fecha_creacion),
    INDEX idx_uuid_factura (uuid_factura),
    CONSTRAINT fk_tc_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
    CONSTRAINT fk_tc_completador FOREIGN KEY (completado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: factura_operaciones
-- ============================================
CREATE TABLE IF NOT EXISTS factura_operaciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ticket_id INT NOT NULL,
    tipo_operacion ENUM('complemento_pago', 'nota_aplicacion', 'anticipo', 'documento_relacionado') NOT NULL,
    uuid_operacion CHAR(36) NOT NULL,
    descripcion VARCHAR(255) NULL,
    monto DECIMAL(15,2) NULL,
    fecha_operacion DATE NULL,
    requiere_cancelacion TINYINT(1) DEFAULT 0,
    cancelada TINYINT(1) DEFAULT 0,
    fecha_cancelacion TIMESTAMP NULL,
    observaciones TEXT NULL,
    INDEX idx_ticket (ticket_id),
    INDEX idx_tipo (tipo_operacion),
    CONSTRAINT fk_fo_ticket FOREIGN KEY (ticket_id) REFERENCES tickets_cancelacion(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: logs_sistema
-- ============================================
CREATE TABLE IF NOT EXISTS logs_sistema (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NULL,
    tipo_log ENUM('login', 'accion', 'error', 'sistema') NOT NULL,
    modulo VARCHAR(50) NOT NULL,
    accion VARCHAR(100) NOT NULL,
    detalles TEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_usuario (usuario_id),
    INDEX idx_tipo (tipo_log),
    INDEX idx_fecha (fecha),
    INDEX idx_modulo (modulo),
    CONSTRAINT fk_ls_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: auditoria_tickets
-- ============================================
CREATE TABLE IF NOT EXISTS auditoria_tickets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ticket_id INT NOT NULL,
    usuario_id INT NOT NULL,
    accion VARCHAR(100) NOT NULL,
    campo_modificado VARCHAR(100) NULL,
    valor_anterior TEXT NULL,
    valor_nuevo TEXT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45) NULL,
    INDEX idx_ticket (ticket_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_fecha (fecha),
    CONSTRAINT fk_at_ticket FOREIGN KEY (ticket_id) REFERENCES tickets_cancelacion(id) ON DELETE CASCADE,
    CONSTRAINT fk_at_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: configuracion_sistema (SOLUCIÓN FINAL)
-- ============================================
CREATE TABLE IF NOT EXISTS configuracion_sistema (
    id INT PRIMARY KEY AUTO_INCREMENT,
    clave VARCHAR(100) NOT NULL,
    valor TEXT NULL,
    tipo ENUM('string', 'number', 'boolean', 'json', 'array') DEFAULT 'string',
    categoria VARCHAR(50) DEFAULT 'general',
    descripcion TEXT NULL,
    editable TINYINT(1) DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en DATETIME NULL DEFAULT NULL,
    UNIQUE KEY uk_clave (clave),
    INDEX idx_categoria (categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Crear trigger para actualizar actualizado_en
DELIMITER //
CREATE TRIGGER trg_configuracion_sistema_update
BEFORE UPDATE ON configuracion_sistema
FOR EACH ROW
BEGIN
    SET NEW.actualizado_en = NOW();
END//
DELIMITER ;

-- ============================================
-- TABLA: sesiones
-- ============================================
CREATE TABLE IF NOT EXISTS sesiones (
    id VARCHAR(128) PRIMARY KEY,
    usuario_id INT NULL,
    payload TEXT NULL,
    ultima_actividad INT NOT NULL,
    user_agent TEXT NULL,
    ip_address VARCHAR(45) NULL,
    INDEX idx_usuario (usuario_id),
    INDEX idx_ultima_actividad (ultima_actividad),
    CONSTRAINT fk_s_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DATOS INICIALES: Roles
-- ============================================
INSERT INTO roles (nombre, descripcion, nivel) VALUES
('Administrador', 'Acceso completo al sistema. Puede gestionar usuarios, roles y todos los tickets.', 100),
('Supervisor', 'Puede revisar y procesar tickets de cancelación. Ve todos los tickets.', 75),
('Usuario', 'Puede crear y ver sus propios tickets de cancelación.', 50),
('Consulta', 'Solo puede ver tickets, sin capacidad de crear o modificar.', 25);

-- ============================================
-- DATOS INICIALES: Permisos
-- ============================================

-- Permisos de administración
INSERT INTO permisos (nombre, codigo, descripcion, modulo) VALUES
('Administración Total', 'admin.all', 'Acceso completo a todas las funciones del sistema', 'admin'),
('Gestionar Usuarios', 'admin.users', 'Crear, editar y eliminar usuarios', 'admin'),
('Gestionar Roles', 'admin.roles', 'Crear, editar y asignar roles', 'admin'),
('Gestionar Permisos', 'admin.permissions', 'Asignar permisos a roles', 'admin'),
('Ver Configuración', 'admin.config.view', 'Ver configuración del sistema', 'admin'),
('Editar Configuración', 'admin.config.edit', 'Modificar configuración del sistema', 'admin');

-- Permisos de tickets
INSERT INTO permisos (nombre, codigo, descripcion, modulo) VALUES
('Crear Tickets', 'tickets.create', 'Crear nuevos tickets de cancelación', 'tickets'),
('Ver Tickets Propios', 'tickets.view.own', 'Ver tickets creados por el usuario', 'tickets'),
('Ver Todos los Tickets', 'tickets.view.all', 'Ver todos los tickets del sistema', 'tickets'),
('Editar Tickets', 'tickets.edit', 'Editar tickets de cancelación', 'tickets'),
('Cambiar Estado', 'tickets.status', 'Cambiar el estado de los tickets', 'tickets'),
('Eliminar Tickets', 'tickets.delete', 'Eliminar tickets de cancelación', 'tickets'),
('Procesar Cancelación', 'tickets.process', 'Procesar la cancelación ante el SAT', 'tickets');

-- Permisos de reportes
INSERT INTO permisos (nombre, codigo, descripcion, modulo) VALUES
('Ver Dashboard', 'reports.dashboard', 'Ver el dashboard con estadísticas', 'reports'),
('Exportar Reportes', 'reports.export', 'Exportar reportes a Excel/PDF', 'reports'),
('Ver Logs', 'reports.logs', 'Ver logs del sistema', 'reports');

-- ============================================
-- ASIGNAR PERMISOS A ROLES
-- ============================================

-- Administrador: todos los permisos
INSERT INTO rol_permiso (rol_id, permiso_id)
SELECT 1, id FROM permisos;

-- Supervisor: tickets y reportes
INSERT INTO rol_permiso (rol_id, permiso_id)
SELECT 2, id FROM permisos WHERE modulo IN ('tickets', 'reports');

-- Usuario: crear y ver propios tickets
INSERT INTO rol_permiso (rol_id, permiso_id)
SELECT 3, id FROM permisos WHERE codigo IN ('tickets.create', 'tickets.view.own', 'reports.dashboard');

-- Consulta: solo ver
INSERT INTO rol_permiso (rol_id, permiso_id)
SELECT 4, id FROM permisos WHERE codigo IN ('tickets.view.own', 'reports.dashboard');

-- ============================================
-- USUARIO ADMINISTRADOR POR DEFECTO
-- Password: Admin123!
-- ============================================
INSERT INTO usuarios (username, email, password_hash, nombre_completo, empresa, departamento, activo)
VALUES (
    'admin',
    'admin@sistema.local',
    '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/X4.K5REpNfHxQEzDq',
    'Administrador del Sistema',
    'grupo_motormexa',
    'Sistemas',
    1
);

-- Asignar rol de administrador
INSERT INTO usuario_rol (usuario_id, rol_id, asignado_por)
VALUES (1, 1, 1);

-- ============================================
-- CONFIGURACIÓN INICIAL DEL SISTEMA
-- ============================================
INSERT INTO configuracion_sistema (clave, valor, tipo, categoria, descripcion, editable) VALUES
('app_name', 'Sistema de Cancelaciones', 'string', 'general', 'Nombre de la aplicación', 1),
('app_logo', '/assets/img/logo.png', 'string', 'general', 'Ruta del logo', 1),
('session_timeout', '7200', 'number', 'seguridad', 'Tiempo de expiración de sesión en segundos', 1),
('max_login_attempts', '5', 'number', 'seguridad', 'Intentos máximos de login antes de bloqueo temporal', 1),
('items_per_page', '15', 'number', 'general', 'Elementos por página en listados', 1),
('timezone', 'America/Mexico_City', 'string', 'general', 'Zona horaria del sistema', 0),
('email_notifications', 'false', 'boolean', 'notificaciones', 'Enviar notificaciones por email', 1),
('email_from', 'noreply@sistema.local', 'string', 'notificaciones', 'Email remitente de notificaciones', 1);

-- ============================================
-- FIN DEL SCRIPT
-- ============================================
