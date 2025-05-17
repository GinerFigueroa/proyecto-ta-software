<?php
require_once __DIR__ . '/../../../php/database/conexion.php';
header('Content-Type: application/json');

try {
    // Validar ID (ahora viene por GET)
    $id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;

    if (!$id || $id < 1) {
        throw new Exception('ID de tratamiento inválido.');
    }

    // Usar la conexión global $db en lugar de crear una nueva
    if (!$db) {
        throw new Exception('No se pudo conectar a la base de datos.');
    }

    // Ejecutar eliminación con transacción
    $db->beginTransaction();
    $stmt = $db->prepare("DELETE FROM tratamientos WHERE id_tratamiento = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        $db->rollBack();
        throw new Exception('Tratamiento no encontrado.');
    }

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Tratamiento eliminado correctamente.'
    ]);
} catch (PDOException $e) {
    error_log("Error de BD: " . $e->getMessage(), 3, 'errores.log');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error en la base de datos.'
    ]);
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage(), 3, 'errores.log');
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>