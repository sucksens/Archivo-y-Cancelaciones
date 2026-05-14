-- ============================================
-- Migración: Agregar permiso de generación de padrón V1J AUTO
-- Fecha: 2026-03-14
-- Descripción: Agrega un nuevo permiso que permite generar el PDF del
--              formulario de padrón V1J AUTO para facturas
-- ============================================

USE cancelaciones;

-- ============================================
-- Insertar nuevo permiso: Generar Padrón V1J AUTO
-- ============================================
INSERT INTO permisos (nombre, codigo, descripcion, modulo) VALUES
('Generar Padrón V1J AUTO', 'facturas.padron.generar', 
 'Generar el formulario PDF de padrón V1J AUTO para facturas', 
 'facturas');

-- ============================================
-- Nota: Este permiso debe asignarse manualmente a los roles que lo necesiten
-- usando la interfaz de gestión de roles o mediante INSERT en rol_permiso
-- ============================================

-- Ejemplo de asignación al rol 'Administrador' (descomentar si es necesario):
-- INSERT INTO rol_permiso (rol_id, permiso_id)
-- SELECT 1, id FROM permisos WHERE codigo = 'facturas.padron.generar';

-- Ejemplo de asignación al rol 'Supervisor' (descomentar si es necesario):
-- INSERT INTO rol_permiso (rol_id, permiso_id)
-- SELECT 2, id FROM permisos WHERE codigo = 'facturas.padron.generar';

-- Ejemplo de asignación al rol 'Usuario' (descomentar si es necesario):
-- INSERT INTO rol_permiso (rol_id, permiso_id)
-- SELECT 3, id FROM permisos WHERE codigo = 'facturas.padron.generar';

-- Ejemplo de asignación al rol 'Consulta' (descomentar si es necesario):
-- INSERT INTO rol_permiso (rol_id, permiso_id)
-- SELECT 4, id FROM permisos WHERE codigo = 'facturas.padron.generar';
