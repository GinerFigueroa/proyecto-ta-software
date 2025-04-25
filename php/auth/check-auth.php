<?php
require '../libs/JWT.php';

function checkAuth($rolesPermitidos = []) {
    $headers = getallheaders();
    $token = $headers['Authorization'] ?? str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION'] ?? '');
    
    try {
        $decoded = JWT::decode($token, 'clave_secreta_zazdent_2024', ['HS256']);
        
        // Verificar rol
        if (!empty($rolesPermitidos) && !in_array($decoded->rol, $rolesPermitidos)) {
            http_response_code(403);
            die(json_encode(['error' => 'Acceso no autorizado para tu rol']));
        }
        
        return $decoded; // Datos del token decodificados
        
    } catch (Exception $e) {
        http_response_code(401);
        die(json_encode(['error' => 'Token inválido: ' . $e->getMessage()]));
    }
}
?>