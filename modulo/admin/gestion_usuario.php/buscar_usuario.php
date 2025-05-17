<?php
require_once '../../../php/database/conexion.php';

header('Content-Type: application/json');

try {
    // Consulta para obtener usuarios con información de rol
    $sql = "SELECT 
                u.id_usuario, 
                u.usuario_usuario, 
                u.email, 
                u.nombre_apellido, 
                u.telefono, 
                u.activo,
                u.ultimo_login,
                r.nombre as rol_nombre,
                r.id_rol
            FROM usuarios u
            JOIN roles r ON u.id_rol = r.id_rol
            ORDER BY u.nombre_apellido";
    
    $stmt = $db->query($sql);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $usuarios,
        'total' => count($usuarios)
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al obtener usuarios: ' . $e->getMessage()
    ]);
}
?>
