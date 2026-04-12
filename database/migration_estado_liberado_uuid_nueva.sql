-- ============================================
-- Migración: Estado Liberado y UUID Factura Nueva
-- Fecha: 2025-01-16
-- Descripción: Agrega estado 'liberado' para refacturaciones y campo para UUID de factura nueva
-- ============================================

-- Agregar campo para UUID de factura nueva en tickets de refacturación
ALTER TABLE tickets_cancelacion 
ADD COLUMN uuid_factura_nueva CHAR(36) NULL AFTER tipo_cancelacion,
ADD INDEX idx_uuid_factura_nueva (uuid_factura_nueva);

-- Actualizar el ENUM de estado para agregar 'liberado'
-- Se mantiene 'completado' por compatibilidad con datos existentes
ALTER TABLE tickets_cancelacion 
MODIFY COLUMN estado ENUM('pendiente', 'en_revision', 'proceso_cancelacion', 'cancelado', 'rechazado', 'completado', 'liberado') DEFAULT 'pendiente';
