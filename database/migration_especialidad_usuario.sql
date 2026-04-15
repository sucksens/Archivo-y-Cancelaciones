-- ============================================
-- Migración: Agregar campo especialidad_usuario a tabla usuarios
-- Sistema de Tickets de Cancelación
-- Fecha: 2025-01-15
-- ============================================

USE cancelaciones;

-- 1. Agregar columna especialidad_usuario
ALTER TABLE usuarios 
ADD COLUMN especialidad_usuario ENUM('ambos', 'autos_nuevos', 'seminuevos') DEFAULT 'ambos' 
AFTER departamento;

-- 2. Establecer valor por defecto para usuarios existentes
UPDATE usuarios SET especialidad_usuario = 'ambos' WHERE especialidad_usuario IS NULL;

-- 3. Agregar índice para mejorar rendimiento en consultas
ALTER TABLE usuarios ADD INDEX idx_especialidad_usuario (especialidad_usuario);

-- 4. Agregar comentarios a la columna
ALTER TABLE usuarios 
MODIFY COLUMN especialidad_usuario ENUM('ambos', 'autos_nuevos', 'seminuevos') DEFAULT 'ambos' 
COMMENT 'Especialidad del usuario: ambos, autos_nuevos o seminuevos. Usado para filtrar contenido en rol Consulta';

-- ============================================
-- Verificación
-- ============================================

-- Verificar estructura de la tabla
DESCRIBE usuarios;

-- Contar usuarios por especialidad
SELECT especialidad_usuario, COUNT(*) as total 
FROM usuarios 
GROUP BY especialidad_usuario;