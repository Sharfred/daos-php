<?php
require_once dirname(__DIR__) . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson(['error' => 'Método no permitido'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);

$nombre = $data['nombre'] ?? '';
$apellido_p = $data['apellido_p'] ?? '';
$apellido_m = $data['apellido_m'] ?? '';
$edad = $data['edad'] ?? '';

if (empty($nombre) || empty($apellido_p) || empty($apellido_m) || empty($edad)) {
    sendJson(['error' => 'Faltan campos requeridos'], 400);
}

try {
    $sql = "INSERT INTO clientes (nombre, apellido_p, apellido_m, edad, estado) VALUES (:nombre, :apellido_p, :apellido_m, :edad, 'activo')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nombre' => $nombre,
        ':apellido_p' => $apellido_p,
        ':apellido_m' => $apellido_m,
        ':edad' => $edad
    ]);
    sendJson(['mensaje' => 'Registro exitoso']);
} catch (Exception $e) {
    error_log("Error en registro público: " . $e->getMessage());
    sendJson(['error' => 'Error interno al registrar el cliente'], 500);
}
?>
