<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../../php/database/conexion.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validación completa del ID
    if (!isset($data['id_paciente'])) {
        throw new Exception('Parámetro ID faltante');
    }
    
    $idPaciente = filter_var($data['id_paciente'], FILTER_VALIDATE_INT);
    
    if ($idPaciente === false || $idPaciente < 1) {
        throw new Exception('ID debe ser un número entero positivo');
    }
    // Estructura completa de parámetros esperados
    $parametros = [
        'id_paciente' => $data['id_paciente'],
        'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
        'genero' => $data['genero'] ?? null,
        'alergias' => $data['alergias'] ?? null,
        'enfermedades_cronicas' => $data['enfermedades_cronicas'] ?? null,
        'medicamentos' => $data['medicamentos'] ?? null,
        'seguro_medico' => $data['seguro_medico'] ?? null,
        'numero_seguro' => $data['numero_seguro'] ?? null
    ];

    // Validar tipos de datos
    if (!is_int($parametros['id_paciente'])) {
        throw new Exception('ID debe ser numérico');
    }

    $query = "UPDATE pacientes SET
        fecha_nacimiento = :fecha_nacimiento,
        genero = :genero,
        alergias = :alergias,
        enfermedades_cronicas = :enfermedades_cronicas,
        medicamentos = :medicamentos,
        seguro_medico = :seguro_medico,
        numero_seguro = :numero_seguro
        WHERE id_paciente = :id_paciente";

    $stmt = $db->prepare($query);
    
    if ($stmt->execute($parametros)) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Error al actualizar: ' . implode(', ', $stmt->errorInfo()));
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
$requiredParams = [
    'id_paciente', 'id_usuario', 'fecha_nacimiento', 'genero',
    'alergias', 'enfermedades_cronicas', 'medicamentos',
    'seguro_medico', 'numero_seguro'
];

foreach ($requiredParams as $param) {
    if (!array_key_exists($param, $data)) {
        throw new Exception("Parámetro faltante: $param");
    }
}