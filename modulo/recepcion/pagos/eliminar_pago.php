<?php
require_once __DIR__ . '/../../php/database/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id_pago = $data['id'] ?? null;

if (!$id_pago) {
    echo json_encode(['success' => false, 'message' => 'ID de pago no proporcionado']);
    exit;
}

try {
    // Verificar si el pago existe
    $stmt = $db->prepare("SELECT estado FROM pagos WHERE id_pago = :id_pago");
    $stmt->bindParam(':id_pago', $id_pago);
    $stmt->execute();
    $pago = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pago) {
        echo json_encode(['success' => false, 'message' => 'Pago no encontrado']);
        exit;
    }
    
    if ($pago['estado'] === 'Reembolsado') {
        echo json_encode(['success' => false, 'message' => 'No se puede eliminar un pago reembolsado']);
        exit;
    }
    
    // Eliminar el pago
    $stmt = $db->prepare("DELETE FROM pagos WHERE id_pago = :id_pago");
    $stmt->bindParam(':id_pago', $id_pago);
    $stmt->execute();
    
    echo json_encode(['success' => true, 'message' => 'Pago eliminado correctamente']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar pago: ' . $e->getMessage()]);
}
?>
