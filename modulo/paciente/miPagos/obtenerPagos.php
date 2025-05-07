<?php
require_once '../../../php/database/conexion.php';
session_start();

header('Content-Type: application/json');

// Verificar autenticación y rol (4 = Paciente)
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 4) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    $id_paciente = $_SESSION['id_usuario'];

    $sql = "SELECT 
                p.id_pago,
                p.monto,
                p.metodo_pago,
                p.estado,
                p.referencia,
                p.fecha_pago,
                p.notas,
                CONCAT('Cita #', c.id_cita, ' - ', t.nombre) as cita_info
            FROM pagos p
            LEFT JOIN citas c ON p.id_cita = c.id_cita
            LEFT JOIN tratamientos t ON c.id_tratamiento = t.id_tratamiento
            WHERE c.id_paciente = :id_paciente
            ORDER BY p.fecha_pago DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
    $stmt->execute();

    $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $pagos]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>