<?php
require_once __DIR__ . '/../../../../php/database/conexion.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

$db = new Database();
$conn = $db->getConnection();
$accion = $_GET['accion'] ?? '';

try {
    switch ($accion) {
        case 'cargarPacientes':
            $query = "SELECT p.id_paciente, u.nombre_apellido AS nombre_completo 
                      FROM pacientes p 
                      JOIN usuarios u ON p.id_usuario = u.id_usuario 
                      WHERE u.id_rol = 4 
                      ORDER BY u.nombre_apellido";
            break;

        case 'cargarTratamientos':
            $query = "SELECT id_tratamiento, nombre, duracion_estimada 
                      FROM tratamientos  
                      WHERE activo = 1 
                      ORDER BY nombre";
            break;

        case 'cargarDentistas':
            $query = "SELECT d.id_dentista, u.nombre_apellido AS nombre_completo 
                      FROM dentistas d 
                      JOIN usuarios u ON d.id_usuario = u.id_usuario 
                      WHERE u.id_rol = 2 
                      ORDER BY u.nombre_apellido";
            break;

        default:
            throw new Exception('Acción no válida');
    }

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($resultados)) {
        throw new Exception("No se encontraron registros");
    }

    echo json_encode($resultados);
    
} catch (PDOException $e) {
    echo json_encode([
        'error' => 'Error de base de datos: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
?>
