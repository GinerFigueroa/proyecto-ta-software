<?php
require_once '../../../php/database/conexion.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'message' => 'Método no permitido']));
}

$input = json_decode(file_get_contents('php://input'), true);

// Validación básica
$required = ['nombre', 'duracion', 'costo'];
foreach ($required as $field) {
    if (empty($input[$field])) {
        die(json_encode(['success' => false, 'message' => "Campo $field requerido"]));
    }
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Obtener especialidad del dentista
    $stmt = $conn->prepare("
        SELECT d.id_especialidad 
        FROM dentistas d
        WHERE d.id_usuario = ?
    ");
    $stmt->execute([$_SESSION['id_usuario']]);
    $dentista = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $sql = "INSERT INTO tratamientos (
        nombre, id_especialidad, descripcion, 
        duracion_estimada, costo, requisitos
    ) VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $input['nombre'],
        $dentista['id_especialidad'],
        $input['descripcion'] ?? null,
        $input['duracion'],
        $input['costo'],
        $input['requisitos'] ?? null
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Tratamiento registrado']);

} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos']);
}