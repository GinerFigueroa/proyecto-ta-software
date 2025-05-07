<?php
require_once '../../../php/database/conexion.php';

header('Content-Type: application/json');

try {
    $query = "
        SELECT 
            c.id_cita,
            u_p.nombre_apellido AS paciente,
            t.nombre AS tratamiento,
            IFNULL(u_d.nombre_apellido, 'No asignado') AS dentista,
            c.fecha_hora,
            c.duracion,
            c.estado
        FROM citas c
        LEFT JOIN pacientes p ON c.id_paciente = p.id_paciente
        LEFT JOIN usuarios u_p ON p.id_usuario = u_p.id_usuario
        LEFT JOIN tratamientos t ON c.id_tratamiento = t.id_tratamiento
        LEFT JOIN dentistas d ON c.id_dentista = d.id_dentista
        LEFT JOIN usuarios u_d ON d.id_usuario = u_d.id_usuario
        ORDER BY c.fecha_hora DESC
    ";

    $stmt = $db->prepare($query);
    $stmt->execute();
    $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['data' => $citas]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>