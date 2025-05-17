<?php
require_once __DIR__ . '/../../../../php/database/conexion.php';



header('Content-Type: application/json');

if (empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID de usuario no proporcionado']);
    exit;
}

try {
    // Obtener datos del usuario
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
    $stmt->execute([$_GET['id']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Usuario no encontrado']);
        exit;
    }

    // Obtener lista de roles
    $stmt = $db->query("SELECT id_rol, nombre FROM roles ORDER BY nombre");
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'usuario' => $usuario,
        'roles' => $roles
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al obtener datos: ' . $e->getMessage()]);
}
?>
