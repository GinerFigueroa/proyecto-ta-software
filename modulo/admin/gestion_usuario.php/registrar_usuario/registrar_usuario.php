<?php
require_once __DIR__ . '/../../../../php/database/conexion.php';

header('Content-Type: application/json');

// Verificar método y contenido
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

$json = file_get_contents('php://input');
if (empty($json)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Datos JSON no proporcionados']);
    exit;
}

$data = json_decode($json, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'JSON inválido: ' . json_last_error_msg()]);
    exit;
}

// Validación de campos
$required = ['nombre_apellido', 'usuario_usuario', 'email', 'id_rol', 'usuario_clave'];
foreach ($required as $field) {
    if (!isset($data[$field]) || empty(trim($data[$field]))) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => "El campo $field es requerido"]);
        exit;
    }
}

// Validación específica
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Email no válido']);
    exit;
}

if (strlen($data['usuario_clave']) < 8) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'La contraseña debe tener al menos 8 caracteres']);
    exit;
}

try {
    // Verificar que el rol existe
    $stmt = $db->prepare("SELECT id_rol FROM roles WHERE id_rol = ?");
    $stmt->execute([$data['id_rol']]);
    $rol = $stmt->fetch();
    
    if (!$rol) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'El rol seleccionado no existe']);
        exit;
    }

    // Verificar duplicados
    $stmt = $db->prepare("SELECT id_usuario FROM usuarios WHERE usuario_usuario = ? OR email = ?");
    $stmt->execute([$data['usuario_usuario'], $data['email']]);
    
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'error' => 'El nombre de usuario o email ya está registrado']);
        exit;
    }

    // Insertar usuario (con contraseña hasheada)
    $hashedPassword = password_hash($data['usuario_clave'], PASSWORD_BCRYPT);
    
    $stmt = $db->prepare("INSERT INTO usuarios (
        id_rol, usuario_usuario, email, usuario_clave, 
        nombre_apellido, telefono, activo
    ) VALUES (?, ?, ?, ?, ?, ?, ?)");

    $stmt->execute([
        $data['id_rol'],
        $data['usuario_usuario'],
        $data['email'],
        $hashedPassword,
        $data['nombre_apellido'],
        $data['telefono'] ?? null,
        $data['activo'] ?? 1
    ]);

    echo json_encode([
        'success' => true,
        'id' => $db->lastInsertId(),
        'message' => 'Usuario registrado correctamente'
    ]);
    
} catch (PDOException $e) {
    error_log("Error en registro de usuario: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error en el servidor',
        'debug' => $e->getMessage() // Solo en desarrollo
    ]);
}
?>