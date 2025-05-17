<?php
require '../../../php/database/conexion.php'; // Ajusta el path si es necesario
session_start();

if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401); // No autorizado
    echo json_encode(["error" => "Sesión no iniciada"]);
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

try {
    $sql = "SELECT t.id_tratamiento, t.nombre, e.nombre AS especialidad, t.descripcion, 
                   t.duracion_estimada, t.costo, t.requisitos 
            FROM tratamientos t
            JOIN dentistas d ON t.id_especialidad = d.id_especialidad
            LEFT JOIN especialidades e ON t.id_especialidad = e.id_especialidad
            WHERE d.id_usuario = ?";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$id_usuario]);
    $tratamientos = $stmt->fetchAll();

    echo json_encode($tratamientos);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error al obtener tratamientos"]);
}
?>
