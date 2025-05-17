<?php
require_once '../../../php/database/conexion.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID de usuario no proporcionado']);
    exit;
}

try {
    // Verificar si el usuario existe
    $stmt = $db->prepare("SELECT id_usuario FROM usuarios WHERE id_usuario = ?");
    $stmt->execute([$data['id']]);
    
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Usuario no encontrado']);
        exit;
    }

    // Eliminación lógica (recomendado)
    $stmt = $db->prepare("UPDATE usuarios SET activo = 0 WHERE id_usuario = ?");
    $stmt->execute([$data['id']]);
    
    // Alternativa para eliminación física (no recomendado si hay relaciones)
    // $stmt = $db->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
    
    echo json_encode(['success' => true, 'message' => 'Usuario eliminado correctamente']);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al eliminar usuario: ' . $e->getMessage()]);
}
?>
