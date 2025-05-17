<?php
require_once __DIR__ . '/../../../php/database/conexion.php';
require_once __DIR__ . '/../../../dompdf/autoload.inc.php';
use Dompdf\Dompdf;
// 1. Configuración inicial
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-d', strtotime('-30 days'));
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-d');
$especialidad_filtro = isset($_GET['especialidad']) ? $_GET['especialidad'] : '';

// 2. Obtener lista de especialidades para el filtro
try {
    $db = (new Database())->getConnection();
    
    $stmt_especialidades = $db->query("SELECT id_especialidad, nombre FROM especialidades ORDER BY nombre");
    $especialidades = $stmt_especialidades->fetchAll(PDO::FETCH_ASSOC);

    // 3. Consulta principal de tratamientos con filtros
    $sql = "
        SELECT 
            t.*, 
            e.nombre AS especialidad, 
            COUNT(DISTINCT c.id_cita) AS total_citas,
            SUM(p.monto) AS ingresos,
            AVG(p.monto) AS ingreso_promedio
        FROM tratamientos t
        LEFT JOIN especialidades e ON t.id_especialidad = e.id_especialidad
        LEFT JOIN citas c ON t.id_tratamiento = c.id_tratamiento 
            AND c.fecha_hora BETWEEN :fecha_inicio AND :fecha_fin
        LEFT JOIN pagos p ON c.id_cita = p.id_cita AND p.estado = 'Completado'
    ";

    $params = [
        ':fecha_inicio' => $fecha_inicio,
        ':fecha_fin' => $fecha_fin
    ];

    if (!empty($especialidad_filtro)) {
        $sql .= " WHERE t.id_especialidad = :especialidad";
        $params[':especialidad'] = $especialidad_filtro;
    }

    $sql .= " GROUP BY t.id_tratamiento ORDER BY total_citas DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $tratamientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Estadísticas generales
    $sql_stats = "
        SELECT 
            COUNT(DISTINCT t.id_tratamiento) AS total_tratamientos,
            SUM(COUNT(DISTINCT c.id_cita)) OVER () AS total_citas_general,
            AVG(t.costo) AS costo_promedio,
            SUM(p.monto) AS ingresos_totales,
            MAX(p.monto) AS ingreso_maximo,
            MIN(t.costo) AS costo_minimo
        FROM tratamientos t
        LEFT JOIN citas c ON t.id_tratamiento = c.id_tratamiento 
            AND c.fecha_hora BETWEEN :fecha_inicio AND :fecha_fin
        LEFT JOIN pagos p ON c.id_cita = p.id_cita AND p.estado = 'Completado'
    ";

    if (!empty($especialidad_filtro)) {
        $sql_stats .= " WHERE t.id_especialidad = :especialidad";
    }

    $stmt_stats = $db->prepare($sql_stats);
    $stmt_stats->execute($params);
    $estadisticas = $stmt_stats->fetch(PDO::FETCH_ASSOC);

    // 5. Datos para gráficos
    $sql_grafico = "
        SELECT t.nombre, COUNT(DISTINCT c.id_cita) as total 
        FROM tratamientos t
        LEFT JOIN citas c ON t.id_tratamiento = c.id_tratamiento 
            AND c.fecha_hora BETWEEN :fecha_inicio AND :fecha_fin
    ";

    if (!empty($especialidad_filtro)) {
        $sql_grafico .= " WHERE t.id_especialidad = :especialidad";
    }

    $sql_grafico .= " GROUP BY t.id_tratamiento HAVING total > 0 LIMIT 10";

    $stmt_grafico = $db->prepare($sql_grafico);
    $stmt_grafico->execute($params);
    $popularidad = $stmt_grafico->fetchAll(PDO::FETCH_KEY_PAIR);

} catch (PDOException $e) {
    die("Error al generar el reporte: " . $e->getMessage());
}

    // 6. Obtener los pagos para exportar al PDF
   



// 6. Exportar a PDF
// 5. Exportar a PDF con DomPDF
if (isset($_GET['exportar_pdf'])) {
    // Crear instancia de Dompdf
    $dompdf = new Dompdf();
    
    // Generar HTML para el PDF
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Reporte de Tratamientos</title>
        <style>
            body { font-family: Arial, sans-serif; }
            h1 { color: #2c3e50; text-align: center; }
            .header { margin-bottom: 20px; }
            .periodo { text-align: center; margin-bottom: 30px; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #3498db; color: white; }
            tr:nth-child(even) { background-color: #f2f2f2; }
            .stat-container { display: flex; justify-content: space-between; margin-bottom: 30px; }
            .stat-card { width: 23%; padding: 15px; background-color: #3f51b5; color: white; 
                         border-radius: 5px; text-align: center; }
            .stat-card h3 { margin: 5px 0; font-size: 24px; }
            .footer { margin-top: 30px; text-align: right; font-size: 12px; color: #7f8c8d; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>Reporte de Tratamientos</h1>
            <div class="periodo">
                <p>Del ' . date('d/m/Y', strtotime($fecha_inicio)) . ' al ' . date('d/m/Y', strtotime($fecha_fin)) . '</p>
            </div>
        </div>
        
        <div class="stat-container">
            <div class="stat-card">
                <h5>Total Tratamientos</h5>
                <h3>' . $estadisticas['total_tratamientos'] . '</h3>
            </div>
            <div class="stat-card" style="background-color: #4caf50;">
                <h5>Citas Totales</h5>
                <h3>' . ($estadisticas['total_citas_general'] ?? 0) . '</h3>
            </div>
            <div class="stat-card" style="background-color: #00bcd4;">
                <h5>Costo Promedio</h5>
                <h3>S/ ' . number_format($estadisticas['costo_promedio'] ?? 0, 2) . '</h3>
            </div>
            <div class="stat-card" style="background-color: #ff9800;">
                <h5>Ingresos Totales</h5>
                <h3>S/ ' . number_format($estadisticas['ingresos_totales'] ?? 0, 2) . '</h3>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Tratamiento</th>
                    <th>Especialidad</th>
                    <th>Citas</th>
                    <th>Ingresos</th>
                    <th>Costo</th>
                    <th>Duración</th>
                    <th>Ingreso Promedio</th>
                </tr>
            </thead>
            <tbody>';
    
    foreach ($tratamientos as $t) {
        $html .= '
                <tr>
                    <td>' . htmlspecialchars($t['nombre']) . '</td>
                    <td>' . htmlspecialchars($t['especialidad'] ?? 'General') . '</td>
                    <td>' . $t['total_citas'] . '</td>
                    <td>S/ ' . number_format($t['ingresos'] ?? 0, 2) . '</td>
                    <td>S/ ' . number_format($t['costo'], 2) . '</td>
                    <td>' . $t['duracion_estimada'] . ' min</td>
                    <td>S/ ' . number_format($t['ingreso_promedio'] ?? 0, 2) . '</td>
                </tr>';
    }
    
    $html .= '
            </tbody>
        </table>
        
        <div class="footer">
            <p>Generado el ' . date('d/m/Y H:i:s') . '</p>
        </div>
    </body>
    </html>';
    
    // Cargar HTML en Dompdf
    $dompdf->loadHtml($html);
    
    // Configurar tamaño de papel
    $dompdf->setPaper('A4', 'portrait');
    
    // Renderizar PDF
    $dompdf->render();
    
    // Mostrar el PDF en el navegador
    $dompdf->stream("reporte_tratamientos_" . date('Ymd') . ".pdf", ["Attachment" => false]);
    exit;
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Tratamientos</title>
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
        .stat-card {
            color: white;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }
        .bg-custom-primary { background-color: #3f51b5; }
        .bg-custom-success { background-color: #4caf50; }
        .bg-custom-info { background-color: #00bcd4; }
        .bg-custom-warning { background-color: #ff9800; }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Filtros del Reporte</h4>
            </div>
            <div class="card-body">
                <form method="get" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Especialidad</label>
                        <select name="especialidad" class="form-select">
                            <option value="">Todas las especialidades</option>
                            <?php foreach($especialidades as $esp): ?>
                            <option value="<?= $esp['id_especialidad'] ?>" <?= $esp['id_especialidad'] == $especialidad_filtro ? 'selected' : '' ?>>
                                <?= htmlspecialchars($esp['nombre']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" class="form-control" 
                              value="<?= $fecha_inicio ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha Fin</label>
                        <input type="date" name="fecha_fin" class="form-control" 
                              value="<?= $fecha_fin ?>">
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <a href="reporte_tratamientos.php" class="btn btn-secondary">
                            <i class="fas fa-sync-alt"></i> Limpiar
                        </a>
                        <a href="reporte_tratamientos.php?exportar_pdf=1&fecha_inicio=<?= $fecha_inicio ?>&fecha_fin=<?= $fecha_fin ?>&especialidad=<?= $especialidad_filtro ?>" 
                           class="btn btn-danger">
                            <i class="fas fa-file-pdf"></i> PDF
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card bg-custom-primary">
                    <h5>Total Tratamientos</h5>
                    <h2><?= $estadisticas['total_tratamientos'] ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card bg-custom-success">
                    <h5>Citas Totales</h5>
                    <h2><?= $estadisticas['total_citas_general'] ?? 0 ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card bg-custom-info">
                    <h5>Costo Promedio</h5>
                    <h2>S/ <?= number_format($estadisticas['costo_promedio'] ?? 0, 2) ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card bg-custom-warning">
                    <h5>Ingresos Totales</h5>
                    <h2>S/ <?= number_format($estadisticas['ingresos_totales'] ?? 0, 2) ?></h2>
                </div>
            </div>
        </div>

        <!-- Gráficos y Tabla -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Tratamientos Más Solicitados</h4>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="tratamientosChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Ingresos por Tratamiento</h4>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="ingresosChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de tratamientos -->
        <div class="card mt-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Detalle de Tratamientos</h4>
                <span class="badge bg-light text-dark">
                    Mostrando <?= count($tratamientos) ?> registros
                </span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="tablaTratamientos">
                        <thead class="table-dark">
                            <tr>
                                <th>Tratamiento</th>
                                <th>Especialidad</th>
                                <th>Citas</th>
                                <th>Ingresos</th>
                                <th>Costo</th>
                                <th>Duración</th>
                                <th>Ingreso Promedio</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tratamientos as $t): ?>
                            <tr>
                                <td><?= htmlspecialchars($t['nombre']) ?></td>
                                <td><?= htmlspecialchars($t['especialidad'] ?? 'General') ?></td>
                                <td><?= $t['total_citas'] ?></td>
                                <td>S/ <?= number_format($t['ingresos'] ?? 0, 2) ?></td>
                                <td>S/ <?= number_format($t['costo'], 2) ?></td>
                                <td><?= $t['duracion_estimada'] ?> min</td>
                                <td>S/ <?= number_format($t['ingreso_promedio'] ?? 0, 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
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
        // DataTable para tratamientos
        $(document).ready(function() {
            $('#tablaTratamientos').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
                },
                dom: '<"top"f>rt<"bottom"lip><"clear">',
                pageLength: 10,
                order: [[2, 'desc']]
            });
        });
        
        // Gráfico de tratamientos populares
        const ctxTratamientos = document.getElementById('tratamientosChart').getContext('2d');
        const tratamientosChart = new Chart(ctxTratamientos, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_keys($popularidad)) ?>,
                datasets: [{
                    data: <?= json_encode(array_values($popularidad)) ?>,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                        '#FF9F40', '#8AC24A', '#607D8B', '#E91E63', '#9C27B0'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} citas (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        // Gráfico de ingresos por tratamiento
        const ctxIngresos = document.getElementById('ingresosChart').getContext('2d');
        const ingresosChart = new Chart(ctxIngresos, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column(array_slice($tratamientos, 0, 10), 'nombre')) ?>,
                datasets: [{
                    label: 'Ingresos (S/)',
                    data: <?= json_encode(array_column(array_slice($tratamientos, 0, 10), 'ingresos')) ?>,
                    backgroundColor: '#4BC0C0',
                    borderColor: '#36A2EB',
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
    </script>
</body>
</html>