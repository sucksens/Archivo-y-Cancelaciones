<?php
/**
 * Clase DatabaseBBj - Para conexiones BBj ODBC
 * Adaptada para evitar bugs del driver BBj ODBC
 * 
 * @author José Ernesto Ruiz Valdivia
 * @version 1.0
 */

namespace App\Core;

use PDO;
use PDOException;
use Exception;

class DatabaseBBj
{
    private static array $instances = [];
    private PDO $connection;
    private string $database;
    private string $server = '200.1.1.240';
    private string $username = 'basis';
    private string $password = 'akjeeft';

    /**
     * Constructor privado para implementar Singleton por base de datos
     */
    private function __construct(string $database)
    {
        $this->database = $database;
        $this->connect();
    }

    private function __clone() {}

    /**
     * Obtener instancia única por base de datos
     * 
     * @param string $database Nombre de la base de datos BBj
     * @return DatabaseBBj
     */
    public static function getInstance(string $database): DatabaseBBj
    {
        if (!isset(self::$instances[$database])) {
            self::$instances[$database] = new self($database);
        }
        return self::$instances[$database];
    }

    /**
     * Establecer conexión a BBj ODBC
     */
    private function connect(): void
    {
        try {
            $dsn = "odbc:Driver={BBj ODBC Driver};Server={$this->server};Database={$this->database}";
            
            $this->connection = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (PDOException $e) {
            $this->logError('Error de conexión BBj: ' . $e->getMessage());
            throw new Exception('Error al conectar con BBj: ' . $this->database);
        }
    }

    /**
     * Obtener la conexión PDO
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }

    /**
     * Ejecutar consulta - campos pequeños primero, fecha al final
     */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            $this->logError("Error en consulta: {$sql} - " . $e->getMessage());
            throw new Exception('Error al ejecutar la consulta BBj');
        }
    }

    /**
     * Obtener un solo registro
     */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Obtener todos los registros
     * ADVERTENCIA: Usar con LIMIT para evitar problemas de memoria
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Obtener valor escalar (COUNT, SUM, etc.)
     */
    public function fetchColumn(string $sql, array $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchColumn();
    }

    /**
     * Iterar resultados uno por uno (más seguro para memoria)
     */
    public function fetchIterator(string $sql, array $params = []): \Generator
    {
        $stmt = $this->query($sql, $params);
        while ($row = $stmt->fetch()) {
            yield $row;
        }
    }

    /**
     * Cerrar conexión
     */
    public function close(): void
    {
        unset($this->connection);
        unset(self::$instances[$this->database]);
    }

    /**
     * Cerrar todas las conexiones
     */
    public static function closeAll(): void
    {
        foreach (self::$instances as $db => $instance) {
            $instance->close();
        }
        self::$instances = [];
    }

    private function logError(string $message): void
    {
        $logFile = BASE_PATH . '/logs/database.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        error_log("[{$timestamp}] {$message}" . PHP_EOL, 3, $logFile);
    }
}
