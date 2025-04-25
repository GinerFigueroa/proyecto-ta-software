<?php
header('Content-Type: application/json');
require '../database/conexion.php';

$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

// 1. Buscar usuario
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

// 2. Validar credenciales
if ($user && password_verify($password, $user['password'])) {
    // 3. Generar token JWT
    require_once '../libs/JWT.php'; // Librería JWT
    
    $payload = [
        'sub' => $user['id_usuario'],
        'rol' => $user['rol'],
        'paciente_id' => $user['id_paciente'] ?? null,
        'iat' => time(),
        'exp' => time() + (60 * 60 * 4) // 4 horas de expiración
    ];
    
    $token = JWT::encode($payload, 'clave_secreta_zazdent_2024', 'HS256');
    
    // 4. Responder con token
    echo json_encode([
        'success' => true,
        'token' => $token,
        'user' => [
            'id' => $user['id_usuario'],
            'rol' => $user['rol'],
            'nombre' => $user['nombre'] ?? ''
        ]
    ]);
} else {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Credenciales inválidas']);
}
?>