<?php
// reporte_pacientes.php
require_once __DIR__ . '/../../../php/database/conexion.php';
use Dompdf\Dompdf;
// 1. Establecer fechas por defecto (últimos 30 días)
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-d', strtotime('-30 days'));
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-d');

// 2. Verificar si se solicita exportar a PDF
$es_pdf = isset($_GET['exportar_pdf']);

// 3. Consulta principal de pacientes
try {
  $db = (new Database())->getConnection(); // Crea nueva instancia
    // Consulta para obtener pacientes y estadísticas
    $stmt = $db->prepare("
        SELECT 
            p.*, 
            u.nombre_apellido, 
            u.email, 
            u.telefono,
            TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) AS edad,
            COUNT(c.id_cita) AS total_citas,
            MAX(c.fecha_hora) AS ultima_cita,
            SUM(CASE WHEN c.estado = 'Completada' THEN 1 ELSE 0 END) AS citas_completadas,
            SUM(CASE WHEN c.estado = 'Cancelada' THEN 1 ELSE 0 END) AS citas_canceladas,
            SUM(CASE WHEN c.estado = 'No asistió' THEN 1 ELSE 0 END) AS citas_no_asistidas
        FROM pacientes p
        JOIN usuarios u ON p.id_usuario = u.id_usuario
        LEFT JOIN citas c ON p.id_paciente = c.id_paciente
        WHERE DATE(p.creado_en) BETWEEN :fecha_inicio AND :fecha_fin
        GROUP BY p.id_paciente
        ORDER BY p.creado_en DESC
    ");
    
    $stmt->execute([
        ':fecha_inicio' => $fecha_inicio,
        ':fecha_fin' => $fecha_fin
    ]);
    $pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Estadísticas generales
    $stmt_stats = $db->prepare("
        SELECT 
            COUNT(*) AS total_pacientes,
            AVG(TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE())) AS edad_promedio,
            SUM(CASE WHEN genero = 'Masculino' THEN 1 ELSE 0 END) AS masculinos,
            SUM(CASE WHEN genero = 'Femenino' THEN 1 ELSE 0 END) AS femeninos,
            SUM(CASE WHEN genero NOT IN ('Masculino', 'Femenino') THEN 1 ELSE 0 END) AS otros_generos
        FROM pacientes
        WHERE DATE(creado_en) BETWEEN :fecha_inicio AND :fecha_fin
    ");
    
    $stmt_stats->execute([
        ':fecha_inicio' => $fecha_inicio,
        ':fecha_fin' => $fecha_fin
    ]);
    $estadisticas = $stmt_stats->fetch(PDO::FETCH_ASSOC);
    
    // Datos para gráficos (solo en vista web)
    if (!$es_pdf) {
        $stmt_generos = $db->prepare("
            SELECT 
                genero, 
                COUNT(*) as total
            FROM pacientes 
            WHERE DATE(creado_en) BETWEEN :fecha_inicio AND :fecha_fin
            GROUP BY genero
        ");
        $stmt_generos->execute([
            ':fecha_inicio' => $fecha_inicio,
            ':fecha_fin' => $fecha_fin
        ]);
        $generos = $stmt_generos->fetchAll(PDO::FETCH_ASSOC);
        
        $stmt_mensual = $db->prepare("
            SELECT 
                DATE_FORMAT(creado_en, '%Y-%m') as mes,
                COUNT(*) as total
            FROM pacientes
            WHERE DATE(creado_en) BETWEEN :fecha_inicio AND :fecha_fin
            GROUP BY DATE_FORMAT(creado_en, '%Y-%m')
            ORDER BY mes
        ");
        $stmt_mensual->execute([
            ':fecha_inicio' => $fecha_inicio,
            ':fecha_fin' => $fecha_fin
        ]);
        $pacientes_mensual = $stmt_mensual->fetchAll(PDO::FETCH_ASSOC);
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
    <h1 style="text-align:center;">Reporte de Pacientes</h1>
    <p style="text-align:center;">Del ' . date('d/m/Y', strtotime($fecha_inicio)) . ' al ' . date('d/m/Y', strtotime($fecha_fin)) . '</p>
    
    <table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse; font-size: 12px;">
        <thead>
            <tr>
                <th width="15%">ID</th>
                <th width="25%">Paciente</th>
                <th width="20%">Contacto</th>
                <th width="10%">Género</th>
                <th width="10%">Edad</th>
                <th width="20%">Citas</th>
            </tr>
        </thead>
        <tbody>';

    foreach ($pacientes as $paciente) {
        $html .= '
            <tr>
                <td>' . $paciente['id_paciente'] . '</td>
                <td><strong>' . htmlspecialchars($paciente['nombre_apellido']) . '</strong><br>
                    ' . ($paciente['fecha_nacimiento'] ? date('d/m/Y', strtotime($paciente['fecha_nacimiento'])) : '') . '
                </td>
                <td>' . htmlspecialchars($paciente['email']) . '<br>' . htmlspecialchars($paciente['telefono']) . '</td>
                <td>' . htmlspecialchars($paciente['genero']) . '</td>
                <td>' . ($paciente['edad'] ?? 'N/A') . '</td>
                <td>
                    Total: ' . $paciente['total_citas'] . '<br>
                    Completadas: ' . $paciente['citas_completadas'] . '<br>
                    Canceladas: ' . $paciente['citas_canceladas'] . '
                </td>
            </tr>';
    }

    $html .= '</tbody></table>';

    // Cargar y renderizar PDF
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Descargar el PDF automáticamente
    $dompdf->stream("reporte_pacientes_" . date('Ymd') . ".pdf", ["Attachment" => true]);
    exit;
}


// 5. Vista HTML normal
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Pacientes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <style>
        .card {
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .progress {
            height: 5px;
            margin-top: 5px;
        }
        .chart-container {
            position: relative;
            height: 300px;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Reporte de Pacientes</h4>
                        <div>
                            <a href="reporte_pacientes.php?exportar_pdf=1&fecha_inicio=<?= $fecha_inicio ?>&fecha_fin=<?= $fecha_fin ?>" 
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
                                <a href="reporte_pacientes.php" class="btn btn-secondary ms-2">
                                    <i class="fas fa-sync-alt"></i> Resetear
                                </a>
                            </div>
                        </form>
                        
                        <!-- Resumen Estadístico -->
                        <div class="row text-center mb-4">
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5 class="text-muted">Total Pacientes</h5>
                                        <h3><?= $estadisticas['total_pacientes'] ?? 0 ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5 class="text-primary">Edad Promedio</h5>
                                        <h3><?= round($estadisticas['edad_promedio'] ?? 0) ?> años</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5 class="text-info">Masculinos</h5>
                                        <h3><?= $estadisticas['masculinos'] ?? 0 ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5 class="text-danger">Femeninos</h5>
                                        <h3><?= $estadisticas['femeninos'] ?? 0 ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tabla de pacientes -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="tablaPacientes">
                                <thead class="table-primary">
                                    <tr>
                                        <th>ID</th>
                                        <th>Paciente</th>
                                        <th>Contacto</th>
                                        <th>Género</th>
                                        <th>Edad</th>
                                        <th>Citas</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pacientes as $paciente): ?>
                                    <tr>
                                        <td><?= $paciente['id_paciente'] ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($paciente['nombre_apellido']) ?></strong>
                                            <?php if($paciente['fecha_nacimiento']): ?>
                                            <div class="text-muted small">
                                                <?= date('d/m/Y', strtotime($paciente['fecha_nacimiento'])) ?>
                                            </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div><?= htmlspecialchars($paciente['email']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($paciente['telefono']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($paciente['genero']) ?></td>
                                        <td><?= $paciente['edad'] ?? 'N/A' ?></td>
                                        <td>
                                            <span class="badge bg-primary"><?= $paciente['total_citas'] ?></span>
                                            <?php if ($paciente['ultima_cita']): ?>
                                                <div><small><?= date('d/m/Y', strtotime($paciente['ultima_cita'])) ?></small></div>
                                            <?php endif; ?>
                                            <?php if($paciente['total_citas'] > 0): ?>
                                            <div class="progress mt-1">
                                                <div class="progress-bar bg-success" style="width: <?= ($paciente['citas_completadas']/$paciente['total_citas'])*100 ?>%"></div>
                                                <div class="progress-bar bg-danger" style="width: <?= ($paciente['citas_canceladas']/$paciente['total_citas'])*100 ?>%"></div>
                                                <div class="progress-bar bg-warning" style="width: <?= ($paciente['citas_no_asistidas']/$paciente['total_citas'])*100 ?>%"></div>
                                            </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="verDetallePaciente(<?= $paciente['id_paciente'] ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary" 
                                                    onclick="editarPaciente(<?= $paciente['id_paciente'] ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" 
                                                    onclick="verHistorial(<?= $paciente['id_paciente'] ?>)">
                                                <i class="fas fa-history"></i>
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
                        <h5 class="mb-0">Distribución por Género</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="generoPacientesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Registro Mensual</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="pacientesMensualChart"></canvas>
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
        // DataTable para pacientes
        $(document).ready(function() {
            $('#tablaPacientes').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
                },
                dom: '<"top"f>rt<"bottom"lip><"clear">',
                pageLength: 10,
                order: [[1, 'asc']]
            });
        });
        
        // Gráfico de género de pacientes
        const ctxGeneros = document.getElementById('generoPacientesChart').getContext('2d');
        const generoPacientesChart = new Chart(ctxGeneros, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_column($generos, 'genero')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($generos, 'total')) ?>,
                    backgroundColor: [
                        '#36A2EB', '#FF6384', '#FFCE56', '#4BC0C0', '#9966FF'
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
        
        // Gráfico de pacientes por mes
        const ctxMensual = document.getElementById('pacientesMensualChart').getContext('2d');
        const pacientesMensualChart = new Chart(ctxMensual, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_map(function($item) { 
                    return date('M Y', strtotime($item['mes'].'-01')); 
                }, $pacientes_mensual)) ?>,
                datasets: [{
                    label: 'Pacientes Registrados',
                    data: <?= json_encode(array_column($pacientes_mensual, 'total')) ?>,
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
        
        // Funciones de acción
        function verDetallePaciente(idPaciente) {
            // Implementar lógica para ver detalles
            alert('Mostrando detalles del paciente ID: ' + idPaciente);
        }
        
        function editarPaciente(idPaciente) {
            // Implementar lógica para editar paciente
            alert('Editando paciente ID: ' + idPaciente);
        }
        
        function verHistorial(idPaciente) {
            // Implementar lógica para ver historial
            alert('Mostrando historial del paciente ID: ' + idPaciente);
        }
    </script>
</body>
</html>
