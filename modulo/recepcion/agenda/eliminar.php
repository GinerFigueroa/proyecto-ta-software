<?php
require_once '../../../php/database/conexion.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

try {
    if (empty($data['id_cita'])) {
        throw new Exception('ID de cita no proporcionado');
    }

    $query = "DELETE FROM citas WHERE id_cita = :id_cita";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_cita', $data['id_cita'], PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Cita eliminada correctamente'
        ]);
    } else {
        throw new Exception('Error al eliminar la cita');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>