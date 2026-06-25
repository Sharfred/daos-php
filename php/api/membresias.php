<?php
require_once __DIR__ . '/db.php';
requireAuth();

$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;

if ($method === 'GET') {
    if ($id) {
        $stmt = $pdo->prepare("SELECT * FROM membresias WHERE id_membresia = ? AND estado = 'activo'");
        $stmt->execute([$id]);
        $m = $stmt->fetch();
        if ($m) sendJson($m);
        else sendJson(['error' => 'No encontrado'], 404);
    } else {
        $stmt = $pdo->query("SELECT * FROM membresias WHERE estado = 'activo' ORDER BY id_membresia DESC");
        sendJson($stmt->fetchAll());
    }
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if(empty($data['tipo']) || empty($data['duracion']) || !isset($data['costo'])) {
        sendJson(['mensaje' => 'Faltan campos'], 400);
    }
    
    $stmt = $pdo->prepare("INSERT INTO membresias (tipo, duracion, costo, estado) VALUES (?, ?, ?, 'activo')");
    $stmt->execute([$data['tipo'], $data['duracion'], $data['costo']]);
    sendJson(['mensaje' => 'Membresía creada', 'id' => $pdo->lastInsertId()]);
    
} elseif ($method === 'PUT') {
    if (!$id) sendJson(['mensaje' => 'ID requerido'], 400);
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("UPDATE membresias SET tipo=?, duracion=?, costo=? WHERE id_membresia=?");
    $stmt->execute([$data['tipo'], $data['duracion'], $data['costo'], $id]);
    sendJson(['mensaje' => 'Membresía actualizada']);
    
} elseif ($method === 'DELETE') {
    if (!$id) sendJson(['mensaje' => 'ID requerido'], 400);
    $stmt = $pdo->prepare("UPDATE membresias SET estado='inactivo' WHERE id_membresia=?");
    $stmt->execute([$id]);
    sendJson(['mensaje' => 'Membresía eliminada']);
} else {
    sendJson(['error' => 'Método no permitido'], 405);
}
?>
