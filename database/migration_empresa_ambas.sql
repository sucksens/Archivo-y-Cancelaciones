-- Migration: Agregar opción 'ambas' al campo empresa de usuarios
-- Archivo: migration_empresa_ambas.sql
-- Descripción: Permite que usuarios tengan acceso a ambas empresas

USE cancelaciones;

-- Modificar el campo empresa en la tabla usuarios para incluir 'ambas'
ALTER TABLE usuarios 
MODIFY COLUMN empresa ENUM('grupo_motormexa', 'automotriz_motormexa', 'ambas') NOT NULL;

-- NOTA: No se modifica tickets_cancelacion ni facturas_archivo
-- porque los usuarios con empresa='ambas' solo pueden CONSULTAR, no crear.
-- Los tickets y facturas siempre tendrán una empresa específica.

-- Opcional: Asignar 'ambas' a usuarios específicos (descomentar y editar IDs)
-- UPDATE usuarios 
-- SET empresa = 'ambas' 
-- WHERE id IN (1, 2, 3); -- Reemplazar con IDs de usuarios que deben tener ambas empresas