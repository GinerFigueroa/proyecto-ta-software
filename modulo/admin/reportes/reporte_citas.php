<?php
require_once __DIR__ . '/../../../php/database/conexion.php';
use Dompdf\Dompdf;
// 1. Establecer fechas por defecto (últimos 30 días)
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-d', strtotime('-30 days'));
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-d');

// 2. Verificar si se solicita exportar a PDF
$es_pdf = isset($_GET['exportar_pdf']);

// 3. Consulta principal de citas
try {
    $db = (new Database())->getConnection();
    
    // Consulta para obtener citas con información relacionada
      $stmt = $db->prepare("
        SELECT 
            c.*, 
            p.nombre_apellido AS paciente, 
            d.nombre_apellido AS dentista, 
            t.nombre AS tratamiento,
            e.nombre AS especialidad,
            TIMESTAMPDIFF(YEAR, pa.fecha_nacimiento, CURDATE()) AS edad_paciente,
            pa.genero AS genero_paciente,
            pg.monto AS monto_pago,
            pg.estado AS estado_pago  -- Corrección 1
        FROM citas c
        LEFT JOIN pacientes pa ON c.id_paciente = pa.id_paciente
        LEFT JOIN usuarios p ON pa.id_usuario = p.id_usuario
        LEFT JOIN dentistas de ON c.id_dentista = de.id_dentista
        LEFT JOIN usuarios d ON de.id_usuario = d.id_usuario
        LEFT JOIN tratamientos t ON c.id_tratamiento = t.id_tratamiento
        LEFT JOIN especialidades e ON t.id_especialidad = e.id_especialidad
        LEFT JOIN pagos pg ON c.id_cita = pg.id_cita
        WHERE c.fecha_hora BETWEEN :fecha_inicio AND :fecha_fin
        ORDER BY c.fecha_hora DESC
    ");
    
    $stmt->execute([
        ':fecha_inicio' => $fecha_inicio,
        ':fecha_fin' => $fecha_fin
    ]);
    $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Estadísticas generales
 $stmt_stats = $db->prepare("
     SELECT 
        c.*, 
        p.nombre_apellido AS paciente, 
        d.nombre_apellido AS dentista, 
        t.nombre AS tratamiento,
        e.nombre AS especialidad,
        TIMESTAMPDIFF(YEAR, pa.fecha_nacimiento, CURDATE()) AS edad_paciente,
        pa.genero AS genero_paciente,
        pg.monto AS monto_pago,
        pg.estado AS estado_pago,
        TIMESTAMPDIFF(MINUTE, c.fecha_hora, c.fecha_hora + INTERVAL c.duracion MINUTE) AS duracion_cita  -- Calcula duración
    FROM citas c
    LEFT JOIN pacientes pa ON c.id_paciente = pa.id_paciente
    LEFT JOIN usuarios p ON pa.id_usuario = p.id_usuario
    LEFT JOIN dentistas de ON c.id_dentista = de.id_dentista
    LEFT JOIN usuarios d ON de.id_usuario = d.id_usuario
    LEFT JOIN tratamientos t ON c.id_tratamiento = t.id_tratamiento
    LEFT JOIN especialidades e ON t.id_especialidad = e.id_especialidad
    LEFT JOIN pagos pg ON c.id_cita = pg.id_cita
    WHERE c.fecha_hora BETWEEN :fecha_inicio AND :fecha_fin
    ORDER BY c.fecha_hora DESC
");
    
    $stmt_stats->execute([
        ':fecha_inicio' => $fecha_inicio,
        ':fecha_fin' => $fecha_fin
    ]);
    $estadisticas = $stmt_stats->fetch(PDO::FETCH_ASSOC);
    
    // Datos para gráficos (solo en vista web)
    if (!$es_pdf) {
        // Gráfico por estado de citas
        $stmt_estados = $db->prepare("
            SELECT 
                estado, 
                COUNT(*) as total
            FROM citas
            WHERE fecha_hora BETWEEN :fecha_inicio AND :fecha_fin
            GROUP BY estado
        ");
        $stmt_estados->execute([
            ':fecha_inicio' => $fecha_inicio,
            ':fecha_fin' => $fecha_fin
        ]);
        $estados_citas = $stmt_estados->fetchAll(PDO::FETCH_ASSOC);
        
        // Gráfico mensual de citas
        $stmt_mensual = $db->prepare("
            SELECT 
                DATE_FORMAT(fecha_hora, '%Y-%m') as mes,
                COUNT(*) as total
            FROM citas
            WHERE fecha_hora BETWEEN :fecha_inicio AND :fecha_fin
            GROUP BY DATE_FORMAT(fecha_hora, '%Y-%m')
            ORDER BY mes
        ");
        $stmt_mensual->execute([
            ':fecha_inicio' => $fecha_inicio,
            ':fecha_fin' => $fecha_fin
        ]);
        $citas_mensual = $stmt_mensual->fetchAll(PDO::FETCH_ASSOC);
        
        // Gráfico por especialidad
        $stmt_especialidades = $db->prepare("
            SELECT 
                e.nombre as especialidad, 
                COUNT(*) as total
            FROM citas c
            JOIN tratamientos t ON c.id_tratamiento = t.id_tratamiento
            JOIN especialidades e ON t.id_especialidad = e.id_especialidad
            WHERE c.fecha_hora BETWEEN :fecha_inicio AND :fecha_fin
            GROUP BY e.nombre
            ORDER BY total DESC
            LIMIT 5
        ");
        $stmt_especialidades->execute([
            ':fecha_inicio' => $fecha_inicio,
            ':fecha_fin' => $fecha_fin
        ]);
        $citas_especialidad = $stmt_especialidades->fetchAll(PDO::FETCH_ASSOC);
    }
    
} catch (PDOException $e) {
    die("Error al generar el reporte: " . $e->getMessage());
}

if ($es_pdf) {
    require_once __DIR__ . '/../../../dompdf/autoload.inc.php';
    

    $dompdf = new Dompdf();

    // Construir el HTML
    $html = '
    <h1 style="text-align:center;">Reporte de Citas</h1>
    <p style="text-align:center;">Del ' . date('d/m/Y', strtotime($fecha_inicio)) . ' al ' . date('d/m/Y', strtotime($fecha_fin)) . '</p>
    
    <table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse; font-size: 12px;">
        <thead>
            <tr>
                <th width="15%">Fecha/Hora</th>
                <th width="20%">Paciente</th>
                <th width="15%">Dentista</th>
                <th width="20%">Tratamiento</th>
                <th width="10%">Duración</th>
                <th width="10%">Estado</th>
                <th width="10%">Pago</th>
            </tr>
        </thead>
        <tbody>';
    
    foreach ($citas as $cita) {
        $html .= '
            <tr>
                <td>' . date('d/m/Y H:i', strtotime($cita['fecha_hora'])) . '</td>
                <td>' . htmlspecialchars($cita['paciente']) . '<br>
                    ' . $cita['edad_paciente'] . ' años | ' . htmlspecialchars($cita['genero_paciente']) . '
                </td>
                <td>' . htmlspecialchars($cita['dentista'] ?? 'Sin asignar') . '</td>
                <td>' . htmlspecialchars($cita['tratamiento']) . '<br>
                    ' . htmlspecialchars($cita['especialidad'] ?? 'General') . '
                </td>
                <td>' . $cita['duracion'] . ' min</td>
                <td>' . htmlspecialchars($cita['estado']) . '</td>
                <td>' . ($cita['estado_pago'] == 'Completado' ? 'S/ ' . number_format($cita['monto_pago'], 2) : 'Pendiente') . '</td>
            </tr>';
    }

    $html .= '</tbody></table>';

    // Cargar HTML y configurar PDF
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Mostrar PDF en el navegador
    $dompdf->stream("reporte_citas_" . date('Ymd') . ".pdf", ["Attachment" => false]);
    exit;
}


// 5. Vista HTML normal
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Citas</title>
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
        .bg-custom-danger { background-color: #f44336; }
        .bg-custom-info { background-color: #00bcd4; }
        .bg-custom-warning { background-color: #ff9800; }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <!-- Encabezado y filtros -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Reporte de Citas</h4>
                <div>
                    <a href="reporte_citas.php?exportar_pdf=1&fecha_inicio=<?= $fecha_inicio ?>&fecha_fin=<?= $fecha_fin ?>" 
                       class="btn btn-danger btn-sm">
                        <i class="fas fa-file-pdf"></i> Exportar PDF
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form method="get" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Fecha Inicio</label>
                        <input type="date" class="form-control" name="fecha_inicio" 
                               value="<?= $fecha_inicio ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha Fin</label>
                        <input type="date" class="form-control" name="fecha_fin" 
                               value="<?= $fecha_fin ?>" required>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <a href="reporte_citas.php" class="btn btn-secondary ms-2">
                            <i class="fas fa-sync-alt"></i> Resetear
                        </a>
                    </div>
                </form>
                
                <!-- Resumen Estadístico -->
                <div class="row text-center mb-4">
                    <div class="col-md-2">
                        <div class="stat-card bg-custom-primary">
                            <h5>Total Citas</h5>
                            <h3><?= $estadisticas['total_citas'] ?? 0 ?></h3>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stat-card bg-custom-success">
                            <h5>Confirmadas</h5>
                            <h3><?= $estadisticas['confirmadas'] ?? 0 ?></h3>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stat-card bg-custom-danger">
                            <h5>Canceladas</h5>
                            <h3><?= $estadisticas['canceladas'] ?? 0 ?></h3>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stat-card bg-custom-info">
                            <h5>Completadas</h5>
                            <h3><?= $estadisticas['completadas'] ?? 0 ?></h3>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stat-card bg-custom-warning">
                            <h5>No asistió</h5>
                            <h3><?= $estadisticas['no_asistio'] ?? 0 ?></h3>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stat-card" style="background-color: #9c27b0;">
                            <h5>Ingresos</h5>
                            <h3>S/ <?= number_format($estadisticas['ingresos_totales'] ?? 0, 2) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Distribución por Estado</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="estadosChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Citas Mensuales</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="mensualChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Por Especialidad</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="especialidadChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de citas -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Detalle de Citas</h4>
                <span class="badge bg-light text-dark">
                    Mostrando <?= count($citas) ?> registros
                </span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="tablaCitas">
                        <thead class="table-dark">
                            <tr>
                                <th>Fecha/Hora</th>
                                <th>Paciente</th>
                                <th>Dentista</th>
                                <th>Tratamiento</th>
                                <th>Duración</th>
                                <th>Estado</th>
                                <th>Pago</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($citas as $cita): ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($cita['fecha_hora'])) ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($cita['paciente']) ?></strong>
                                    <div class="text-muted small">
                                        <?= $cita['edad_paciente'] ?> años | 
                                        <?= htmlspecialchars($cita['genero_paciente']) ?>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($cita['dentista'] ?? 'Sin asignar') ?></td>
                                <td>
                                    <?= htmlspecialchars($cita['tratamiento']) ?>
                                    <div class="text-muted small">
                                        <?= htmlspecialchars($cita['especialidad'] ?? 'General') ?>
                                    </div>
                                </td>
                                <td><?= $cita['duracion'] ?> min</td>
                                <td>
                                    <span class="badge bg-<?= 
                                        $cita['estado'] == 'Confirmada' ? 'success' : 
                                        ($cita['estado'] == 'Cancelada' ? 'danger' : 
                                        ($cita['estado'] == 'Completada' ? 'primary' : 
                                        ($cita['estado'] == 'No asistió' ? 'warning' : 'info'))) ?>">
                                        <?= htmlspecialchars($cita['estado']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if($cita['estado_pago'] == 'Completado'): ?>
                                        <span class="badge bg-success">S/ <?= number_format($cita['monto_pago'], 2) ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Pendiente</span>
                                    <?php endif; ?>
                                </td>
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
        // DataTable para citas
        $(document).ready(function() {
            $('#tablaCitas').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
                },
                dom: '<"top"f>rt<"bottom"lip><"clear">',
                pageLength: 10,
                order: [[0, 'desc']]
            });
        });
        
        // Gráfico de estados de citas
        const ctxEstados = document.getElementById('estadosChart').getContext('2d');
        const estadosChart = new Chart(ctxEstados, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_column($estados_citas, 'estado')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($estados_citas, 'total')) ?>,
                    backgroundColor: [
                        '#4caf50', '#f44336', '#2196F3', '#ff9800', '#00bcd4'
                    ],
                    borderWidth: 1
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
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
        
        // Gráfico mensual de citas
        const ctxMensual = document.getElementById('mensualChart').getContext('2d');
        const mensualChart = new Chart(ctxMensual, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_map(function($item) { 
                    return date('M Y', strtotime($item['mes'].'-01')); 
                }, $citas_mensual)) ?>,
                datasets: [{
                    label: 'Citas Programadas',
                    data: <?= json_encode(array_column($citas_mensual, 'total')) ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        // Gráfico por especialidad
        const ctxEspecialidad = document.getElementById('especialidadChart').getContext('2d');
        const especialidadChart = new Chart(ctxEspecialidad, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($citas_especialidad, 'especialidad')) ?>,
                datasets: [{
                    label: 'Citas por Especialidad',
                    data: <?= json_encode(array_column($citas_especialidad, 'total')) ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>