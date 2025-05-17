<?php
require_once __DIR__ . '/../../../../php/database/conexion.php';
header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->getConnection();

    $query = "SELECT id_especialidad, nombre 
              FROM especialidades 
              ORDER BY nombre";
    $stmt = $conn->query($query);
    
    $especialidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($especialidades);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}