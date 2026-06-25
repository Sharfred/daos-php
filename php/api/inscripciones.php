<?php
require_once __DIR__ . '/db.php';
requireAuth();

$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;

if ($method === 'GET') {
    if ($id) {
        $stmt = $pdo->prepare("SELECT i.*, CONCAT(c.nombre, ' ', c.apellido_p) AS cliente_nombre, m.tipo AS membresia_tipo 
                               FROM inscripciones i 
                               JOIN clientes c ON i.id_cliente = c.id_cliente
                               JOIN membresias m ON i.id_membresia = m.id_membresia
                               WHERE i.id_inscripcion = ? AND i.estado = 'activo'");
        $stmt->execute([$id]);
        $i = $stmt->fetch();
        if ($i) sendJson($i);
        else sendJson(['error' => 'No encontrado'], 404);
    } else {
        $stmt = $pdo->query("SELECT i.*, CONCAT(c.nombre, ' ', c.apellido_p) AS cliente_nombre, m.tipo AS membresia_tipo 
                             FROM inscripciones i 
                             JOIN clientes c ON i.id_cliente = c.id_cliente
                             JOIN membresias m ON i.id_membresia = m.id_membresia
                             WHERE i.estado = 'activo' ORDER BY i.id_inscripcion DESC");
        sendJson($stmt->fetchAll());
    }
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if(empty($data['id_cliente']) || empty($data['id_membresia']) || empty($data['fecha_inicio']) || empty($data['fecha_fin'])) {
        sendJson(['mensaje' => 'Faltan campos'], 400);
    }
    
    $stmt = $pdo->prepare("INSERT INTO inscripciones (id_cliente, id_membresia, fecha_inicio, fecha_fin, estado) VALUES (?, ?, ?, ?, 'activo')");
    $stmt->execute([$data['id_cliente'], $data['id_membresia'], $data['fecha_inicio'], $data['fecha_fin']]);
    sendJson(['mensaje' => 'Inscripción registrada', 'id' => $pdo->lastInsertId()]);
    
} elseif ($method === 'PUT') {
    if (!$id) sendJson(['mensaje' => 'ID requerido'], 400);
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("UPDATE inscripciones SET id_cliente=?, id_membresia=?, fecha_inicio=?, fecha_fin=? WHERE id_inscripcion=?");
    $stmt->execute([$data['id_cliente'], $data['id_membresia'], $data['fecha_inicio'], $data['fecha_fin'], $id]);
    sendJson(['mensaje' => 'Inscripción actualizada']);
    
} elseif ($method === 'DELETE') {
    if (!$id) sendJson(['mensaje' => 'ID requerido'], 400);
    $stmt = $pdo->prepare("UPDATE inscripciones SET estado='inactivo' WHERE id_inscripcion=?");
    $stmt->execute([$id]);
    sendJson(['mensaje' => 'Inscripción eliminada']);
} else {
    sendJson(['error' => 'Método no permitido'], 405);
}
?>
