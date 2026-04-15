-- ============================================
-- Migration: Agregar permiso para corregir errores de rechazo
-- Tablas: permisos, rol_permiso
-- Fecha: 2025-01-19
-- ============================================

USE cancelaciones;

-- Insertar nuevo permiso para corregir errores de rechazo
INSERT INTO permisos (nombre, codigo, descripcion, modulo) 
VALUES (
    'Corregir Errores de Rechazo',
    'tickets.correct_rejection_errors',
    'Permite corregir errores de tipo de cancelación o archivo incorrecto en tickets rechazados',
    'tickets'
) 
ON DUPLICATE KEY UPDATE 
    descripcion = VALUES(descripcion);

-- Asignar el permiso al rol Supervisor
-- Asumimos que el rol Supervisor tiene id=2 (verificar en datos iniciales)
INSERT INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id 
FROM roles r 
CROSS JOIN permisos p 
WHERE r.nombre = 'Supervisor' 
  AND p.codigo = 'tickets.correct_rejection_errors'
  AND NOT EXISTS (
    SELECT 1 FROM rol_permiso rp 
    WHERE rp.rol_id = r.id AND rp.permiso_id = p.id
  );

-- ============================================
-- Completado: 2025-01-19
-- ============================================
