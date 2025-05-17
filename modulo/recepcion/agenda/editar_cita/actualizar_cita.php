<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../../php/database/conexion.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id_cita'])) {
        throw new Exception('Parámetro ID faltante');
    }
    
    // Validar campos requeridos
    $required = ['fecha_hora', 'duracion', 'estado'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("El campo $field es requerido");
        }
    }

    $query = "UPDATE citas SET 
                id_dentista = :id_dentista,
                fecha_hora = :fecha_hora,
                duracion = :duracion,
                estado = :estado,
                notas = :notas
              WHERE id_cita = :id_cita";

    $params = [
        ':id_cita' => $data['id_cita'],
        ':id_dentista' => $data['id_dentista'] ?? null,
        ':fecha_hora' => $data['fecha_hora'],
        ':duracion' => $data['duracion'],
        ':estado' => $data['estado'],
        ':notas' => $data['notas'] ?? null
    ];

    $stmt = $db->prepare($query);
    
    if ($stmt->execute($params)) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Error al actualizar la cita');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>