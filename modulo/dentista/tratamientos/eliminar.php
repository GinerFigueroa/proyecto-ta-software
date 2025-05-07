<?php
require_once '../../../php/database/conexion.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    die(json_encode(['success' => false, 'message' => 'No autenticado']));
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Verificar especialidad del dentista
    $stmt = $conn->prepare("
        SELECT d.id_especialidad 
        FROM dentistas d
        WHERE d.id_usuario = ?
    ");
    $stmt->execute([$_SESSION['id_usuario']]);
    $dentista = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Eliminar tratamiento
    $stmt = $conn->prepare("
        DELETE FROM tratamientos 
        WHERE id_tratamiento = ? 
        AND (id_especialidad = ? OR id_especialidad IS NULL)
    ");
    $stmt->execute([$id, $dentista['id_especialidad']]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Tratamiento eliminado']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se pudo eliminar']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}