<?php
require_once '../../../php/database/conexion.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

try {
    if (empty($data['id_paciente'])) {
        throw new Exception('ID de paciente no proporcionado');
    }

    $query = "DELETE FROM pacientes WHERE id_paciente = :id_paciente";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_paciente', $data['id_paciente']);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Paciente eliminado correctamente'
        ]);
    } else {
        throw new Exception('Error al eliminar el paciente');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>