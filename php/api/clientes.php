<?php
require_once __DIR__ . '/db.php';
requireAuth();

$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;

if ($method === 'GET') {
    if ($id) {
        $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id_cliente = ? AND estado = 'activo'");
        $stmt->execute([$id]);
        $cliente = $stmt->fetch();
        if ($cliente) sendJson($cliente);
        else sendJson(['error' => 'Cliente no encontrado'], 404);
    } else {
        $stmt = $pdo->query("SELECT * FROM clientes WHERE estado = 'activo' ORDER BY id_cliente DESC");
        sendJson($stmt->fetchAll());
    }
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if(empty($data['nombre']) || empty($data['apellido_p']) || empty($data['apellido_m']) || empty($data['edad'])) {
        sendJson(['mensaje' => 'Faltan campos'], 400);
    }
    
    $stmt = $pdo->prepare("INSERT INTO clientes (nombre, apellido_p, apellido_m, edad, estado) VALUES (?, ?, ?, ?, 'activo')");
    $stmt->execute([$data['nombre'], $data['apellido_p'], $data['apellido_m'], $data['edad']]);
    sendJson(['mensaje' => 'Cliente creado', 'id' => $pdo->lastInsertId()]);
    
} elseif ($method === 'PUT') {
    if (!$id) sendJson(['mensaje' => 'ID requerido'], 400);
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("UPDATE clientes SET nombre=?, apellido_p=?, apellido_m=?, edad=? WHERE id_cliente=?");
    $stmt->execute([$data['nombre'], $data['apellido_p'], $data['apellido_m'], $data['edad'], $id]);
    sendJson(['mensaje' => 'Cliente actualizado']);
    
} elseif ($method === 'DELETE') {
    if (!$id) sendJson(['mensaje' => 'ID requerido'], 400);
    $stmt = $pdo->prepare("UPDATE clientes SET estado='inactivo' WHERE id_cliente=?");
    $stmt->execute([$id]);
    sendJson(['mensaje' => 'Cliente eliminado']);
} else {
    sendJson(['error' => 'Método no permitido'], 405);
}
?>
