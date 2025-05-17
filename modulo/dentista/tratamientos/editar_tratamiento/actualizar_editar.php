<?php
require_once '../../../../php/database/conexion.php'; // Ajusta la ruta según tu estructura
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    die(json_encode(['success' => false, 'message' => 'No autenticado']));
}

// Validar y sanitizar datos
$id = filter_input(INPUT_POST, 'id_tratamiento', FILTER_VALIDATE_INT);
$nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
$descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING);
$duracion = filter_input(INPUT_POST, 'duracion_estimada', FILTER_VALIDATE_INT);
$costo = filter_input(INPUT_POST, 'costo', FILTER_VALIDATE_FLOAT);
$requisitos = filter_input(INPUT_POST, 'requisitos', FILTER_SANITIZE_STRING);
$activo = filter_input(INPUT_POST, 'activo', FILTER_VALIDATE_INT);

if (!$id || !$nombre || !$descripcion || !$duracion || !$costo || $activo === false) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Datos inválidos']));
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Verificar permiso del dentista
    $stmt = $conn->prepare("
        SELECT t.id_tratamiento
        FROM tratamientos t
        JOIN dentistas d ON t.id_especialidad = d.id_especialidad
        WHERE d.id_usuario = :id_usuario AND t.id_tratamiento = :id
    ");
    $stmt->execute([':id_usuario' => $_SESSION['id_usuario'], ':id' => $id]);
    
    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        http_response_code(403);
        die(json_encode(['success' => false, 'message' => 'No autorizado']));
    }

    // Actualizar tratamiento
    $stmt = $conn->prepare("
        UPDATE tratamientos 
        SET nombre = :nombre, descripcion = :descripcion, 
            duracion_estimada = :duracion, costo = :costo, 
            requisitos = :requisitos, activo = :activo
        WHERE id_tratamiento = :id
    ");

    $stmt->execute([
        ':nombre' => $nombre,
        ':descripcion' => $descripcion,
        ':duracion' => $duracion,
        ':costo' => $costo,
        ':requisitos' => $requisitos,
        ':activo' => $activo,
        ':id' => $id
    ]);

    echo json_encode(['success' => true, 'message' => 'Tratamiento actualizado']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error en el servidor']);
}