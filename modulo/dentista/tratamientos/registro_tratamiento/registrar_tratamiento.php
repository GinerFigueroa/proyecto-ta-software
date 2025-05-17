

<?php
require_once __DIR__ . '/../../../../php/database/conexion.php';
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validar campos requeridos
    $required = ['nombre', 'duracion_estimada', 'costo'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("El campo $field es obligatorio");
        }
    }

    $db = new Database();
    $conn = $db->getConnection();

    $query = "INSERT INTO tratamientos (
                nombre, 
                id_especialidad, 
                descripcion, 
                duracion_estimada, 
                costo, 
                requisitos, 
                activo
              ) VALUES (
                :nombre, 
                :id_especialidad, 
                :descripcion, 
                :duracion_estimada, 
                :costo, 
                :requisitos, 
                1
              )";

    $stmt = $conn->prepare($query);
    
    $params = [
        ':nombre' => $data['nombre'],
        ':id_especialidad' => $data['id_especialidad'] ?? null,
        ':descripcion' => $data['descripcion'] ?? null,
        ':duracion_estimada' => $data['duracion_estimada'],
        ':costo' => $data['costo'],
        ':requisitos' => $data['requisitos'] ?? null
    ];

    if ($stmt->execute($params)) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Error al registrar el tratamiento');
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error de base de datos: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}