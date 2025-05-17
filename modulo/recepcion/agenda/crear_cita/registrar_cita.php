<?php
ob_start();
require_once __DIR__ . '/../../../../php/database/conexion.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método no permitido", 405);
    }

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("JSON inválido: " . json_last_error_msg(), 400);
    }
    
// Validación completa
$required = ['id_paciente', 'id_tratamiento', 'fecha_hora'];
foreach ($required as $field) {
    if (!isset($data[$field])) { // Corregido: se cerró correctamente el paréntesis
        throw new Exception("Campo requerido: $field", 400);
    }
}

    $db = new Database();
    $conn = $db->getConnection();

    $query = "INSERT INTO citas (
                id_paciente, id_tratamiento, id_dentista, 
                fecha_hora, duracion, estado, 
                notas, recordatorio_enviado, creado_por
              ) VALUES (
                :id_paciente, :id_tratamiento, :id_dentista, 
                :fecha_hora, :duracion, :estado, 
                :notas, :recordatorio, :creado_por
              )";

    $stmt = $conn->prepare($query);
    
    $stmt->execute([
        ':id_paciente' => $data['id_paciente'],
        ':id_tratamiento' => $data['id_tratamiento'],
        ':id_dentista' => $data['id_dentista'] ?? null,
        ':fecha_hora' => date('Y-m-d H:i:s', strtotime($data['fecha_hora'])),
        ':duracion' => $data['duracion'] ?? 30,
        ':estado' => $data['estado'] ?? 'Pendiente',
        ':notas' => $data['notas'] ?? null,
        ':recordatorio' => $data['recordatorio_enviado'] ?? 0,
        ':creado_por' => $data['creado_por'] ?? 1
    ]);

    echo json_encode([
        'success' => true,
        'id' => $conn->lastInsertId(),
        'message' => 'Cita creada exitosamente'
    ]);

} catch (PDOException $e) {
    error_log("PDO Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Error de base de datos: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("General Error: " . $e->getMessage());
    http_response_code($e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    ob_end_flush();
}
?>