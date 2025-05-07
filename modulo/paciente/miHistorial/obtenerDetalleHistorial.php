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
    $id_historial = $_GET['id'];

    $sql = "SELECT 
                h.*,
                t.nombre as tratamiento,
                CONCAT(u.nombre_apellido) as dentista
            FROM historial_medico h
            LEFT JOIN tratamientos t ON h.id_tratamiento = t.id_tratamiento
            LEFT JOIN usuarios u ON h.id_dentista = u.id_usuario
            WHERE h.id_historial = :id_historial
            AND h.id_paciente = :id_paciente
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_historial', $id_historial, PDO::PARAM_INT);
    $stmt->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
    $stmt->execute();

    $registro = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($registro) {
        echo json_encode(['success' => true, 'data' => $registro]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Registro no encontrado']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>