<?php
require_once '../../../php/database/conexion.php';
session_start();

if (!isset($_SESSION['id_usuario'])) {
    die(json_encode(['success' => false, 'message' => 'No autenticado']));
}
if ($_SESSION['id_rol'] != 2) {
    die(json_encode(['success' => false, 'message' => 'No autorizado']));
}

if (!isset($_GET['id'])) {
    die(json_encode(['success' => false, 'message' => 'ID no especificado']));
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Verificar pertenencia del historial
    $stmt = $conn->prepare("
        DELETE h FROM historial_medico h
        JOIN dentistas d ON h.id_dentista = d.id_dentista
        WHERE h.id_historial = ? AND d.id_usuario = ?
    ");
    
    $stmt->execute([$_GET['id'], $_SESSION['id_usuario']]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Historial eliminado']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Registro no encontrado o no autorizado']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}