<?php
require_once __DIR__ . '/../../../php/database/conexion.php';
session_start();

header('Content-Type: application/json');

try {
    // Validar sesión y rol
    if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 2) {
        throw new Exception('Acceso no autorizado');
    }

    // Validar ID
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) throw new Exception('ID inválido');

    // Obtener dentista
    $stmt = $db->prepare("
        SELECT d.id_dentista 
        FROM dentistas d
        WHERE d.id_usuario = ?
    ");
    $stmt->execute([$_SESSION['id_usuario']]);
    $dentista = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$dentista) throw new Exception('Dentista no encontrado');

    // Eliminar con validación de pertenencia
    $stmt = $db->prepare("
        DELETE FROM historial_medico 
        WHERE id_historial = :id
        AND id_dentista = :id_dentista
    ");
    
    $stmt->execute([
        ':id' => $id,
        ':id_dentista' => $dentista['id_dentista']
    ]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('No se encontró el historial o no tienes permiso');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Historial eliminado correctamente'
    ]);

} catch (PDOException $e) {
    error_log("Error BD: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>