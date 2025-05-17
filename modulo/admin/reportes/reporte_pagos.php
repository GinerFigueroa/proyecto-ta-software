<?php
// reporte_pagos.php
require_once __DIR__ . '/../../../php/database/conexion.php';
  use Dompdf\Dompdf;

// 1. Establecer fechas por defecto (últimos 30 días)
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-d', strtotime('-30 days'));
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-d');

// 2. Verificar si se solicita exportar a PDF
$es_pdf = isset($_GET['exportar_pdf']);

// 3. Consulta principal de pagos
try {
    $db = (new Database())->getConnection();
    
    // Consulta para obtener pagos y estadísticas
    $stmt = $db->prepare("
        SELECT 
            p.*, 
            c.fecha_hora, 
            u.nombre_apellido AS paciente,
            d.nombre_apellido AS dentista,
            t.nombre AS tratamiento
        FROM pagos p
        LEFT JOIN citas c ON p.id_cita = c.id_cita
        LEFT JOIN pacientes pa ON c.id_paciente = pa.id_paciente
        LEFT JOIN usuarios u ON pa.id_usuario = u.id_usuario
        LEFT JOIN dentistas de ON c.id_dentista = de.id_dentista
        LEFT JOIN usuarios d ON de.id_usuario = d.id_usuario
        LEFT JOIN tratamientos t ON c.id_tratamiento = t.id_tratamiento
        WHERE (p.fecha_pago BETWEEN :fecha_inicio AND :fecha_fin) 
           OR (p.fecha_pago IS NULL AND p.creado_en BETWEEN :fecha_inicio AND :fecha_fin)
        ORDER BY p.fecha_pago DESC
    ");
    
    $stmt->execute([
        ':fecha_inicio' => $fecha_inicio,
        ':fecha_fin' => $fecha_fin
    ]);
    $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Estadísticas generales
    $stmt_stats = $db->prepare("
        SELECT 
            COUNT(*) AS total_pagos,
            SUM(CASE WHEN estado = 'Completado' THEN 1 ELSE 0 END) AS completados,
            SUM(CASE WHEN estado = 'Cancelado' THEN 1 ELSE 0 END) AS cancelados,
            SUM(CASE WHEN estado = 'Pendiente' THEN 1 ELSE 0 END) AS pendientes,
            SUM(CASE WHEN estado = 'Completado' THEN monto ELSE 0 END) AS ingresos_totales,
            AVG(monto) AS promedio_pago,
            MAX(monto) AS pago_maximo,
            MIN(monto) AS pago_minimo
        FROM pagos
        WHERE (fecha_pago BETWEEN :fecha_inicio AND :fecha_fin) 
           OR (fecha_pago IS NULL AND creado_en BETWEEN :fecha_inicio AND :fecha_fin)
    ");
    
    $stmt_stats->execute([
        ':fecha_inicio' => $fecha_inicio,
        ':fecha_fin' => $fecha_fin
    ]);
    $estadisticas = $stmt_stats->fetch(PDO::FETCH_ASSOC);
    
    // Datos para gráficos (solo en vista web)
    if (!$es_pdf) {
        $stmt_metodos = $db->prepare("
            SELECT 
                metodo_pago, 
                SUM(monto) as total,
                COUNT(*) as cantidad
            FROM pagos 
            WHERE estado = 'Completado' AND fecha_pago BETWEEN :fecha_inicio AND :fecha_fin
            GROUP BY metodo_pago
        ");
        $stmt_metodos->execute([
            ':fecha_inicio' => $fecha_inicio,
            ':fecha_fin' => $fecha_fin
        ]);
        $metodos = $stmt_metodos->fetchAll(PDO::FETCH_ASSOC);
        
        $stmt_diarios = $db->prepare("
            SELECT 
                DATE(fecha_pago) as fecha,
                SUM(monto) as total
            FROM pagos
            WHERE estado = 'Completado' AND fecha_pago BETWEEN :fecha_inicio AND :fecha_fin
            GROUP BY DATE(fecha_pago)
            ORDER BY fecha_pago
        ");
        $stmt_diarios->execute([
            ':fecha_inicio' => $fecha_inicio,
            ':fecha_fin' => $fecha_fin
        ]);
        $ingresos_diarios = $stmt_diarios->fetchAll(PDO::FETCH_ASSOC);
    }
    
} catch (PDOException $e) {
    die("Error al generar el reporte: " . $e->getMessage());
}

// 4. Configuración para PDF
if ($es_pdf) {
    require_once __DIR__ . '/../../../dompdf/autoload.inc.php';
  
    $dompdf = new Dompdf();

    // Construir el contenido HTML
    $html = '
    <h1 style="text-align:center;">Reporte de Pagos</h1>
    <p style="text-align:center;">Del ' . date('d/m/Y', strtotime($fecha_inicio)) . ' al ' . date('d/m/Y', strtotime($fecha_fin)) . '</p>
    
    <h3>Resumen Financiero</h3>
    <table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse; font-size: 12px;">
        <thead>
            <tr>
                <th width="25%">Total Pagos</th>
                <th width="25%">Ingresos Totales</th>
                <th width="25%">Pago Promedio</th>
                <th width="25%">Pago Máximo</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>' . $estadisticas['total_pagos'] . '</td>
                <td>S/ ' . number_format($estadisticas['ingresos_totales'], 2) . '</td>
                <td>S/ ' . number_format($estadisticas['promedio_pago'], 2) . '</td>
                <td>S/ ' . number_format($estadisticas['pago_maximo'], 2) . '</td>
            </tr>
        </tbody>
    </table>

    <br><h3>Detalle de Pagos</h3>
    <table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse; font-size: 12px;">
        <thead>
            <tr>
                <th width="10%">ID</th>
                <th width="15%">Fecha</th>
                <th width="20%">Paciente</th>
                <th width="15%">Monto</th>
                <th width="15%">Método</th>
                <th width="15%">Estado</th>
                <th width="10%">Tratamiento</th>
            </tr>
        </thead>
        <tbody>';

    foreach ($pagos as $pago) {
        $html .= '
            <tr>
                <td>' . $pago['id_pago'] . '</td>
                <td>' . ($pago['fecha_pago'] ? date('d/m/Y H:i', strtotime($pago['fecha_pago'])) : 'Pendiente') . '</td>
                <td>' . htmlspecialchars($pago['paciente'] ?? 'No asignado') . '</td>
                <td>S/ ' . number_format($pago['monto'], 2) . '</td>
                <td>' . ($pago['metodo_pago'] ?? 'No especificado') . '</td>
                <td>' . $pago['estado'] . '</td>
                <td>' . ($pago['tratamiento'] ?? 'N/A') . '</td>
            </tr>';
    }

    $html .= '</tbody></table>';

    // Cargar HTML y generar PDF
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Descargar el archivo
    $dompdf->stream('reporte_pagos_' . date('Ymd') . '.pdf', ['Attachment' => true]);
    exit;
}

// 5. Vista HTML normal
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Pagos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <style>
        .card {
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .chart-container {
            position: relative;
            height: 300px;
        }
        .badge-completado { background-color: #28a745; }
        .badge-pendiente { background-color: #ffc107; color: #212529; }
        .badge-cancelado { background-color: #dc3545; }
        .badge-reembolsado { background-color: #6c757d; }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Reporte de Pagos</h4>
                        <div>
                            <a href="reporte_pagos.php?exportar_pdf=1&fecha_inicio=<?= $fecha_inicio ?>&fecha_fin=<?= $fecha_fin ?>" 
                               class="btn btn-danger btn-sm">
                                <i class="fas fa-file-pdf"></i> Exportar PDF
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filtros de fecha -->
                        <form method="get" class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                                <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                                       value="<?= $fecha_inicio ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label for="fecha_fin" class="form-label">Fecha Fin</label>
                                <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" 
                                       value="<?= $fecha_fin ?>" required>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Filtrar
                                </button>
                                <a href="reporte_pagos.php" class="btn btn-secondary ms-2">
                                    <i class="fas fa-sync-alt"></i> Resetear
                                </a>
                            </div>
                        </form>
                        
                        <!-- Resumen Estadístico -->
                        <div class="row text-center mb-4">
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5 class="text-muted">Total Pagos</h5>
                                        <h3><?= $estadisticas['total_pagos'] ?? 0 ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5 class="text-success">Completados</h5>
                                        <h3><?= $estadisticas['completados'] ?? 0 ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5 class="text-danger">Cancelados</h5>
                                        <h3><?= $estadisticas['cancelados'] ?? 0 ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5 class="text-primary">Ingresos Totales</h5>
                                        <h3>S/ <?= number_format($estadisticas['ingresos_totales'] ?? 0, 2) ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tabla de pagos -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="tablaPagos">
                                <thead class="table-primary">
                                    <tr>
                                        <th>ID</th>
                                        <th>Fecha</th>
                                        <th>Paciente</th>
                                        <th>Monto</th>
                                        <th>Método</th>
                                        <th>Estado</th>
                                        <th>Tratamiento</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pagos as $pago): ?>
                                    <tr>
                                        <td><?= $pago['id_pago'] ?></td>
                                        <td>
                                            <?= $pago['fecha_pago'] ? date('d/m/Y H:i', strtotime($pago['fecha_pago'])) : 'Pendiente' ?>
                                            <?php if($pago['fecha_hora'] && !$es_pdf): ?>
                                            <div class="text-muted small">
                                                Cita: <?= date('d/m/Y H:i', strtotime($pago['fecha_hora'])) ?>
                                            </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($pago['paciente'] ?? 'No asignado') ?>
                                            <?php if($pago['dentista'] && !$es_pdf): ?>
                                            <div class="text-muted small">
                                                Dentista: <?= htmlspecialchars($pago['dentista']) ?>
                                            </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>S/ <?= number_format($pago['monto'], 2) ?></td>
                                        <td><?= $pago['metodo_pago'] ?? 'No especificado' ?></td>
                                        <td>
                                            <span class="badge badge-<?= strtolower($pago['estado']) ?>">
                                                <?= $pago['estado'] ?>
                                            </span>
                                        </td>
                                        <td><?= $pago['tratamiento'] ?? 'N/A' ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="verDetallePago(<?= $pago['id_pago'] ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary" 
                                                    onclick="editarPago(<?= $pago['id_pago'] ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Gráficos -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Métodos de Pago</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="metodosPagoChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Ingresos Diarios</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="ingresosDiariosChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // DataTable para pagos
        $(document).ready(function() {
            $('#tablaPagos').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
                },
                dom: '<"top"f>rt<"bottom"lip><"clear">',
                pageLength: 10,
                order: [[1, 'desc']]
            });
        });
        
        // Gráfico de métodos de pago
        const ctxMetodos = document.getElementById('metodosPagoChart').getContext('2d');
        const metodosPagoChart = new Chart(ctxMetodos, {
            type: 'pie',
            data: {
                labels: <?= json_encode(array_column($metodos, 'metodo_pago')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($metodos, 'total')) ?>,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: S/ ${value.toFixed(2)} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
        
        // Gráfico de ingresos diarios
        const ctxDiarios = document.getElementById('ingresosDiariosChart').getContext('2d');
        const ingresosDiariosChart = new Chart(ctxDiarios, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_map(function($item) { 
                    return date('d/m', strtotime($item['fecha'])); 
                }, $ingresos_diarios)) ?>,
                datasets: [{
                    label: 'Ingresos Diarios',
                    data: <?= json_encode(array_column($ingresos_diarios, 'total')) ?>,
                    backgroundColor: '#36A2EB',
                    borderColor: '#1E88E5',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'S/ ' + value.toFixed(2);
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'S/ ' + context.raw.toFixed(2);
                            }
                        }
                    }
                }
            }
        });
        
        // Funciones de acción
        function verDetallePago(idPago) {
            // Implementar lógica para ver detalles
            alert('Mostrando detalles del pago ID: ' + idPago);
        }
        
        function editarPago(idPago) {
            // Implementar lógica para editar pago
            alert('Editando pago ID: ' + idPago);
        }
    </script>
</body>
</html>
