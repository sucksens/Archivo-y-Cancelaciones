-- ============================================
-- Script de Cambios para Sistema de Cancelaciones Detallado
-- Implementación de nuevas banderas de cancelación
-- ============================================

USE cancelaciones;

-- ============================================
-- AGREGAR NUEVA COLUMNA PARA SOLICITUD DE CANCELACIÓN
-- ============================================
ALTER TABLE factura_operaciones 
ADD COLUMN solicitada_cancelacion TINYINT(1) DEFAULT 0 
AFTER requiere_cancelacion;

-- ============================================
-- ELIMINAR TRIGGERS EXISTENTES (si existen)
-- ============================================
DROP TRIGGER IF EXISTS trg_check_cancelacion_flags_update;
DROP TRIGGER IF EXISTS trg_check_cancelacion_flags_insert;

-- ============================================
-- CREAR TRIGGERS PARA ACTUALIZACIÓN AUTOMÁTICA DE FECHA_CANCELACION
-- Se activa cuando las tres banderas están en positivo
-- ============================================
DELIMITER //

-- Trigger para operaciones UPDATE
CREATE TRIGGER trg_check_cancelacion_flags_update
BEFORE UPDATE ON factura_operaciones
FOR EACH ROW
BEGIN
    -- Verificar si las tres banderas están activas y antes no lo estaban
    IF NEW.solicitada_cancelacion = 1 AND NEW.cancelado_sat = 1 AND NEW.cancelado_sistema = 1
       AND (OLD.solicitada_cancelacion != 1 OR OLD.cancelado_sat != 1 OR OLD.cancelado_sistema != 1) THEN
        SET NEW.fecha_cancelacion = CURRENT_TIMESTAMP;
    END IF;
END//

-- Trigger para operaciones INSERT
CREATE TRIGGER trg_check_cancelacion_flags_insert
BEFORE INSERT ON factura_operaciones
FOR EACH ROW
BEGIN
    -- Si las tres banderas están activas al momento de inserción, establecer fecha de cancelación
    IF NEW.solicitada_cancelacion = 1 AND NEW.cancelado_sat = 1 AND NEW.cancelado_sistema = 1 THEN
        SET NEW.fecha_cancelacion = CURRENT_TIMESTAMP;
    END IF;
END//

DELIMITER ;

-- ============================================
-- ACTUALIZAR ÍNDICES (opcional para rendimiento)
-- ============================================
CREATE INDEX idx_cancelacion_flags ON factura_operaciones(
    solicitada_cancelacion, 
    cancelado_sat, 
    cancelado_sistema
);

-- ============================================
-- VERIFICACIÓN DE CAMBIOS
-- ============================================
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'cancelaciones' 
AND TABLE_NAME = 'factura_operaciones' 
AND COLUMN_NAME IN ('requiere_cancelacion', 'solicitada_cancelacion', 'cancelado_sat', 'cancelado_sistema', 'cancelada');

-- ============================================
-- NOTAS IMPORTANTES
-- ============================================
-- 1. El campo 'cancelada' se mantiene temporalmente para migración manual
-- 2. Los triggers aseguran que fecha_cancelacion se establezca automáticamente
-- 3. Los nuevos índices mejoran el rendimiento para consultas de filtrado
-- 4. La migración de datos debe hacerse manualmente según lo acordado
-- ============================================