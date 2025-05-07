<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Citas - Clínica Dental</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .card-cita {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 20px;
        }
        .card-cita:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .badge-estado {
            font-size: 0.9rem;
            padding: 0.5em 0.75em;
        }
        .estado-pendiente {
            background-color: #ffc107;
            color: #000;
        }
        .estado-confirmada {
            background-color: #28a745;
            color: #fff;
        }
        .estado-completada {
            background-color: #17a2b8;
            color: #fff;
        }
        .estado-cancelada {
            background-color: #dc3545;
            color: #fff;
        }
        .estado-no-asistio {
            background-color: #6c757d;
            color: #fff;
        }
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            background-color: #f8f9fa;
            border-radius: 10px;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container py-5">
       

        <!-- Filtros -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" id="filtroBusqueda" class="form-control" placeholder="Buscar por tratamiento o dentista...">
                </div>
            </div>
            <div class="col-md-6">
                <select id="filtroEstado" class="form-select">
                    <option value="">Todos los estados</option>
                    <option value="Pendiente">Pendiente</option>
                    <option value="Confirmada">Confirmada</option>
                    <option value="Completada">Completada</option>
                    <option value="Cancelada">Cancelada</option>
                    <option value="No asistió">No asistió</option>
                </select>
            </div>
        </div>

        <!-- Contenedor de citas -->
        <div id="citasContainer" class="row">
            <!-- Las citas se cargarán aquí via JavaScript -->
            <div class="empty-state">
                <i class="fas fa-spinner fa-spin fa-3x mb-3"></i>
                <p>Cargando tus citas...</p>
            </div>
        </div>

        <!-- Modal para detalles -->
        <div class="modal fade" id="detalleCitaModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detalles de la Cita</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="detalleCitaContent">
                        <!-- Contenido se llenará con JavaScript -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Nuestro JavaScript -->
    <script src="misCitas.js"></script>
</body>
</html>