<?php
// citas.php - Sistema de gestión de citas para pacientes

// Iniciar sesión y verificar autenticación
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

// Usar rutas absolutas o una constante base para los requires
require_once __DIR__ . '/../../php/database/conexion.php';

// Obtener información del paciente
$id_usuario = $_SESSION['id_usuario'];
$paciente = $db->query("SELECT * FROM pacientes WHERE id_usuario = $id_usuario")->fetch_assoc();
$id_paciente = $paciente['id_paciente'] ?? null;

// Procesar formulario de cita
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agendar_cita'])) {
    $id_tratamiento = $db->real_escape_string($_POST['tratamiento']);
    $fecha = $db->real_escape_string($_POST['fecha']);
    $hora = $db->real_escape_string($_POST['hora']);
    $notas = $db->real_escape_string($_POST['notas'] ?? '');
    
    $fecha_hora = "$fecha $hora";
    
    // Insertar la cita
    $sql = "INSERT INTO citas (id_paciente, id_tratamiento, fecha_hora, duracion, estado, notas, creado_por) 
            VALUES ($id_paciente, $id_tratamiento, '$fecha_hora', 30, 'Pendiente', '$notas', $id_usuario)";
    
    if ($db->query($sql)) {
        $mensaje = "¡Cita agendada correctamente!";
    } else {
        $error = "Error al agendar la cita: " . $db->error;
    }
}

// Obtener tratamientos disponibles
$tratamientos = $db->query("SELECT * FROM tratamientos WHERE activo = 1");

// Obtener citas del paciente
$citas = $db->query("SELECT c.*, t.nombre as tratamiento 
                     FROM citas c 
                     JOIN tratamientos t ON c.id_tratamiento = t.id_tratamiento 
                     WHERE c.id_paciente = $id_paciente 
                     ORDER BY c.fecha_hora DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Citas Odontológicas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .calendar-day {
            cursor: pointer;
            transition: all 0.2s;
        }
        .calendar-day:hover {
            background-color: #f0f0f0;
        }
        .available-hour {
            cursor: pointer;
        }
        .available-hour:hover {
            background-color: #e9f7ef;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="row mb-4">
            <div class="col">
                <h1 class="text-center">Sistema de Citas Odontológicas</h1>
                <p class="text-center">Bienvenido, <?= htmlspecialchars($_SESSION['nombre_apellido'] ?? 'Usuario') ?></p>
            </div>
        </div>

        <?php if (isset($mensaje)): ?>
            <div class="alert alert-success"><?= $mensaje ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Agendar Nueva Cita</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="form-cita">
                            <div class="mb-3">
                                <label for="tratamiento" class="form-label">Tratamiento</label>
                                <select class="form-select" id="tratamiento" name="tratamiento" required>
                                    <option value="">Seleccione un tratamiento</option>
                                    <?php while ($tratamiento = $tratamientos->fetch_assoc()): ?>
                                        <option value="<?= $tratamiento['id_tratamiento'] ?>" 
                                                data-duracion="<?= $tratamiento['duracion_estimada'] ?>">
                                            <?= htmlspecialchars($tratamiento['nombre']) ?> - 
                                            $<?= number_format($tratamiento['costo'], 2) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="fecha" class="form-label">Fecha</label>
                                <input type="date" class="form-control" id="fecha" name="fecha" min="<?= date('Y-m-d') ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="hora" class="form-label">Hora</label>
                                <select class="form-select" id="hora" name="hora" required>
                                    <option value="">Seleccione una hora</option>
                                    <!-- Las opciones se llenarán con JavaScript -->
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="notas" class="form-label">Notas adicionales</label>
                                <textarea class="form-control" id="notas" name="notas" rows="3"></textarea>
                            </div>
                            
                            <button type="submit" name="agendar_cita" class="btn btn-primary">Agendar Cita</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Mis Citas Programadas</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($citas->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Fecha y Hora</th>
                                            <th>Tratamiento</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($cita = $citas->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= date('d/m/Y H:i', strtotime($cita['fecha_hora'])) ?></td>
                                                <td><?= htmlspecialchars($cita['tratamiento']) ?></td>
                                                <td>
                                                    <span class="badge 
                                                        <?= $cita['estado'] == 'Confirmada' ? 'bg-success' : 
                                                           ($cita['estado'] == 'Cancelada' ? 'bg-danger' : 'bg-warning') ?>">
                                                        <?= $cita['estado'] ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($cita['estado'] == 'Pendiente' || $cita['estado'] == 'Confirmada'): ?>
                                                        <button class="btn btn-sm btn-outline-danger cancelar-cita" 
                                                                data-id="<?= $cita['id_cita'] ?>">
                                                            Cancelar
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">No tienes citas programadas.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para cancelar cita -->
    <div class="modal fade" id="cancelarModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cancelar Cita</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas cancelar esta cita?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                    <button type="button" class="btn btn-danger" id="confirmar-cancelar">Sí, cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Generar horas disponibles
            const horaSelect = document.getElementById('hora');
            const fechaInput = document.getElementById('fecha');
            
            function generarHorasDisponibles() {
                horaSelect.innerHTML = '<option value="">Seleccione una hora</option>';
                
                if (!fechaInput.value) return;
                
                // Horario de trabajo (9am a 6pm)
                const horaInicio = 9;
                const horaFin = 18;
                
                // Agregar horas cada 30 minutos
                for (let h = horaInicio; h < horaFin; h++) {
                    for (let m = 0; m < 60; m += 30) {
                        const hora = `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}`;
                        const option = document.createElement('option');
                        option.value = hora;
                        option.textContent = hora;
                        horaSelect.appendChild(option);
                    }
                }
            }
            
            fechaInput.addEventListener('change', generarHorasDisponibles);
            
            // Manejar cancelación de citas
            let citaACancelar = null;
            const cancelarBtns = document.querySelectorAll('.cancelar-cita');
            const cancelarModal = new bootstrap.Modal(document.getElementById('cancelarModal'));
            
            cancelarBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    citaACancelar = this.dataset.id;
                    cancelarModal.show();
                });
            });
            
            document.getElementById('confirmar-cancelar').addEventListener('click', function() {
                if (citaACancelar) {
                    fetch('cancelar_cita.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `id_cita=${citaACancelar}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error al cancelar la cita: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Ocurrió un error al cancelar la cita');
                    });
                }
                cancelarModal.hide();
            });
            
            // Validar fecha futura
            document.getElementById('form-cita').addEventListener('submit', function(e) {
                const fechaSeleccionada = new Date(fechaInput.value);
                const hoy = new Date();
                hoy.setHours(0, 0, 0, 0);
                
                if (fechaSeleccionada < hoy) {
                    e.preventDefault();
                    alert('Por favor seleccione una fecha futura');
                }
            });
        });
    </script>
</body>
</html>
<?php
$db->close();
?>