<?php
require_once __DIR__ . '/db.php';
requireAuth();

$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;

if ($method === 'GET') {
    if ($id) {
        $stmt = $pdo->prepare("SELECT p.*, CONCAT(c.nombre, ' ', c.apellido_p) AS cliente_nombre 
                               FROM pagos p 
                               JOIN clientes c ON p.id_cliente = c.id_cliente
                               WHERE p.id_pago = ? AND p.estado = 'activo'");
        $stmt->execute([$id]);
        $p = $stmt->fetch();
        if ($p) sendJson($p);
        else sendJson(['error' => 'No encontrado'], 404);
    } else {
        $stmt = $pdo->query("SELECT p.*, CONCAT(c.nombre, ' ', c.apellido_p) AS cliente_nombre 
                             FROM pagos p 
                             JOIN clientes c ON p.id_cliente = c.id_cliente
                             WHERE p.estado = 'activo' ORDER BY p.fecha_pago DESC, p.id_pago DESC");
        sendJson($stmt->fetchAll());
    }
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if(empty($data['id_cliente']) || empty($data['monto']) || empty($data['fecha_pago'])) {
        sendJson(['mensaje' => 'Faltan campos'], 400);
    }
    
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT INTO pagos (id_cliente, monto, fecha_pago, estado) VALUES (?, ?, ?, 'activo')");
        $stmt->execute([$data['id_cliente'], $data['monto'], $data['fecha_pago']]);
        
        // Handling automatic renewal logic if renovar is true
        if (isset($data['renovar']) && $data['renovar'] === true) {
            // Find active/last inscripcion for this client to copy membership type, or just assume we extend the latest one
            $stmtLast = $pdo->prepare("SELECT * FROM inscripciones WHERE id_cliente = ? ORDER BY id_inscripcion DESC LIMIT 1");
            $stmtLast->execute([$data['id_cliente']]);
            $lastInsc = $stmtLast->fetch();
            
            if ($lastInsc) {
                // Get duration from membresia
                $stmtMem = $pdo->prepare("SELECT duracion FROM membresias WHERE id_membresia = ?");
                $stmtMem->execute([$lastInsc['id_membresia']]);
                $mem = $stmtMem->fetch();
                
                $days = 30; // default
                if ($mem) {
                    if (str_contains(strtolower($mem['duracion']), 'año') || str_contains(strtolower($mem['duracion']), 'ano')) $days = 365;
                    else if (str_contains(strtolower($mem['duracion']), 'semana')) $days = 7;
                }
                
                // extend logic
                $startDate = max(strtotime($lastInsc['fecha_fin']), strtotime(date('Y-m-d')));
                $newStart = date('Y-m-d', $startDate);
                $newEnd = date('Y-m-d', strtotime("+$days days", $startDate));
                
                $stmtRen = $pdo->prepare("INSERT INTO inscripciones (id_cliente, id_membresia, fecha_inicio, fecha_fin, estado) VALUES (?, ?, ?, ?, 'activo')");
                $stmtRen->execute([$data['id_cliente'], $lastInsc['id_membresia'], $newStart, $newEnd]);
            }
        }
        $pdo->commit();
        sendJson(['mensaje' => 'Pago registrado', 'id' => $pdo->lastInsertId()]);
    } catch(Exception $e) {
        $pdo->rollBack();
        error_log("Error en registro de pago: " . $e->getMessage());
        sendJson(['error' => 'Error interno al registrar el pago'], 500);
    }
    
} elseif ($method === 'PUT') {
    if (!$id) sendJson(['mensaje' => 'ID requerido'], 400);
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("UPDATE pagos SET id_cliente=?, monto=?, fecha_pago=? WHERE id_pago=?");
    $stmt->execute([$data['id_cliente'], $data['monto'], $data['fecha_pago'], $id]);
    sendJson(['mensaje' => 'Pago actualizado']);
    
} elseif ($method === 'DELETE') {
    if (!$id) sendJson(['mensaje' => 'ID requerido'], 400);
    $stmt = $pdo->prepare("UPDATE pagos SET estado='inactivo' WHERE id_pago=?");
    $stmt->execute([$id]);
    sendJson(['mensaje' => 'Pago eliminado']);
} else {
    sendJson(['error' => 'Método no permitido'], 405);
}
?>
