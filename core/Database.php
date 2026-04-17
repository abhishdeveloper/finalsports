<?php

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        // We will read from a config file eventually, but for now hardcode for testing/development
        // or expect them to be defined. Let's include a config.php
        require_once CONFIG_DIR . '/database.php';

        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, // Essential for bank-grade security (real prepared statements)
        ];

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (\PDOException $e) {
            // In a real bank-grade app, we wouldn't show the real error to the user, we'd log it.
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }

    // Prevent cloning and unserialization for Singleton
    private function __clone() {}
    public function __wakeup() {
        throw new \Exception("Cannot unserialize a singleton.");
    }
}
