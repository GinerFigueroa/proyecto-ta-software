<?php
require_once '../../../php/database/conexion.php';
session_start();

// Verificar autenticación y rol (4 = Paciente)
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 4) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->getConnection();

    $id_paciente = $_SESSION['id_usuario'];

    $sql = "SELECT 
                c.id_cita, 
                DATE_FORMAT(c.fecha_hora, '%Y-%m-%dT%H:%i:%s') AS fecha_hora, 
                c.duracion, 
                c.estado, 
                c.notas,
                t.nombre AS tratamiento,
                CONCAT(cru.nombre_apellido) AS dentista
            FROM citas c
            JOIN tratamientos t ON c.id_tratamiento = t.id_tratamiento
            JOIN pacientes p ON p.id_paciente=c.id_paciente
            JOIN usuarios u ON u.id_usuario=p.id_usuario
            JOIN (SELECT de.id_dentista,us.nombre_apellido FROM usuarios us JOIN dentistas de ON us.id_usuario=de.id_usuario) AS cru ON c.id_dentista=cru.id_dentista
            WHERE u.id_usuario = :id_paciente
            ORDER BY c.fecha_hora DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $citas]);
    } else {
        $errorInfo = $stmt->errorInfo();
        error_log("Error en MostrarCitas.php: SQLSTATE: " . $errorInfo[0] . ", Error: " . $errorInfo[2]);
        echo json_encode(['success' => false, 'message' => 'Error al obtener las citas.']);
    }
} catch (PDOException $e) {
    error_log("Error en MostrarCitas.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al conectar a la base de datos: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("Error en MostrarCitas.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error general: ' . $e->getMessage()]);
}
?>
