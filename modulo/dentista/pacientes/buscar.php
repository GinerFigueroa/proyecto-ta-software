<?php
require '../../../php/database/conexion.php';
session_start();

// 🔒 RESTRICCIÓN 1: Verificar que el usuario tenga sesión iniciada
// 🔒 RESTRICCIÓN 2: Verificar que el usuario tenga rol 2 (dentista)
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 2) {
    http_response_code(401); // Acceso no autorizado
    exit(json_encode(["error" => "Acceso no autorizado"]));
}

try {
    // Conexión a la base de datos
    $db = new Database();
    $conn = $db->getConnection();

    // 🔒 RESTRICCIÓN 3: Obtener ID del dentista asociado al usuario
    $stmt = $conn->prepare("
        SELECT d.id_dentista 
        FROM dentistas d
        WHERE d.id_usuario = ?
    ");
    $stmt->execute([$_SESSION['id_usuario']]);
    $dentista = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si no se encuentra un dentista asociado al usuario, lanzar excepción
    if (!$dentista) {
        throw new Exception('Dentista no encontrado');
    }

    // Consulta principal para obtener el historial médico del dentista
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
    
    // Ejecutar consulta
    $stmt = $conn->prepare($sql);
    $stmt->execute([$dentista['id_dentista']]);
    
    // Obtener todos los resultados
    $historiales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Devolver resultados en formato JSON
    echo json_encode($historiales);

    // Cerrar conexión
    $db->closeConnection();

} catch (PDOException $e) {
    // Error en la base de datos
    error_log("Error BD: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["error" => "Error en la base de datos"]);
} catch (Exception $e) {
    // Otro tipo de error (como "dentista no encontrado")
    http_response_code(400);
    echo json_encode(["error" => $e->getMessage()]);
}
?>
