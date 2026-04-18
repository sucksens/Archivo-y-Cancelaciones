<?php
/**
 * ValidationHelper - Validación de datos
 * Sistema de Tickets de Cancelación
 * 
 * @author José Ernesto Ruiz Valdivia
 * @version 1.0
 */

namespace App\Helpers;

class ValidationHelper
{
    private array $errors = [];
    private array $data = [];

    /**
     * Constructor
     * 
     * @param array $data Datos a validar
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Establecer datos a validar
     * 
     * @param array $data Datos
     * @return self
     */
    public function setData(array $data): self
    {
        $this->data = $data;
        $this->errors = [];
        return $this;
    }

    /**
     * Validar que un campo es requerido
     * 
     * @param string $field Campo
     * @param string|null $message Mensaje personalizado
     * @return self
     */
    public function required(string $field, ?string $message = null): self
    {
        if (!isset($this->data[$field]) || trim($this->data[$field]) === '') {
            $this->errors[$field] = $message ?? "El campo {$field} es requerido";
        }
        return $this;
    }

    /**
     * Validar email
     * 
     * @param string $field Campo
     * @param string|null $message Mensaje personalizado
     * @return self
     */
    public function email(string $field, ?string $message = null): self
    {
        if (isset($this->data[$field]) && !empty($this->data[$field])) {
            if (!filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
                $this->errors[$field] = $message ?? "El campo {$field} no es un email válido";
            }
        }
        return $this;
    }

    /**
     * Validar RFC mexicano
     * 
     * @param string $field Campo
     * @param string|null $message Mensaje personalizado
     * @return self
     */
    public function rfc(string $field, ?string $message = null): self
    {
        if (isset($this->data[$field]) && !empty($this->data[$field])) {
            $rfc = strtoupper(trim($this->data[$field]));
            
            // Patrón RFC mexicano (persona física o moral)
            //$pattern = '/^[A-ZÑ&]{3,4}\d{6}[A-V1-9][0-9A-Z]?[0-9A-Z]$/';
            $pattern = '/^([A-ZÑ&]{3,4}\d{6}[A-V1-9][0-9A-Z]?[0-9A-Z]|XAXX010101000|XEXX010101000)$/';

            
            if (!preg_match($pattern, $rfc)) {
                $this->errors[$field] = $message ?? "El RFC no tiene un formato válido";
            }
        }
        return $this;
    }

    /**
     * Validar UUID
     * 
     * @param string $field Campo
     * @param string|null $message Mensaje personalizado
     * @return self
     */
    public function uuid(string $field, ?string $message = null): self
    {
        if (isset($this->data[$field]) && !empty($this->data[$field])) {
            $uuid = strtolower(trim($this->data[$field]));
            $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/';
            
            if (!preg_match($pattern, $uuid)) {
                $this->errors[$field] = $message ?? "El UUID no tiene un formato válido";
            }
        }
        return $this;
    }

    /**
     * Validar longitud mínima
     * 
     * @param string $field Campo
     * @param int $min Longitud mínima
     * @param string|null $message Mensaje personalizado
     * @return self
     */
    public function minLength(string $field, int $min, ?string $message = null): self
    {
        if (isset($this->data[$field]) && strlen($this->data[$field]) < $min) {
            $this->errors[$field] = $message ?? "El campo {$field} debe tener al menos {$min} caracteres";
        }
        return $this;
    }

    /**
     * Validar longitud máxima
     * 
     * @param string $field Campo
     * @param int $max Longitud máxima
     * @param string|null $message Mensaje personalizado
     * @return self
     */
    public function maxLength(string $field, int $max, ?string $message = null): self
    {
        if (isset($this->data[$field]) && strlen($this->data[$field]) > $max) {
            $this->errors[$field] = $message ?? "El campo {$field} no debe exceder {$max} caracteres";
        }
        return $this;
    }

    /**
     * Validar que es numérico
     * 
     * @param string $field Campo
     * @param string|null $message Mensaje personalizado
     * @return self
     */
    public function numeric(string $field, ?string $message = null): self
    {
        if (isset($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field] = $message ?? "El campo {$field} debe ser numérico";
        }
        return $this;
    }

    /**
     * Validar valor mínimo
     * 
     * @param string $field Campo
     * @param float $min Valor mínimo
     * @param string|null $message Mensaje personalizado
     * @return self
     */
    public function min(string $field, float $min, ?string $message = null): self
    {
        if (isset($this->data[$field]) && floatval($this->data[$field]) < $min) {
            $this->errors[$field] = $message ?? "El campo {$field} debe ser mayor o igual a {$min}";
        }
        return $this;
    }

    /**
     * Validar que está en un conjunto de valores
     * 
     * @param string $field Campo
     * @param array $values Valores permitidos
     * @param string|null $message Mensaje personalizado
     * @return self
     */
    public function in(string $field, array $values, ?string $message = null): self
    {
        if (isset($this->data[$field]) && !in_array($this->data[$field], $values)) {
            $this->errors[$field] = $message ?? "El valor del campo {$field} no es válido";
        }
        return $this;
    }

    /**
     * Validar coincidencia de campos (ej: contraseña y confirmación)
     * 
     * @param string $field1 Primer campo
     * @param string $field2 Segundo campo
     * @param string|null $message Mensaje personalizado
     * @return self
     */
    public function matches(string $field1, string $field2, ?string $message = null): self
    {
        if (($this->data[$field1] ?? '') !== ($this->data[$field2] ?? '')) {
            $this->errors[$field1] = $message ?? "Los campos {$field1} y {$field2} no coinciden";
        }
        return $this;
    }

    /**
     * Validar patrón regex personalizado
     * 
     * @param string $field Campo
     * @param string $pattern Patrón regex
     * @param string|null $message Mensaje personalizado
     * @return self
     */
    public function pattern(string $field, string $pattern, ?string $message = null): self
    {
        if (isset($this->data[$field]) && !preg_match($pattern, $this->data[$field])) {
            $this->errors[$field] = $message ?? "El formato del campo {$field} no es válido";
        }
        return $this;
    }

    /**
     * Validar fecha
     * 
     * @param string $field Campo
     * @param string $format Formato esperado
     * @param string|null $message Mensaje personalizado
     * @return self
     */
    public function date(string $field, string $format = 'Y-m-d', ?string $message = null): self
    {
        if (isset($this->data[$field]) && !empty($this->data[$field])) {
            $date = \DateTime::createFromFormat($format, $this->data[$field]);
            if (!$date || $date->format($format) !== $this->data[$field]) {
                $this->errors[$field] = $message ?? "El campo {$field} no es una fecha válida";
            }
        }
        return $this;
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
     * Verificar si la validación pasó
     * 
     * @return bool
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }

    /**
     * Obtener todos los errores
     * 
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Obtener error de un campo específico
     * 
     * @param string $field Campo
     * @return string|null
     */
    public function getError(string $field): ?string
    {
        return $this->errors[$field] ?? null;
    }

    /**
     * Obtener primer error
     * 
     * @return string|null
     */
    public function getFirstError(): ?string
    {
        return reset($this->errors) ?: null;
    }

    /**
     * Agregar error manualmente
     * 
     * @param string $field Campo
     * @param string $message Mensaje
     * @return self
     */
    public function addError(string $field, string $message): self
    {
        $this->errors[$field] = $message;
        return $this;
    }

    /**
     * Sanitizar string para HTML
     * 
     * @param string $value Valor
     * @return string
     */
    public static function sanitize(string $value): string
    {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitizar array completo
     * 
     * @param array $data Datos
     * @return array
     */
    public static function sanitizeArray(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = self::sanitizeArray($value);
            } else {
                $sanitized[$key] = self::sanitize($value);
            }
        }
        return $sanitized;
    }

    /**
     * Limpiar RFC (mayúsculas y sin espacios)
     * 
     * @param string $rfc RFC
     * @return string
     */
    public static function cleanRfc(string $rfc): string
    {
        return strtoupper(preg_replace('/\s+/', '', $rfc));
    }

    /**
     * Limpiar UUID (minúsculas)
     * 
     * @param string $uuid UUID
     * @return string
     */
    public static function cleanUuid(string $uuid): string
    {
        return strtolower(trim($uuid));
    }
   
    /**
     * Convertir las fechas de bbj  a el formato de mysql
     * 
     * @param string $date DATE
     * @return string
     */
    public static function BbjDateToMysqlDate(string $date): string 
    {
        // Verifica que la longitud de la fecha sea correcta
        if (strlen($date) !== 8) {
            throw new InvalidArgumentException('La fecha debe estar en formato YYYYMMDD.');
        }
        
        // Extrae el año, mes y día
        $year = substr($date, 0, 4);
        $month = substr($date, 4, 2);
        $day = substr($date, 6, 2);
        
        // Forma la nueva fecha en formato YYYY-MM-DD
        return "$year-$month-$day";
    }
}
