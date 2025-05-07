<?php
try {
    require_once '../../../php/database/conexion.php';
    header('Content-Type: application/json');

    $searchTerm = isset($_GET['search']) ? "%{$_GET['search']}%" : '%';

    $query = "SELECT 
                p.id_paciente, 
                u.nombre_apellido AS nombre, 
                u.telefono,
                p.fecha_nacimiento, 
                TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) AS edad, 
                p.genero, 
                p.alergias, 
                p.enfermedades_cronicas, 
                p.medicamentos, 
                p.seguro_medico, 
                p.numero_seguro 
              FROM pacientes p
              JOIN usuarios u ON p.id_usuario = u.id_usuario
              WHERE u.nombre_apellido LIKE :search
              ORDER BY p.id_paciente DESC
              LIMIT 0, 25";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':search', $searchTerm);
    $stmt->execute();
    $pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["data" => $pacientes], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>