<?php
require_once 'config.php';

class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $conn;

    public function connect() {
        if ($this->conn) {
            return $this->conn;
        }

        // Opciones de PDO para la conexión
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        // Se define la cadena de conexión (DSN) con el charset
        $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";

        try {
            // Se crea la conexión PDO
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);

            // ================================================================
            // LÍNEA ADICIONAL PARA FORZAR LA CODIFICACIÓN UTF-8
            // Este comando es una segunda capa de seguridad para asegurar la codificación correcta.
            // ================================================================
            $this->conn->exec('set names utf8mb4');

            return $this->conn;
            
        } catch (PDOException $e) {
            error_log("Error de conexión DB: " . $e->getMessage());
            throw new Exception("Error interno del servidor. No se pudo conectar a la base de datos.");
        }
    }

    public function getConnection() {
        return $this->connect();
    }
}