<?php
require_once __DIR__ . '/../../../../php/database/conexion.php';
session_start();

header('Content-Type: application/json');

// Verificar sesión y rol
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 2) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validar campos requeridos
    $required = ['id_paciente', 'fecha_procedimiento', 'diagnostico'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("El campo $field es obligatorio");
        }
    }

    $db = new Database();
    $conn = $db->getConnection();

    // Obtener id_dentista del usuario actual
    $stmt = $conn->prepare("SELECT id_dentista FROM dentistas WHERE id_usuario = ?");
    $stmt->execute([$_SESSION['id_usuario']]);
    $dentista = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$dentista) {
        throw new Exception('Dentista no encontrado');
    }

    $query = "INSERT INTO historial_medico (
                id_paciente, 
                id_dentista,
                fecha_procedimiento, 
                id_tratamiento, 
                diagnostico, 
                procedimiento, 
                observaciones, 
                receta, 
                proxima_visita
              ) VALUES (
                :id_paciente, 
                :id_dentista,
                :fecha_procedimiento, 
                :id_tratamiento, 
                :diagnostico, 
                :procedimiento, 
                :observaciones, 
                :receta, 
                :proxima_visita
              )";

    $stmt = $conn->prepare($query);
    
    $success = $stmt->execute([
        ':id_paciente' => $data['id_paciente'],
        ':id_dentista' => $dentista['id_dentista'],
        ':fecha_procedimiento' => $data['fecha_procedimiento'],
        ':id_tratamiento' => $data['id_tratamiento'] ?? null,
        ':diagnostico' => $data['diagnostico'],
        ':procedimiento' => $data['procedimiento'] ?? null,
        ':observaciones' => $data['observaciones'] ?? null,
        ':receta' => $data['receta'] ?? null,
        ':proxima_visita' => $data['proxima_visita'] ?? null
    ]);

    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'Historial médico registrado exitosamente',
            'id' => $conn->lastInsertId()
        ]);
    } else {
        throw new Exception('Error al registrar el historial');
    }

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Error de base de datos: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>