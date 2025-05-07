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

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID no especificado']);
    exit();
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    $id_paciente = $_SESSION['id_usuario'];
    $id_pago = $_GET['id'];

    $sql = "SELECT 
                p.*,
                CONCAT('Cita #', c.id_cita, ' - ', t.nombre, ' (', DATE_FORMAT(c.fecha_hora, '%d/%m/%Y %H:%i'), ')') as cita_info
            FROM pagos p
            LEFT JOIN citas c ON p.id_cita = c.id_cita
            LEFT JOIN tratamientos t ON c.id_tratamiento = t.id_tratamiento
            WHERE p.id_pago = :id_pago
            AND c.id_paciente = :id_paciente
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_pago', $id_pago, PDO::PARAM_INT);
    $stmt->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
    $stmt->execute();

    $pago = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($pago) {
        echo json_encode(['success' => true, 'data' => $pago]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Pago no encontrado']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>