<?php
// Database Configuration
$host = 'sql204.infinityfree.com';
$db_name = 'if0_41550083_bus2';
$username = 'if0_41550083';
$password = 'IpUser192168';

// Base URL detection (useful for if the app is in a subdirectory or root)
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domainName = $_SERVER['HTTP_HOST'];
    
    // Auto-detect the project root directory
    $script_name = $_SERVER['SCRIPT_NAME'];
    $dir = dirname($script_name);
    // If we're inside /admin or /user, we want to go up to the root
    $dir = preg_replace('/(\/admin|\/user|\/driver|\/api|\/config|\/layouts)$/', '', $dir);
    $baseUrl = $protocol . $domainName . ($dir == '/' ? '' : $dir);
    define('BASE_URL', rtrim($baseUrl, '/'));
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    // Set PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set default fetch mode to object
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
} catch (PDOException $e) {
    die("Database connection failed! Error: " . $e->getMessage());
}
?>
