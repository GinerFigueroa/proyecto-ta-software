<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../../php/database/conexion.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $required = ['fecha_nacimiento', 'genero'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("El campo $field es obligatorio");
        }
    }

    $query = "INSERT INTO pacientes (
        id_usuario,
        fecha_nacimiento,
        genero,
        alergias,
        enfermedades_cronicas,
        medicamentos,
        seguro_medico,
        numero_seguro
    ) VALUES (
        :id_usuario,
        :fecha_nacimiento,
        :genero,
        :alergias,
        :enfermedades_cronicas,
        :medicamentos,
        :seguro_medico,
        :numero_seguro
    )";

    $stmt = $db->prepare($query);
    
    $params = [
        ':id_usuario' => $data['id_usuario'],
        ':fecha_nacimiento' => $data['fecha_nacimiento'],
        ':genero' => $data['genero'],
        ':alergias' => $data['alergias'] ?? null,
        ':enfermedades_cronicas' => $data['enfermedades_cronicas'] ?? null,
        ':medicamentos' => $data['medicamentos'] ?? null,
        ':seguro_medico' => $data['seguro_medico'] ?? null,
        ':numero_seguro' => $data['numero_seguro'] ?? null
    ];

    if ($stmt->execute($params)) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Error al registrar el paciente');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}