<?php
require_once '../../includes/conexion.php';

header('Content-Type: application/json');

try {
    // Búsqueda por ID (para detalles)
    if (isset($_GET['id'])) {
        $id_pago = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        
        if (!$id_pago) {
            echo json_encode(['success' => false, 'message' => 'ID de pago inválido']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT p.*, 
                                      c.id_cita, c.fecha_hora,
                                      pa.id_paciente,
                                      u.nombre as nombre_paciente, u.apellido as apellido_paciente,
                                      t.nombre as nombre_tratamiento
                               FROM pagos p
                               LEFT JOIN citas c ON p.id_cita = c.id_cita
                               LEFT JOIN pacientes pa ON c.id_paciente = pa.id_paciente
                               LEFT JOIN usuarios u ON pa.id_usuario = u.id_usuario
                               LEFT JOIN tratamientos t ON c.id_tratamiento = t.id_tratamiento
                               WHERE p.id_pago = ?");
        $stmt->execute([$id_pago]);
        $pago = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($pago) {
            echo json_encode($pago);
        } else {
            echo json_encode(['success' => false, 'message' => 'Pago no encontrado']);
        }
        exit;
    }

    // Resumen de pagos
    if (isset($_GET['resumen'])) {
        // Pagos de hoy
        $hoy_inicio = date('Y-m-d 00:00:00');
        $hoy_fin = date('Y-m-d 23:59:59');
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as cantidad, COALESCE(SUM(monto), 0) as monto 
                               FROM pagos 
                               WHERE fecha_pago BETWEEN ? AND ? 
                               AND estado = 'Completado'");
        $stmt->execute([$hoy_inicio, $hoy_fin]);
        $hoy = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Pagos del mes
        $mes_inicio = date('Y-m-01 00:00:00');
        $mes_fin = date('Y-m-t 23:59:59');
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as cantidad, COALESCE(SUM(monto), 0) as monto 
                               FROM pagos 
                               WHERE fecha_pago BETWEEN ? AND ? 
                               AND estado = 'Completado'");
        $stmt->execute([$mes_inicio, $mes_fin]);
        $mes = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Pagos pendientes
        $stmt = $pdo->prepare("SELECT COUNT(*) as cantidad, COALESCE(SUM(monto), 0) as monto 
                               FROM pagos 
                               WHERE estado = 'Pendiente'");
        $stmt->execute();
        $pendientes = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Pagos reembolsados
        $stmt = $pdo->prepare("SELECT COUNT(*) as cantidad, COALESCE(SUM(monto), 0) as monto 
                               FROM pagos 
                               WHERE estado = 'Reembolsado'");
        $stmt->execute();
        $reembolsados = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'hoy' => $hoy,
            'mes' => $mes,
            'pendientes' => $pendientes,
            'reembolsados' => $reembolsados
        ]);
        exit;
    }

    // Búsqueda con paginación
    $fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] . ' 00:00:00' : null;
    $fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] . ' 23:59:59' : null;
    $estado = isset($_GET['estado']) ? $_GET['estado'] : null;
    $metodo = isset($_GET['metodo']) ? $_GET['metodo'] : null;
    
    $pagina = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT) ?? 1;
    $limite = filter_input(INPUT_GET, 'limite', FILTER_VALIDATE_INT) ?? 10;
    $offset = ($pagina - 1) * $limite;

    // Consulta base con JOIN para obtener información de pacientes
    $sql = "SELECT SQL_CALC_FOUND_ROWS 
                   p.id_pago, p.monto, p.metodo_pago, p.estado, p.fecha_pago,
                   p.referencia, p.id_cita,
                   u.nombre as nombre_paciente, u.apellido as apellido_paciente
            FROM pagos p
            LEFT JOIN citas c ON p.id_cita = c.id_cita
            LEFT JOIN pacientes pa ON c.id_paciente = pa.id_paciente
            LEFT JOIN usuarios u ON pa.id_usuario = u.id_usuario
            WHERE 1=1";
    
    $params = [];
    
    // Aplicar filtros
    if ($fecha_inicio && $fecha_fin) {
        $sql .= " AND p.fecha_pago BETWEEN ? AND ?";
        $params[] = $fecha_inicio;
        $params[] = $fecha_fin;
    }
    
    if ($estado) {
        $sql .= " AND p.estado = ?";
        $params[] = $estado;
    }
    
    if ($metodo) {
        $sql .= " AND p.metodo_pago = ?";
        $params[] = $metodo;
    }
    
    // Ordenación y paginación
    $sql .= " ORDER BY p.fecha_pago DESC LIMIT ? OFFSET ?";
    $params[] = $limite;
    $params[] = $offset;

    // Ejecutar consulta
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener total de registros
    $stmt = $pdo->query("SELECT FOUND_ROWS() as total");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPaginas = ceil($total / $limite);

    // Respuesta
    echo json_encode([
        'pagos' => $pagos,
        'total' => $total,
        'pagina_actual' => $pagina,
        'total_paginas' => $totalPaginas
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al buscar pagos: ' . $e->getMessage()
    ]);
}
?>