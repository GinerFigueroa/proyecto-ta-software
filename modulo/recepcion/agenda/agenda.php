<?php
require_once __DIR__ . '/../../../../php/database/conexion.php';
header('Content-Type: application/json');

try {
    $query = "SELECT 
                c.id_cita,
                c.id_dentista,
                u_p.nombre_apellido AS paciente,
                t.nombre AS tratamiento,
                c.fecha_hora,
                c.duracion,
                c.estado,
                c.notas
              FROM citas c
              LEFT JOIN pacientes p ON c.id_paciente = p.id_paciente
              LEFT JOIN usuarios u_p ON p.id_usuario = u_p.id_usuario
              LEFT JOIN tratamientos t ON c.id_tratamiento = t.id_tratamiento";

    $stmt = $db->prepare($query);
    $stmt->execute();
    $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$citas) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'No se encontraron citas']);
        exit;
    }

    echo json_encode(['success' => true, 'data' => $citas]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>