<?php
// ============================================================
//  CyberShield — API Configuration
//  Edit DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS below
// ============================================================

define('DB_HOST', 'localhost');
define('DB_PORT', '3308');        // Change to 3306 if your MySQL uses default port
define('DB_NAME', 'cybershield_db');
define('DB_USER', 'root');
define('DB_PASS', '');            // Add your MySQL password here if set

// CORS Headers — allow frontend to talk to API
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database connection
try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed. Check WAMP is running and port is correct.",
        "error"   => $e->getMessage()
    ]);
    exit();
}
