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













function registrarLog($pdo, $id_usuario, $accion, $tabla_afectada, $id_registro, $datos_anteriores = null, $datos_nuevos = null) {
    $stmt = $pdo->prepare("INSERT INTO logs 
                          (id_usuario, accion, tabla_afectada, id_registro_afectado, 
                           datos_anteriores, datos_nuevos) 
                          VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $id_usuario,
        $accion,
        $tabla_afectada,
        $id_registro,
        $datos_anteriores ? json_encode($datos_anteriores) : null,
        $datos_nuevos ? json_encode($datos_nuevos) : null
    ]);
}


?>