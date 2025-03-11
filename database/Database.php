<?php
namespace Database;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;
    private static string $dsn = 'mysql:host=127.0.0.1;port=3306;dbname=sportevent;charset=utf8';
    private static string $pseudo = 'root';
    private static string $password = '';

    private function __construct() {}

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            try {
               
                self::$instance = new PDO(self::$dsn, self::$pseudo, self::$password);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                
            } catch (PDOException $e) {
                die("❌ Erreur dans Database.php : " . $e->getMessage());
            }
        }
        return self::$instance;
    }
}