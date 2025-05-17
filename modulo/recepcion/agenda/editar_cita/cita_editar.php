<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../../php/database/conexion.php';

try {
    if (!isset($_GET['id_cita']) || !is_numeric($_GET['id_cita'])) {
        throw new Exception('ID de cita inválido');
    }
    
    $idCita = (int)$_GET['id_cita'];
    
    $query = "SELECT 
                c.*,
                u_p.nombre_apellido AS paciente,
                t.nombre AS tratamiento,
                u_d.nombre_apellido AS dentista
              FROM citas c
              LEFT JOIN pacientes p ON c.id_paciente = p.id_paciente
              LEFT JOIN usuarios u_p ON p.id_usuario = u_p.id_usuario
              LEFT JOIN tratamientos t ON c.id_tratamiento = t.id_tratamiento
              LEFT JOIN dentistas d ON c.id_dentista = d.id_dentista
              LEFT JOIN usuarios u_d ON d.id_usuario = u_d.id_usuario
              WHERE c.id_cita = :id_cita";
              
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_cita', $idCita, PDO::PARAM_INT);
    $stmt->execute();
    
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$data) {
        throw new Exception('Cita no encontrada');
    }
    
    echo json_encode($data);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
?>