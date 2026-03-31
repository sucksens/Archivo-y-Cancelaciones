<?php
/**
 * Clase Database - Singleton para conexión PDO
 * Sistema de Tickets de Cancelación
 * 
 * @author José Ernesto Ruiz Valdivia
 * @version 1.0
 */

namespace App\Core;

use PDO;
use PDOException;
use Exception;

class Database
{
    private static ?Database $instance = null;
    private PDO $connection;
    private array $config;

    /**
     * Constructor privado para implementar Singleton
     */
    private function __construct()
    {
        $this->config = require APP_PATH . '/config/database.php';
        $this->connect();
    }

    /**
     * Prevenir clonación del objeto
     */
    private function __clone() {}

    /**
     * Obtener instancia única de Database
     * 
     * @return Database
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Establecer conexión a la base de datos
     * 
     * @throws Exception Si la conexión falla
     */
    private function connect(): void
    {
        try {
            $dsn = sprintf(
                '%s:host=%s;port=%s;dbname=%s;charset=%s',
                $this->config['driver'],
                $this->config['host'],
                $this->config['port'],
                $this->config['database'],
                $this->config['charset']
            );

            $this->connection = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                $this->config['options']
            );
        } catch (PDOException $e) {
            $this->logError('Error de conexión: ' . $e->getMessage());
            throw new Exception('Error al conectar con la base de datos');
        }
    }

    /**
     * Obtener la conexión PDO
     * 
     * @return PDO
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }

    /**
     * Ejecutar una consulta preparada
     * 
     * @param string $sql Consulta SQL
     * @param array $params Parámetros para la consulta
     * @return \PDOStatement
     */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            $this->logError("Error en consulta: {$sql} - " . $e->getMessage());
            throw new Exception('Error al ejecutar la consulta');
        }
    }

    /**
     * Preparar una consulta
     * 
     * @param string $sql Consulta SQL
     * @return \PDOStatement
     */
    public function prepare(string $sql): \PDOStatement
    {
        return $this->connection->prepare($sql);
    }

    /**
     * Obtener el último ID insertado
     * 
     * @return string
     */
    public function lastInsertId(): string
    {
        return $this->connection->lastInsertId();
    }

    /**
     * Iniciar una transacción
     * 
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    /**
     * Confirmar una transacción
     * 
     * @return bool
     */
    public function commit(): bool
    {
        return $this->connection->commit();
    }

    /**
     * Revertir una transacción
     * 
     * @return bool
     */
    public function rollBack(): bool
    {
        return $this->connection->rollBack();
    }

    /**
     * Obtener un solo registro
     * 
     * @param string $sql Consulta SQL
     * @param array $params Parámetros
     * @return array|null
     */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Obtener todos los registros
     * 
     * @param string $sql Consulta SQL
     * @param array $params Parámetros
     * @return array
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Obtener un valor escalar
     * 
     * @param string $sql Consulta SQL
     * @param array $params Parámetros
     * @return mixed
     */
    public function fetchColumn(string $sql, array $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchColumn();
    }

    /**
     * Registrar error en log
     * 
     * @param string $message Mensaje de error
     */
    private function logError(string $message): void
    {
        $logFile = BASE_PATH . '/logs/database.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}" . PHP_EOL;
        
        error_log($logMessage, 3, $logFile);
    }
}
