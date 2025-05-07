<?php
require_once __DIR__ . '/../../../../php/database/conexion.php';
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validar campos requeridos
    $required = ['id_paciente', 'fecha_procedimiento', 'diagnostico'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("El campo $field es obligatorio");
        }
    }

    $db = new Database();
    $conn = $db->getConnection();

    $query = "INSERT INTO historial_medico (
                id_paciente, 
                fecha_procedimiento, 
                id_tratamiento, 
                diagnostico, 
                procedimiento, 
                observaciones, 
                receta, 
                proxima_visita
              ) VALUES (
                :id_paciente, 
                :fecha_procedimiento, 
                :id_tratamiento, 
                :diagnostico, 
                :procedimiento, 
                :observaciones, 
                :receta, 
                :proxima_visita
              )";

    $stmt = $conn->prepare($query);
    
    $stmt->execute([
        ':id_paciente' => $data['id_paciente'],
        ':fecha_procedimiento' => $data['fecha_procedimiento'],
        ':id_tratamiento' => $data['id_tratamiento'] ?? null,
        ':diagnostico' => $data['diagnostico'],
        ':procedimiento' => $data['procedimiento'] ?? null,
        ':observaciones' => $data['observaciones'] ?? null,
        ':receta' => $data['receta'] ?? null,
        ':proxima_visita' => $data['proxima_visita'] ?? null
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Historial médico registrado exitosamente',
        'id' => $conn->lastInsertId()
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Error de base de datos: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>