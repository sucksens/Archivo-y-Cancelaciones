-- ============================================
-- Migración: Agregar permiso de descarga de facturas de NR (Financiera Mitsubishi)
-- Fecha: 2025-03-07
-- Descripción: Agrega un nuevo permiso que permite descargar facturas
--              solo del RFC receptor NFM0307091L9 (Financiera NR)
-- ============================================

USE cancelaciones;

-- ============================================
-- Insertar nuevo permiso: Descargar Facturas de NR
-- ============================================
INSERT INTO permisos (nombre, codigo, descripcion, modulo) VALUES
('Descargar Facturas de NR', 'facturas.download.nr', 
 'Descargar facturas solo del RFC receptor NFM0307091L9 (Financiera NR)', 
 'facturas');

-- ============================================
-- Nota: Este permiso debe asignarse manualmente a los roles que lo necesiten
-- usando la interfaz de gestión de roles o mediante INSERT en rol_permiso
-- ============================================

-- Ejemplo de asignación al rol 'Usuario' (descomentar si es necesario):
-- INSERT INTO rol_permiso (rol_id, permiso_id)
-- SELECT 3, id FROM permisos WHERE codigo = 'facturas.download.nr';

-- Ejemplo de asignación al rol 'Consulta' (descomentar si es necesario):
-- INSERT INTO rol_permiso (rol_id, permiso_id)
-- SELECT 4, id FROM permisos WHERE codigo = 'facturas.download.nr';
