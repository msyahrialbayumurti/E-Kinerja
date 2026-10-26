<?php
/**
 * database.php - PDO Database Connection
 * Singleton pattern untuk koneksi database
 */

if (!defined('EKINERJA_DEBUG')) {
    define('EKINERJA_DEBUG', false);
}
ini_set('display_errors', EKINERJA_DEBUG ? '1' : '0');
ini_set('display_startup_errors', EKINERJA_DEBUG ? '1' : '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

$host = getenv('EKINERJA_DB_HOST') ?: 'localhost';
$dbname = getenv('EKINERJA_DB_NAME') ?: 'ekinerja_db';
$username = getenv('EKINERJA_DB_USER') ?: 'root';
$password = getenv('EKINERJA_DB_PASS') ?: '';
$charset = 'utf8mb4';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=$charset",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    // Log error untuk debugging, tapi jangan expose detail ke user
    error_log("Database Error: " . $e->getMessage());
    $pdo = null;
}
?>
