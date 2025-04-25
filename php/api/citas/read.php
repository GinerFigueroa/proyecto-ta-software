<?php
require '../../auth/check-auth.php';
require '../../database/conexion.php';

header('Content-Type: application/json');

$tokenData = checkAuth(['admin', 'cliente']); // Ambos roles

$queryParams = $_GET;
$where = [];
$params = [];

// Filtros para admin vs cliente
if ($tokenData->rol === 'admin') {
    if (!empty($queryParams['id_paciente'])) {
        $where[] = "c.id_paciente = ?";
        $params[] = $queryParams['id_paciente'];
    }
} else {
    $where[] = "c.id_paciente = ?";
    $params[] = $tokenData->paciente_id;
}

// Filtro por estado
if (!empty($queryParams['estado'])) {
    $where[] = "c.estado = ?";
    $params[] = $queryParams['estado'];
}

// Filtro por fecha
if (!empty($queryParams['fecha'])) {
    $where[] = "DATE(c.fecha_hora) = ?";
    $params[] = $queryParams['fecha'];
}

// Construir consulta
$sql = "
    SELECT 
        c.id_cita,
        p.nombre as paciente,
        t.nombre as tratamiento,
        c.fecha_hora,
        c.estado,
        u.nombre as doctor
    FROM citas c
    JOIN pacientes p ON c.id_paciente = p.id_paciente
    JOIN tratamientos t ON c.id_tratamiento = t.id_tratamiento
    LEFT JOIN usuarios u ON c.id_doctor = u.id_usuario
";

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY c.fecha_hora DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>