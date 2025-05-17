<?php
require_once '../../../php/database/conexion.php';

header('Content-Type: application/json');

try {
    // Asegurar zona horaria
    $db->exec("SET time_zone = '-05:00'"); // Ajusta según tu ubicación

    // Citas hoy
    $queryHoy = "SELECT COUNT(*) AS hoy FROM citas 
                WHERE DATE(fecha_hora) = CURDATE()";
    
    // Citas por estado
    $queryEstados = "SELECT 
                    SUM(CASE WHEN estado = 'Completada' THEN 1 ELSE 0 END) AS completadas,
                    SUM(CASE WHEN estado = 'Pendiente' THEN 1 ELSE 0 END) AS pendientes,
                    SUM(CASE WHEN estado IN ('Cancelada', 'No asistió') THEN 1 ELSE 0 END) AS canceladas
                    FROM citas";
    
    $stmt = $db->query($queryHoy);
    $hoy = $stmt->fetch(PDO::FETCH_ASSOC)['hoy'];
    
    $stmt = $db->query($queryEstados);
    $estados = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'hoy' => $hoy,
        'completadas' => $estados['completadas'],
        'pendientes' => $estados['pendientes'],
        'canceladas' => $estados['canceladas']
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>