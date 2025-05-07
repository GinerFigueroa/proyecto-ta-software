<?php
require_once '../../../php/database/conexion.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 2) {
    http_response_code(401);
    die(json_encode(['error' => 'Acceso no autorizado']));
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    die(json_encode(['error' => 'ID de historial no proporcionado']));
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Obtener id_dentista del usuario actual
    $stmt = $conn->prepare("SELECT id_dentista FROM dentistas WHERE id_usuario = ?");
    $stmt->execute([$_SESSION['id_usuario']]);
    $dentista = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$dentista) {
        http_response_code(403);
        die(json_encode(['error' => 'Dentista no encontrado']));
    }

    // Consulta para obtener el historial
    $sql = "SELECT h.id_historial, 
                   h.fecha_procedimiento,
                   h.diagnostico,
                   h.procedimiento,
                   h.observaciones,
                   h.receta,
                   h.proxima_visita,
                   h.id_tratamiento,
                   CONCAT(u.nombre_apellido, ' - ', p.id_paciente) AS paciente
            FROM historial_medico h
            JOIN pacientes p ON h.id_paciente = p.id_paciente
            JOIN usuarios u ON p.id_usuario = u.id_usuario
            WHERE h.id_historial = ?
            AND h.id_dentista = ?";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$_GET['id'], $dentista['id_dentista']]);
    $historial = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$historial) {
        http_response_code(404);
        die(json_encode(['error' => 'Historial no encontrado o no tiene permisos']));
    }

    // Formatear fechas
    $historial['fecha_procedimiento'] = date('Y-m-d\TH:i', strtotime($historial['fecha_procedimiento']));
    $historial['proxima_visita'] = $historial['proxima_visita'] ? date('Y-m-d', strtotime($historial['proxima_visita'])) : null;

    echo json_encode($historial);

} catch (PDOException $e) {
    error_log("PDO Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}