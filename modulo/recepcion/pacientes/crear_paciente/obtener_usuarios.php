<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

require_once __DIR__ . '/../../../../php/database/conexion.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    $query = "SELECT u.id_usuario, u.nombre_apellido
FROM usuarios u
LEFT JOIN pacientes p ON u.id_usuario = p.id_usuario
WHERE u.id_rol = 4 AND p.id_usuario IS NULL;";
    $stmt = $conn->query($query);
    
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$usuarios) {
        throw new Exception("No hay pacientes registrados");
    }

    echo json_encode($usuarios);

} catch (PDOException $e) {
    echo json_encode([
        'error' => 'Error de base de datos: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}