<?php
require_once '../../../php/database/conexion.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'message' => 'Método no permitido']));
}

$input = json_decode(file_get_contents('php://input'), true);

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Verificar permiso de edición
    $stmt = $conn->prepare("
        SELECT t.id_tratamiento 
        FROM tratamientos t
        JOIN dentistas d ON t.id_especialidad = d.id_especialidad
        WHERE d.id_usuario = ? AND t.id_tratamiento = ?
    ");
    $stmt->execute([$_SESSION['id_usuario'], $input['id_tratamiento']]);
    
    if (!$stmt->fetch()) {
        die(json_encode(['success' => false, 'message' => 'No autorizado']));
    }

    $sql = "UPDATE tratamientos SET
            nombre = ?,
            descripcion = ?,
            duracion_estimada = ?,
            costo = ?,
            requisitos = ?
            WHERE id_tratamiento = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $input['nombre'],
        $input['descripcion'],
        $input['duracion'],
        $input['costo'],
        $input['requisitos'],
        $input['id_tratamiento']
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Tratamiento actualizado']);

} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos']);
}