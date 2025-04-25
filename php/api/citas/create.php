<?php
require '../../auth/check-auth.php';
require '../../database/conexion.php';

header('Content-Type: application/json');

// Solo pacientes pueden crear citas
$tokenData = checkAuth(['cliente']);

$data = json_decode(file_get_contents('php://input'), true);

// Validación básica
if (empty($data['id_tratamiento']) || empty($data['fecha_hora'])) {
    http_response_code(400);
    die(json_encode(['error' => 'Datos incompletos']));
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO citas (
            id_paciente, 
            id_tratamiento, 
            fecha_hora, 
            estado
        ) VALUES (?, ?, ?, 'pendiente')
    ");
    
    $stmt->execute([
        $tokenData->paciente_id,
        $data['id_tratamiento'],
        $data['fecha_hora']
    ]);
    
    echo json_encode([
        'success' => true,
        'id_cita' => $pdo->lastInsertId()
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en BD: ' . $e->getMessage()]);
}
?>