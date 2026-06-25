<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$host     = getenv('MYSQLHOST') ?: (getenv('MYSQL_HOST') ?: 'localhost');
$dbname   = getenv('MYSQLDATABASE') ?: (getenv('MYSQL_DATABASE') ?: 'daos_gym');
$usuario  = getenv('MYSQLUSER') ?: (getenv('MYSQL_USER') ?: 'root');
$password = getenv('MYSQLPASSWORD') ?: (getenv('MYSQL_PASSWORD') ?: '');
$port     = getenv('MYSQLPORT') ?: (getenv('MYSQL_PORT') ?: '3306');
$charset  = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";

$opciones = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $usuario, $password, $opciones);
} catch (PDOException $e) {
    // Log the actual error to the server error log, but hide it from the user
    error_log("Database connection error: " . $e->getMessage());
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(["error" => "Error interno del servidor. No se pudo conectar a la base de datos."]);
    exit();
}

// Función auxiliar para chequear sesión
function requireAuth() {
    // Detección de HTTPS segura para proxies (Railway)
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
                (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
                
    // Secure session cookies before starting session
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
    
    // Como el frontend enviaba un Bearer token, si se nos pide una validación estricta de sesión:
    if (!isset($_SESSION['admin_id'])) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['mensaje' => 'No autorizado']);
        exit();
    }
}

// Configurar manejador global de excepciones para evitar filtrado de errores
set_exception_handler(function($e) {
    error_log("Excepción no capturada: " . $e->getMessage());
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => 'Error interno en el servidor']);
    exit();
});

// Función para enviar json
function sendJson($data, $statusCode = 200) {
    header('Content-Type: application/json');
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}
?>
