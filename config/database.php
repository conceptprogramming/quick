<?php

class Database
{
    private static ?PDO $instance = null;

    private static function connect(): void
    {
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $name = $_ENV['DB_NAME'] ?? 'quickchatpdf';
        $user = $_ENV['DB_USER'] ?? 'root';
        $pass = $_ENV['DB_PASS'] ?? '';

        $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";

        self::$instance = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => false, // never reuse stale persistent connections
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci; SET time_zone = '+00:00'",
        ]);
    }

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::connect();
        }
        return self::$instance;
    }

    /**
     * Force a fresh connection — call this after long OCR/AI operations
     * before any DB write.
     */
    public static function reconnect(): void
    {
        self::$instance = null;
        self::connect();
    }

    /**
     * Ping MySQL to check if the connection is still alive.
     * Returns false if the connection has gone away.
     */
    public static function isAlive(): bool
    {
        try {
            self::getInstance()->query('SELECT 1');
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * Returns a live PDO instance — reconnects automatically if stale.
     * Use this instead of getInstance() after heavy processing.
     */
    public static function getAliveInstance(): PDO
    {
        if (!self::isAlive()) {
            self::reconnect();
        }
        return self::$instance;
    }

    // Prevent cloning & unserialization
    private function __clone() {}
    public function __wakeup() {}
}
