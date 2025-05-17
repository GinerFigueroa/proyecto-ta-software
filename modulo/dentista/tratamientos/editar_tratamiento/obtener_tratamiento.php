<?php
require_once '../../../../php/database/conexion.php'; // Ajusta la ruta
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    die(json_encode(['error' => 'No autenticado']));
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    die(json_encode(['error' => 'ID inválido']));
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Verificar permiso del dentista
    $stmt = $conn->prepare("
        SELECT t.* 
        FROM tratamientos t
        JOIN dentistas d ON t.id_especialidad = d.id_especialidad
        WHERE d.id_usuario = :id_usuario AND t.id_tratamiento = :id
    ");
    $stmt->execute([':id_usuario' => $_SESSION['id_usuario'], ':id' => $id]);
    $tratamiento = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tratamiento) {
        http_response_code(404);
        die(json_encode(['error' => 'Tratamiento no encontrado']));
    }

    echo json_encode($tratamiento);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en el servidor']);
}