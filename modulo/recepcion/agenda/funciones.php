<?php
require_once '../../../php/database/conexion.php';

header('Content-Type: application/json');


$accion = $_GET['accion'] ?? '';

try {
    switch ($accion) {
        case 'cargarPacientes':
            // Pacientes (usuarios con rol 4)
            $query = "SELECT   
                        p.id_paciente,  
                        u.nombre_apellido AS nombre_completo 
                      FROM pacientes p 
                      JOIN usuarios u ON p.id_usuario = u.id_usuario 
                      WHERE u.id_rol = 4 
                      ORDER BY u.nombre_apellido";
            break;

        case 'cargarTratamientos':
            // Tratamientos activos
            $query = "SELECT  
                        id_tratamiento,  
                        nombre, 
                        duracion_estimada 
                      FROM tratamientos  
                      WHERE activo = 1 
                      ORDER BY nombre";
            break;

        case 'cargarDentistas':
            // Dentistas (usuarios con rol 2)
            $query = "SELECT  
                        d.id_dentista, 
                        u.nombre_apellido AS nombre_completo 
                      FROM dentistas d 
                      JOIN usuarios u ON d.id_usuario = u.id_usuario 
                      WHERE u.id_rol = 2 
                      ORDER BY u.nombre_apellido";
            break;

        default:
            throw new Exception('Acción no válida');
    }

   


    $stmt = $db->prepare($query);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($resultados);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>