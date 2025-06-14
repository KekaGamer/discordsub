<?php
require_once 'config.php';

class Database {
    private $host = 'localhost';
    private $db_name = 'discord_subs';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function connect() {
        if($this->conn) {
            return $this->conn;
        }

        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4",
                $this->username, 
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
            
            // Verificar conexión con consulta simple
            $this->conn->query("SELECT 1");
            
            return $this->conn;
        } catch(PDOException $e) {
            error_log("Error de conexión DB: " . $e->getMessage());
            throw new Exception("No se pudo conectar a la base de datos. Por favor intenta más tarde.");
        }
    }

    public function getConnection() {
        return $this->connect();
    }
}