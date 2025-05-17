<?php
require '../../../php/database/conexion.php';
session_start();

if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 2) {
    http_response_code(401);
    exit(json_encode(["error" => "Acceso no autorizado"]));
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Obtener ID del dentista
    $stmt = $conn->prepare("
        SELECT d.id_dentista 
        FROM dentistas d
        WHERE d.id_usuario = ?
    ");
    $stmt->execute([$_SESSION['id_usuario']]);
    $dentista = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$dentista) {
        throw new Exception('Dentista no encontrado');
    }

    // Consulta principal
    $sql = "
        SELECT 
            h.id_historial,
            CONCAT(u.nombre_apellido) AS paciente,
            DATE_FORMAT(h.fecha_procedimiento, '%d/%m/%Y %H:%i') AS fecha,
            t.nombre AS tratamiento,
            h.diagnostico,
            h.procedimiento,
            h.observaciones,
            h.receta,
            h.proxima_visita,
            h.adjuntos
        FROM historial_medico h
        LEFT JOIN pacientes p ON h.id_paciente = p.id_paciente
        LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario
        LEFT JOIN tratamientos t ON h.id_tratamiento = t.id_tratamiento
        WHERE h.id_dentista = ?
        ORDER BY h.fecha_procedimiento DESC
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$dentista['id_dentista']]);
    
    $historiales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($historiales);

    $db->closeConnection();

} catch (PDOException $e) {
    error_log("Error BD: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["error" => "Error en la base de datos"]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["error" => $e->getMessage()]);
}
?>