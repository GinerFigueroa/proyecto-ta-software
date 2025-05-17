<?php
require_once __DIR__ . '/../../../../php/database/conexion.php';

header('Content-Type: application/json');

$campo = $_GET['campo'] ?? '';
$valor = $_GET['valor'] ?? '';

// Validar parámetros
if (!in_array($campo, ['usuario', 'email']) || empty($valor)) {
    http_response_code(400);
    echo json_encode(['existe' => false, 'error' => 'Parámetros inválidos']);
    exit;
}

try {
    // Consulta para verificar existencia
    $campoDB = $campo === 'usuario' ? 'usuario_usuario' : 'email';
    $stmt = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE $campoDB = ?");
    $stmt->execute([$valor]);
    
    echo json_encode([
        'existe' => $stmt->fetchColumn() > 0,
        'campo' => $campo,
        'valor' => $valor
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['existe' => false, 'error' => 'Error en la verificación']);
}
?>
