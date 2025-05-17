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
        UPDATE pagos SET
            monto = :monto,
            metodo_pago = :metodo_pago,
            estado = :estado,
            referencia = :referencia,
            notas = :notas,
            fecha_pago = :fecha_pago
        WHERE 
            id_pago = :id_pago
    ");
    
    $stmt->bindParam(':id_pago', $data['id_pago']);
    $stmt->bindParam(':monto', $data['monto']);
    $stmt->bindParam(':metodo_pago', $data['metodo_pago']);
    $stmt->bindParam(':estado', $data['estado']);
    $stmt->bindParam(':referencia', $data['referencia']);
    $stmt->bindParam(':notas', $data['notas']);
    $stmt->bindParam(':fecha_pago', $data['fecha_pago']);
    
    $stmt->execute();
    
    echo json_encode(['success' => true, 'message' => 'Pago actualizado correctamente']);
} catch (PDOException $e) {
    error_log("Error al actualizar pago: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al actualizar el pago: ' . $e->getMessage()]);
}
?>
