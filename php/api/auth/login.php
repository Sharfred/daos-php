<?php
require_once dirname(__DIR__) . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson(['mensaje' => 'Método no permitido'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);

$usuario = $data['usuario'] ?? '';
$password = $data['password'] ?? '';

if (empty($usuario) || empty($password)) {
    sendJson(['mensaje' => 'Faltan credenciales'], 400);
}

try {
    $stmt = $pdo->prepare("SELECT id, password_hash FROM usuarios WHERE usuario = :usuario");
    $stmt->execute([':usuario' => $usuario]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        session_start();
        $_SESSION['admin_id'] = $user['id'];
        // Devolvemos un token dummy para que app.js no falle al hacer setToken(data.token)
        sendJson(['mensaje' => 'Login exitoso', 'token' => session_id()]);
    } else {
        sendJson(['mensaje' => 'Usuario o contraseña incorrectos'], 401);
    }
} catch (Exception $e) {
    error_log("Error en login: " . $e->getMessage());
    sendJson(['mensaje' => 'Error interno en el servidor'], 500);
}
?>
