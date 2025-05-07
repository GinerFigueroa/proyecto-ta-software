<?php
require_once '../../../php/database/conexion.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    die(json_encode(['error' => 'No autenticado']));
}
try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Obtener especialidad del dentista
    $stmt = $conn->prepare("
        SELECT d.id_especialidad 
        FROM dentistas d
        WHERE d.id_usuario = ?
    ");
    $stmt->execute([$_SESSION['id_usuario']]);
    $dentista = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$dentista) {
        die(json_encode(['error' => 'Dentista no encontrado']));
    }

    $sql = "SELECT t.id_tratamiento, t.nombre, t.duracion_estimada as duracion, 
                   t.costo, e.nombre as especialidad
            FROM tratamientos t
            LEFT JOIN especialidades e ON t.id_especialidad = e.id_especialidad
            WHERE t.id_especialidad = ? OR t.id_especialidad IS NULL
            ORDER BY t.nombre";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$dentista['id_especialidad']]);
    
    $tratamientos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($tratamientos);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}