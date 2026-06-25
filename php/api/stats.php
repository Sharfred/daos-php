<?php
require_once __DIR__ . '/db.php';
requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendJson(['error' => 'Método no permitido'], 405);
}

try {
    // Total clientes
    $stmtC = $pdo->query("SELECT COUNT(*) as total FROM clientes WHERE estado = 'activo'");
    $totalClientes = $stmtC->fetch()['total'];

    // Membresías Activas (Inscripciones cuya fecha_fin >= hoy)
    $stmtM = $pdo->query("SELECT COUNT(DISTINCT id_cliente) as activas FROM inscripciones WHERE estado = 'activo' AND fecha_fin >= CURDATE()");
    $membresiasActivas = $stmtM->fetch()['activas'];

    // Ingresos este mes
    $stmtI = $pdo->query("SELECT SUM(monto) as ingresos FROM pagos WHERE estado = 'activo' AND MONTH(fecha_pago) = MONTH(CURDATE()) AND YEAR(fecha_pago) = YEAR(CURDATE())");
    $ingresosMes = $stmtI->fetch()['ingresos'] ?? 0;

    // Últimos 7 pagos para la gráfica
    $stmtP = $pdo->query("SELECT fecha_pago, monto FROM pagos WHERE estado = 'activo' ORDER BY fecha_pago DESC, id_pago DESC LIMIT 7");
    $ultimosPagos = $stmtP->fetchAll();

    sendJson([
        'totalClientes' => $totalClientes,
        'membresiasActivas' => $membresiasActivas,
        'ingresosMes' => $ingresosMes,
        'ultimosPagos' => $ultimosPagos
    ]);

} catch (Exception $e) {
    error_log("Error en estadísticas: " . $e->getMessage());
    sendJson(['error' => 'Error interno al obtener estadísticas'], 500);
}
?>
