<?php
require_once '../../../php/database/conexion.php';
session_start();

// Verificar autenticación y rol (4 = Paciente)
if (!isset($_SESSION['id_usuario'])) {
    header("HTTP/1.1 401 Unauthorized");
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit();
}

if ($_SESSION['id_rol'] != 4) {
    header("HTTP/1.1 403 Forbidden");
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Obtener ID del paciente desde la sesión
    $id_paciente = $_SESSION['id_usuario'];
    
    // Consulta para obtener las citas del paciente
    $sql = "SELECT 
                c.id_cita, 
                DATE_FORMAT(c.fecha_hora, '%Y-%m-%dT%H:%i:%s') as fecha_hora, 
                c.duracion, 
                c.estado, 
                c.notas,
                t.nombre as tratamiento,
                CONCAT(u.nombre_apellido) as dentista
            FROM citas c
            JOIN tratamientos t ON c.id_tratamiento = t.id_tratamiento
            LEFT JOIN usuarios u ON c.id_dentista = u.id_usuario
            WHERE c.id_paciente = :id_paciente
            ORDER BY c.fecha_hora DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
    $stmt->execute();
    
    $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($citas)) {
        echo json_encode(['success' => true, 'data' => []]);
    } else {
        echo json_encode(['success' => true, 'data' => $citas]);
    }
    
} catch (PDOException $e) {
    error_log("Error en MostrarCitas.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al obtener las citas']);
} catch (Exception $e) {
    error_log("Error en MostrarCitas.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>