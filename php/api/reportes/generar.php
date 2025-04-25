<?php
require '../../auth/check-auth.php';
require '../../database/conexion.php';

header('Content-Type: application/json');

// Solo administradores
checkAuth(['admin']);

$tipo = $_GET['tipo'] ?? 'mensual';
$fechaInicio = $_GET['inicio'] ?? date('Y-m-01');
$fechaFin = $_GET['fin'] ?? date('Y-m-t');

switch ($tipo) {
    case 'financiero':
        $stmt = $pdo->prepare("
            SELECT 
                DATE_FORMAT(p.fecha_pago, '%Y-%m') AS mes,
                SUM(p.monto) AS total,
                COUNT(*) AS citas
            FROM pagos p
            JOIN citas c ON p.id_cita = c.id_cita
            WHERE p.fecha_pago BETWEEN ? AND ?
            GROUP BY mes
            ORDER BY mes
        ");
        break;
        
    case 'procedimientos':
        $stmt = $pdo->prepare("
            SELECT 
                t.nombre AS tratamiento,
                COUNT(*) AS cantidad,
                SUM(p.monto) AS ingresos
            FROM citas c
            JOIN tratamientos t ON c.id_tratamiento = t.id_tratamiento
            JOIN pagos p ON c.id_cita = p.id_cita
            WHERE c.fecha_hora BETWEEN ? AND ?
            GROUP BY t.nombre
            ORDER BY cantidad DESC
            LIMIT 5
        ");
        break;
}

$stmt->execute([$fechaInicio, $fechaFin]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'tipo' => $tipo,
    'periodo' => ['inicio' => $fechaInicio, 'fin' => $fechaFin],
    'data' => $data
]);
?>