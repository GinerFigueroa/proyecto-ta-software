<?php
require_once __DIR__ . '/../../../php/database/conexion.php';

header('Content-Type: application/json');

$id_pago = $_GET['id'] ?? null;

if (!$id_pago) {
    echo json_encode(['success' => false, 'message' => 'ID de pago no proporcionado']);
    exit;
}

try {
    $stmt = $db->prepare("
        SELECT 
            p.*,
            CONCAT(u.nombre_apellido, ' - Cita #', c.id_cita) AS info_paciente,
            t.nombre AS tratamiento,
            t.costo AS costo_tratamiento,
            DATE_FORMAT(p.fecha_pago, '%Y-%m-%dT%H:%i') AS fecha_pago_iso
        FROM 
            pagos p
        JOIN citas c ON p.id_cita = c.id_cita
        JOIN pacientes pa ON c.id_paciente = pa.id_paciente
        JOIN usuarios u ON pa.id_usuario = u.id_usuario
        LEFT JOIN tratamientos t ON c.id_tratamiento = t.id_tratamiento
        WHERE 
            p.id_pago = :id_pago
    ");
    
    $stmt->bindParam(':id_pago', $id_pago);
    $stmt->execute();
    $pago = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($pago) {
        echo json_encode(['success' => true, 'data' => $pago]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Pago no encontrado']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al obtener pago: ' . $e->getMessage()]);
}
?>
