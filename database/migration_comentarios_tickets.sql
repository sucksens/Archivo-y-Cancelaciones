-- ============================================
-- MIGRACIÓN: Sistema de Comentarios en Tickets
-- ============================================
-- Fecha: 2026-01-17
-- Descripción: Agrega sistema de comentarios para tickets de cancelación
-- ============================================

-- 1. Crear tabla de comentarios
CREATE TABLE IF NOT EXISTS comentarios_ticket (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    usuario_id INT NOT NULL,
    comentario TEXT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets_cancelacion(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_ticket (ticket_id),
    INDEX idx_fecha (fecha_creacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Insertar permiso para agregar comentarios
INSERT INTO permisos (nombre, codigo, descripcion, modulo) VALUES
('Agregar Comentarios', 'tickets.comments.add', 'Puede agregar comentarios a tickets de cancelación', 'tickets');

-- 3. Asignar permiso a Administrador (rol_id=1)
INSERT INTO rol_permiso (rol_id, permiso_id)
SELECT 1, id FROM permisos WHERE codigo = 'tickets.comments.add';

-- 4. Asignar permiso a Supervisor Cancelaciones (rol_id=2)
INSERT INTO rol_permiso (rol_id, permiso_id)
SELECT 2, id FROM permisos WHERE codigo = 'tickets.comments.add';
