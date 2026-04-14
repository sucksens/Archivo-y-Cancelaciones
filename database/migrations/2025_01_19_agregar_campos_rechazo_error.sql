-- ============================================
-- Migration: Agregar campos de rechazo por error
-- Tabla: tickets_cancelacion
-- Fecha: 2025-01-19
-- ============================================

USE cancelaciones;

-- Agregar campo para marcar si el rechazo fue por error
ALTER TABLE tickets_cancelacion 
ADD COLUMN rechazado_por_error TINYINT(1) DEFAULT 0 
COMMENT 'Indica si el ticket fue rechazado por error del sistema' 
AFTER estado;

-- Agregar campo para el tipo de error
ALTER TABLE tickets_cancelacion 
ADD COLUMN tipo_error_rechazo ENUM('tipo_cancelacion', 'archivo_no_coincide') NULL 
COMMENT 'Tipo de error que causó el rechazo' 
AFTER rechazado_por_error;

-- NOTA: No agregamos campo comentario_rechazo porque usaremos el sistema existente de comentarios (tabla comentarios_ticket)

-- Crear índices para búsquedas frecuentes
ALTER TABLE tickets_cancelacion 
ADD INDEX idx_rechazado_error (rechazado_por_error);

-- ============================================
-- Completado: 2025-01-19
-- ============================================
