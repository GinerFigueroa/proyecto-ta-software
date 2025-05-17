<?php
require_once __DIR__ . '/../../../../php/database/conexion.php';

header('Content-Type: application/json');

try {
    // Consulta para obtener todos los roles (sin filtrar por activo)
    $sql = "SELECT id_rol, nombre FROM roles ORDER BY nombre ASC";
    $stmt = $db->query($sql);
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($roles)) {
        throw new Exception('No se encontraron roles en la base de datos');
    }

    echo json_encode([
        'success' => true,
        'data' => $roles,
        'count' => count($roles)
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error de base de datos: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>