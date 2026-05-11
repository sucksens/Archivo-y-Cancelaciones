-- ============================================
-- Migración: Agregar permiso de descarga de facturas por vendedor
-- Fecha: 2025-03-06
-- Descripción: Agrega un nuevo permiso que permite descargar facturas
--              solo del vendedor asignado según la empresa del usuario
-- ============================================

USE cancelaciones;

-- ============================================
-- Insertar nuevo permiso: Descargar Facturas de Vendedor
-- ============================================
INSERT INTO permisos (nombre, codigo, descripcion, modulo) VALUES
('Descargar Facturas de Vendedor', 'facturas.download.vendedor', 
 'Descargar facturas solo del vendedor asignado según empresa (CDIS para grupo_motormexa, CDI1 para automotriz_motormexa)', 
 'facturas');

-- ============================================
-- Nota: Este permiso debe asignarse manualmente a los roles que lo necesiten
-- usando la interfaz de gestión de roles o mediante INSERT en rol_permiso
-- ============================================

-- Ejemplo de asignación al rol 'Usuario' (descomentar si es necesario):
-- INSERT INTO rol_permiso (rol_id, permiso_id)
-- SELECT 3, id FROM permisos WHERE codigo = 'facturas.download.vendedor';

-- Ejemplo de asignación al rol 'Consulta' (descomentar si es necesario):
-- INSERT INTO rol_permiso (rol_id, permiso_id)
-- SELECT 4, id FROM permisos WHERE codigo = 'facturas.download.vendedor';
