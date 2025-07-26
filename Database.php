<?php
class Database {
    private $conn;

    public function __construct($config) {
        $this->conn = new mysqli(
            $config['host'],
            $config['user'],
            $config['pass'],
            $config['name']
        );
        if ($this->conn->connect_error) {
            die('Database connection failed: ' . $this->conn->connect_error);
        }
        $this->conn->set_charset('utf8mb4');
    }

    public function query($sql) {
        return $this->conn->query($sql);
    }

    public function prepare($sql) {
        return $this->conn->prepare($sql);
    }

    public function insert_id() {
        return $this->conn->insert_id;
    }

    public function escape($value) {
        return $this->conn->real_escape_string($value);
    }

    public function close() {
        $this->conn->close();
    }
}