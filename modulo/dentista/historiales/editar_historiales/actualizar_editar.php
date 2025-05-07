<?php
require_once '../../../php/database/conexion.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $_SESSION['id_rol'] != 2) {
    http_response_code(403);
    die(json_encode(['success' => false, 'message' => 'Acceso no autorizado']));
}

$input = json_decode(file_get_contents('php://input'), true);

// Validación básica
if (empty($input['id_historial'])) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'ID de historial inválido']));
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Verificar permiso del dentista
    $stmt = $conn->prepare("
        SELECT h.id_historial 
        FROM historial_medico h
        JOIN dentistas d ON h.id_dentista = d.id_dentista
        WHERE h.id_historial = ?
        AND d.id_usuario = ?
    ");
    $stmt->execute([$input['id_historial'], $_SESSION['id_usuario']]);
    
    if (!$stmt->fetch()) {
        http_response_code(403);
        die(json_encode(['success' => false, 'message' => 'No tienes permisos para editar este historial']));
    }

    // Actualización segura con parámetros
    $sql = "UPDATE historial_medico SET
            fecha_procedimiento = ?,
            id_tratamiento = ?,
            diagnostico = ?,
            procedimiento = ?,
            observaciones = ?,
            receta = ?,
            proxima_visita = ?
            WHERE id_historial = ?";

    $stmt = $conn->prepare($sql);
    $success = $stmt->execute([
        date('Y-m-d H:i:s', strtotime($input['fecha_procedimiento'])),
        $input['id_tratamiento'] ?? null,
        trim($input['diagnostico']),
        trim($input['procedimiento']),
        trim($input['observaciones']),
        trim($input['receta']),
        !empty($input['proxima_visita']) ? date('Y-m-d', strtotime($input['proxima_visita'])) : null,
        $input['id_historial']
    ]);

    if ($success && $stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Historial actualizado exitosamente'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No se realizaron cambios o ocurrió un error'
        ]);
    }

} catch (PDOException $e) {
    error_log("PDO Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}