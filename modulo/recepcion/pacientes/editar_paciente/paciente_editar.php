<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../../php/database/conexion.php';

try {
    if (!isset($_GET['id_paciente']) || !is_numeric($_GET['id_paciente'])) {
        throw new Exception('ID de paciente inválido');
    }
    
    $idPaciente = (int)$_GET['id_paciente'];
    
    $query = "SELECT p.*, u.nombre_apellido 
              FROM pacientes p
              INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
              WHERE p.id_paciente = :id_paciente";
              
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_paciente', $idPaciente, PDO::PARAM_INT);
    $stmt->execute();
    
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$data) {
        throw new Exception('Paciente no encontrado');
    }
    
    echo json_encode($data);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage(),
        'trace' => $e->getTrace()
    ]);
}