<?php

require_once __DIR__ . '/../../../../php/database/conexion.php';
header('Content-Type: application/json');


error_reporting(0); // Desactiva errores PHP no controlados

// Tu código...
$tipo = $_GET['tipo'] ?? '';

try {
    $db = new Database();
    $conn = $db->getConnection();

    switch ($tipo) {
       case 'pacientes':
    $query = "SELECT p.id_paciente, u.nombre_apellido 
              FROM pacientes p
              JOIN usuarios u ON p.id_usuario = u.id_usuario
              WHERE u.id_rol = 4";
    break;
            
        case 'tratamientos':
            $query = "SELECT id_tratamiento, nombre 
                      FROM tratamientos 
                      WHERE activo = 1
                      ORDER BY nombre";
            break;
            
        default:
            throw new Exception('Tipo de consulta no válido');
    }

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($resultados);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>