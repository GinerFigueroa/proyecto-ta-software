<?php
require_once '../../../php/database/conexion.php';

header('Content-Type: application/json');

try {
    $stmt = $db->prepare("
        SELECT 
            p.id_pago,
            p.id_cita,
            CONCAT('S/. ', FORMAT(p.monto, 2)) AS monto_formateado,
            p.metodo_pago,
            CASE p.estado
                WHEN 'Completado' THEN '✅ Completado'
                WHEN 'Pendiente' THEN '🔄 Pendiente'
                WHEN 'Reembolsado' THEN '↩️ Reembolsado'
                WHEN 'Cancelado' THEN '❌ Cancelado'
                ELSE p.estado
            END AS estado_visual,
            DATE_FORMAT(p.fecha_pago, '%d/%m/%Y %H:%i') AS fecha_pago_formateada,
            u.nombre_apellido AS paciente,
            t.nombre AS tratamiento,
            c.fecha_hora AS fecha_cita
        FROM 
            pagos p
        JOIN citas c ON p.id_cita = c.id_cita
        JOIN pacientes pa ON c.id_paciente = pa.id_paciente
        JOIN usuarios u ON pa.id_usuario = u.id_usuario
        LEFT JOIN tratamientos t ON c.id_tratamiento = t.id_tratamiento
        ORDER BY 
            p.fecha_pago DESC
        LIMIT 100
    ");
    $stmt->execute();
    $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $pagos]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al obtener pagos: ' . $e->getMessage()]);
}
?>
