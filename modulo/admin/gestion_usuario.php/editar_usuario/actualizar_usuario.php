<?php
require_once __DIR__ . '/../../../../php/database/conexion.php';


header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

// Validación básica
if (empty($data['id_usuario']) || empty($data['id_rol'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit;
}

try {
    // Verificar existencia del usuario
    $stmt = $db->prepare("SELECT id_usuario FROM usuarios WHERE id_usuario = ?");
    $stmt->execute([$data['id_usuario']]);
    
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Usuario no encontrado']);
        exit;
    }

    // Construir consulta dinámica
    $sql = "UPDATE usuarios SET 
            id_rol = :id_rol,
            nombre_apellido = :nombre_apellido,
            telefono = :telefono,
            activo = :activo";
    
    // Agregar campo de contraseña si se proporciona
    if (!empty($data['usuario_clave'])) {
        $data['usuario_clave'] = password_hash($data['usuario_clave'], PASSWORD_DEFAULT);
        $sql .= ", usuario_clave = :usuario_clave";
    }
    
    $sql .= " WHERE id_usuario = :id_usuario";

    // Ejecutar actualización
    $stmt = $db->prepare($sql);
    $stmt->execute([
        'id_rol' => $data['id_rol'],
        'nombre_apellido' => $data['nombre_apellido'] ?? '',
        'telefono' => $data['telefono'] ?? null,
        'activo' => $data['activo'] ?? 1,
        'id_usuario' => $data['id_usuario']
    ] + (!empty($data['usuario_clave']) ? ['usuario_clave' => $data['usuario_clave']] : []));

    echo json_encode(['success' => true, 'message' => 'Usuario actualizado correctamente']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al actualizar usuario: ' . $e->getMessage()]);
}
?>
