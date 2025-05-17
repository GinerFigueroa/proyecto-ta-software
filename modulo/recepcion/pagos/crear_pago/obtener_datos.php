<?php
require_once __DIR__ . '/../../../php/database/conexion.php';

header('Content-Type: application/json');

try {
    $stmt = $db->prepare("
        SELECT 
            c.id_cita,
            CONCAT(u.nombre_apellido, ' - ', COALESCE(t.nombre, 'Consulta general'), ' (', DATE_FORMAT(c.fecha_hora, '%d/%m/%Y %H:%i'), ')') AS info_cita,
            COALESCE(t.costo, 0) AS monto_sugerido
        FROM 
            citas c
        JOIN pacientes pa ON c.id_paciente = pa.id_paciente
        JOIN usuarios u ON pa.id_usuario = u.id_usuario
        LEFT JOIN tratamientos t ON c.id_tratamiento = t.id_tratamiento
        WHERE 
            c.estado = 'Completada' 
            AND NOT EXISTS (SELECT 1 FROM pagos p WHERE p.id_cita = c.id_cita)
    ");
    
    $stmt->execute();
    $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $citas
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener citas: ' . $e->getMessage()
    ]);
}
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");

?>


