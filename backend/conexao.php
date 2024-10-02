<?php
// conexao.php

if (!class_exists('Database')) {
    class Database {
        private $host = 'localhost';
        private $db_name = 'db_name';
        private $username = 'db_user';
        private $password = 'password';
        private $conn_mysqli;
        private $conn_pdo;

        public function __construct() {
            // Conexão MySQLi
            $this->conn_mysqli = new mysqli($this->host, $this->username, $this->password, $this->db_name);

            // Verifica se houve um erro na conexão MySQLi
            if ($this->conn_mysqli->connect_error) {
                die("Conexão MySQLi falhou: " . $this->conn_mysqli->connect_error);
            }

            // Conexão PDO
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
            try {
                $this->conn_pdo = new PDO($dsn, $this->username, $this->password);
                $this->conn_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Conexão PDO falhou: " . $e->getMessage());
            }
        }

        public function getConnection() {
            return $this->conn_mysqli;
        }

        public function getPdoConnection() {
            return $this->conn_pdo;
        }

        public function __destruct() {
            if ($this->conn_mysqli && $this->conn_mysqli->ping()) {
                $this->conn_mysqli->close();
            }

            $this->conn_pdo = null;
        }
    }
}