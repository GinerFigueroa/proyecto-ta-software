<?php
require_once __DIR__ . '/../../../../php/database/conexion.php';
session_start();

header('Content-Type: application/json');

// Validar método y rol
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['id_usuario'])) {
    http_response_code(403);
    die(json_encode(['success' => false, 'message' => 'Acceso no autorizado']));
}

// Leer y validar datos
$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Datos JSON inválidos']));
}

// Campos obligatorios
$required = ['id_historial', 'fecha_procedimiento', 'diagnostico'];
foreach ($required as $field) {
    if (empty($input[$field])) {
        http_response_code(400);
        die(json_encode(['success' => false, 'message' => "El campo $field es requerido"]));
    }
}

try {
    $db = new Database();
    $conn = $db->getConnection();

   
   // En actualizar_editar.php, usar LEFT JOIN y validar NULL:
$stmt = $conn->prepare("
    SELECT h.id_historial 
    FROM historial_medico h
    LEFT JOIN dentistas d ON h.id_dentista = d.id_dentista
    WHERE h.id_historial = :id_historial
    AND (h.id_dentista IS NULL OR d.id_usuario = :id_usuario)
");
    $stmt->execute([
        ':id_historial' => $input['id_historial'],
        ':id_usuario' => $_SESSION['id_usuario']
    ]);
    
    if (!$stmt->fetch()) {
        http_response_code(403);
        die(json_encode(['success' => false, 'message' => 'No tienes permisos para este historial']));
    }

    // Actualizar registro
    $sql = "UPDATE historial_medico SET
            fecha_procedimiento = :fecha,
            id_tratamiento = :tratamiento,
            diagnostico = :diagnostico,
            procedimiento = :procedimiento,
            observaciones = :observaciones,
            receta = :receta,
            proxima_visita = :proxima_visita
            WHERE id_historial = :id";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':fecha' => date('Y-m-d H:i:s', strtotime($input['fecha_procedimiento'])),
        ':tratamiento' => !empty($input['id_tratamiento']) ? $input['id_tratamiento'] : null,
        ':diagnostico' => trim($input['diagnostico']),
        ':procedimiento' => trim($input['procedimiento'] ?? ''),
        ':observaciones' => trim($input['observaciones'] ?? ''),
        ':receta' => trim($input['receta'] ?? ''),
        ':proxima_visita' => !empty($input['proxima_visita']) ? date('Y-m-d', strtotime($input['proxima_visita'])) : null,
        ':id' => $input['id_historial']
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Actualización exitosa']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se realizaron cambios']);
    }

} catch (PDOException $e) {
    error_log("Error PDO: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error en base de datos']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}




