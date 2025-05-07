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
                h.id_historial,
                h.fecha_procedimiento,
                h.diagnostico,
                h.procedimiento,
                h.observaciones,
                h.receta,
                h.proxima_visita,
                h.adjuntos,
                t.nombre as tratamiento,
                CONCAT(u.nombre_apellido) as dentista
            FROM historial_medico h
            LEFT JOIN tratamientos t ON h.id_tratamiento = t.id_tratamiento
            LEFT JOIN usuarios u ON h.id_dentista = u.id_usuario
            WHERE h.id_paciente = :id_paciente
            ORDER BY h.fecha_procedimiento DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
    $stmt->execute();

    $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $historial]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>