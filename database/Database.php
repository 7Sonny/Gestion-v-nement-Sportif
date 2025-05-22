<?php
namespace Database;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;
    private static string $host = '127.0.0.1';
    private static string $port = '3306';
    private static string $dbname = 'sportevent';
    private static string $charset = 'utf8';
    private static string $pseudo = 'root';
    private static string $password = '';

    private function __construct() {}

    public static function getInstance(): PDO {
        if (is_null(self::$instance)) {
            try {
                self::$instance = new PDO(
                    "mysql:host=" . self::$host . ";port=" . self::$port . ";dbname=" . self::$dbname . ";charset=" . self::$charset,
                    self::$pseudo,
                    self::$password,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                die("Erreur dans Database.php : " . $e->getMessage());
            }
        }
        return self::$instance;
    }
}