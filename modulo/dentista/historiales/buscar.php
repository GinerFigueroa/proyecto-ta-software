<?php
require_once '../../../php/database/conexion.php';
session_start();

// Verificar autenticación y rol
if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    die(json_encode(['error' => 'No autenticado']));
}
if ($_SESSION['id_rol'] != 2) {
    http_response_code(403);
    die(json_encode(['error' => 'No autorizado']));
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Obtener id_dentista del usuario actual
    $stmt = $conn->prepare("SELECT id_dentista FROM dentistas WHERE id_usuario = ?");
    $stmt->execute([$_SESSION['id_usuario']]);
    $dentista = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$dentista) {
        die(json_encode(['error' => 'Dentista no encontrado']));
    }
    
    // Consulta de historiales
    $sql = "SELECT h.id_historial, p.nombre_apellido as paciente, 
                   DATE_FORMAT(h.fecha_procedimiento, '%d/%m/%Y %H:%i') as fecha,
                   t.nombre as tratamiento, h.diagnostico 
            FROM historial_medico h
            LEFT JOIN pacientes pa ON h.id_paciente = pa.id_paciente
            LEFT JOIN usuarios p ON pa.id_usuario = p.id_usuario
            LEFT JOIN tratamientos t ON h.id_tratamiento = t.id_tratamiento
            WHERE h.id_dentista = ?
            ORDER BY h.fecha_procedimiento DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$dentista['id_dentista']]);
    $historiales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($historiales);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}