<?php
/**
 * FileUploadHelper - Manejo seguro de uploads
 * Sistema de Tickets de Cancelación
 * 
 * @author José Ernesto Ruiz Valdivia
 * @version 1.0
 */

namespace App\Helpers;

class FileUploadHelper
{
    private array $errors = [];
    private ?string $uploadedPath = null;

    /**
     * Subir archivo de autorización
     * 
     * @param array $file Datos del archivo ($_FILES['campo'])
     * @param string $subdir Subdirectorio destino
     * @return string|null Ruta del archivo subido o null si falla
     */
    public function upload(array $file, string $subdir = 'autorizaciones'): ?string
    {
        $this->errors = [];
        $this->uploadedPath = null;

        // Validar que el array de archivo existe
        if (!isset($file['error'])) {
            $this->errors[] = 'No se recibió información del archivo';
            return null;
        }

        // Validar errores de upload (PHP reporta errores aquí antes de llenar tmp_name si falló por tamaño, etc)
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = $this->getUploadErrorMessage($file['error']);
            return null;
        }

        // Validar que hay un archivo temporal
        if (empty($file['tmp_name'])) {
            $this->errors[] = 'No se seleccionó ningún archivo';
            return null;
        }

        // Validar tamaño
        if ($file['size'] > MAX_FILE_SIZE) {
            $maxMb = MAX_FILE_SIZE / 1024 / 1024;
            $this->errors[] = "El archivo excede el tamaño máximo permitido ({$maxMb}MB)";
            return null;
        }

        // Validar tipo MIME
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        
        if (!in_array($mimeType, ALLOWED_FILE_TYPES)) {
            $this->errors[] = 'Tipo de archivo no permitido. Solo se aceptan PDF y XML';
            return null;
        }

        // Validar extensión
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ALLOWED_EXTENSIONS)) {
            $this->errors[] = 'Extensión de archivo no permitida';
            return null;
        }

        // Crear directorio si no existe
        $uploadDir = UPLOADS_PATH . '/' . $subdir;
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                $this->errors[] = 'Error al crear directorio de uploads';
                return null;
            }
        }

        // Generar nombre único
        $uniqueName = $this->generateUniqueName($extension);
        $destination = $uploadDir . '/' . $uniqueName;

        // Mover archivo
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $this->errors[] = 'Error al mover el archivo subido';
            return null;
        }

        // Almacenar ruta relativa para guardar en BD
        $this->uploadedPath = $subdir . '/' . $uniqueName;
        
        return $this->uploadedPath;
    }

    /**
     * Generar nombre único para archivo
     * 
     * @param string $extension Extensión del archivo
     * @return string Nombre único
     */
    private function generateUniqueName(string $extension): string
    {
        $timestamp = date('YmdHis');
        $random = bin2hex(random_bytes(8));
        return "{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Obtener mensaje de error de upload
     * 
     * @param int $errorCode Código de error
     * @return string Mensaje
     */
    private function getUploadErrorMessage(int $errorCode): string
    {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return 'El archivo excede el tamaño máximo permitido por el servidor';
            case UPLOAD_ERR_FORM_SIZE:
                return 'El archivo excede el tamaño máximo permitido por el formulario';
            case UPLOAD_ERR_PARTIAL:
                return 'El archivo se subió parcialmente';
            case UPLOAD_ERR_NO_FILE:
                return 'No se subió ningún archivo';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Falta directorio temporal en el servidor';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Error al escribir el archivo en disco';
            case UPLOAD_ERR_EXTENSION:
                return 'Una extensión de PHP detuvo la carga';
            default:
                return 'Error desconocido al subir el archivo';
        }
    }

    /**
     * Verificar si hay errores
     * 
     * @return bool
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Obtener errores
     * 
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Obtener primer error
     * 
     * @return string|null
     */
    public function getFirstError(): ?string
    {
        return $this->errors[0] ?? null;
    }

    /**
     * Obtener ruta del archivo subido
     * 
     * @return string|null
     */
    public function getUploadedPath(): ?string
    {
        return $this->uploadedPath;
    }

    /**
     * Eliminar archivo
     * 
     * @param string $relativePath Ruta relativa del archivo
     * @return bool
     */
    public function delete(string $relativePath): bool
    {
        $fullPath = UPLOADS_PATH . '/' . $relativePath;
        
        if (file_exists($fullPath) && is_file($fullPath)) {
            return unlink($fullPath);
        }
        
        return false;
    }

    /**
     * Obtener URL pública del archivo
     * 
     * @param string $relativePath Ruta relativa
     * @return string
     */
    public static function getPublicUrl(string $relativePath): string
    {
        return BASE_URL . 'assets/uploads/' . $relativePath;
    }

    /**
     * Verificar si un archivo existe
     * 
     * @param string $relativePath Ruta relativa
     * @return bool
     */
    public static function exists(string $relativePath): bool
    {
        $fullPath = UPLOADS_PATH . '/' . $relativePath;
        return file_exists($fullPath) && is_file($fullPath);
    }

    /**
     * Obtener información del archivo
     * 
     * @param string $relativePath Ruta relativa
     * @return array|null
     */
    public static function getFileInfo(string $relativePath): ?array
    {
        $fullPath = UPLOADS_PATH . '/' . $relativePath;
        
        if (!file_exists($fullPath)) {
            return null;
        }

        return [
            'name' => basename($relativePath),
            'size' => filesize($fullPath),
            'size_formatted' => self::formatFileSize(filesize($fullPath)),
            'extension' => pathinfo($relativePath, PATHINFO_EXTENSION),
            'mime_type' => mime_content_type($fullPath),
            'modified' => filemtime($fullPath)
        ];
    }

    /**
     * Formatear tamaño de archivo
     * 
     * @param int $bytes Bytes
     * @return string
     */
    public static function formatFileSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }
}
