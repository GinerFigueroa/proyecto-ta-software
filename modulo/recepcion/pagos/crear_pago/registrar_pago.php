<?php
require_once __DIR__ . '/../../../php/database/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

try {
    $stmt = $db->prepare("
        INSERT INTO pagos (
            id_cita,
            monto,
            metodo_pago,
            estado,
            referencia,
            fecha_pago,
            notas
        ) VALUES (
            :id_cita,
            :monto,
            :metodo_pago,
            'Completado',
            :referencia,
            NOW(),
            :notas
        )
    ");
    
    $stmt->bindParam(':id_cita', $data['id_cita']);
    $stmt->bindParam(':monto', $data['monto']);
    $stmt->bindParam(':metodo_pago', $data['metodo_pago']);
    $stmt->bindParam(':referencia', $data['referencia']);
    $stmt->bindParam(':notas', $data['notas']);
    
    $stmt->execute();
    
    echo json_encode(['success' => true, 'message' => 'Pago registrado correctamente']);
} catch (PDOException $e) {
    error_log("Error al registrar pago: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al registrar el pago: ' . $e->getMessage()]);
}
?>
