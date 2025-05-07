<?php
require_once '../../../php/database/conexion.php';
session_start();

// Verificar autenticación y rol (4 = Paciente)
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 4) {
    header("Location: ../../../bienvenido/login.php");
    exit();
}

$db = new Database();
$conn = $db->getConnection();
$id_paciente = $_SESSION['id_usuario'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Historial Médico - Clínica Dental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .historial-item {
            border-left: 4px solid #0d6efd;
            transition: all 0.3s ease;
        }
        .historial-item:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
        }
        .badge-procedimiento {
            background-color: #6f42c1;
        }
        .badge-diagnostico {
            background-color: #d63384;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h1><i class="fas fa-file-medical-alt me-2"></i>Mi Historial Médico</h1>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" id="filtroBusqueda" class="form-control" placeholder="Buscar por diagnóstico o procedimiento...">
                </div>
            </div>
            <div class="col-md-6">
                <select id="filtroFecha" class="form-select">
                    <option value="">Todas las fechas</option>
                    <option value="ultimo_mes">Último mes</option>
                    <option value="ultimos_6_meses">Últimos 6 meses</option>
                    <option value="ultimo_ano">Último año</option>
                </select>
            </div>
        </div>

        <div id="historialContainer">
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p>Cargando tu historial médico...</p>
            </div>
        </div>

        <!-- Modal para detalles completos -->
        <div class="modal fade" id="detalleHistorialModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detalles del Registro Médico</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="detalleHistorialContent">
                        <!-- Contenido dinámico -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            cargarHistorial();

            async function cargarHistorial() {
                try {
                    const response = await fetch('obtenerHistorial.php');
                    if (!response.ok) throw new Error('Error al cargar historial');
                    
                    const data = await response.json();
                    
                    if (data.success && data.data.length > 0) {
                        mostrarHistorial(data.data);
                    } else {
                        document.getElementById('historialContainer').innerHTML = `
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                No se encontraron registros en tu historial médico.
                            </div>
                        `;
                    }
                } catch (error) {
                    console.error('Error:', error);
                    document.getElementById('historialContainer').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Error al cargar el historial. Por favor intenta nuevamente.
                        </div>
                    `;
                }
            }

            function mostrarHistorial(registros) {
                let html = '';
                
                registros.forEach(registro => {
                    const fecha = new Date(registro.fecha_procedimiento);
                    const fechaFormateada = fecha.toLocaleDateString('es-ES', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                    
                    html += `
                        <div class="card mb-3 historial-item">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="card-title">${registro.procedimiento || 'Procedimiento no especificado'}</h5>
                                        <h6 class="card-subtitle mb-2 text-muted">
                                            <i class="far fa-calendar me-1"></i>${fechaFormateada}
                                            ${registro.dentista ? `<span class="ms-3"><i class="fas fa-user-md me-1"></i>Dr. ${registro.dentista}</span>` : ''}
                                        </h6>
                                    </div>
                                    <span class="badge bg-primary">${registro.tratamiento || 'General'}</span>
                                </div>
                                
                                ${registro.diagnostico ? `
                                    <div class="mt-2">
                                        <span class="badge badge-diagnostico bg-danger me-1">Diagnóstico</span>
                                        <p class="d-inline">${registro.diagnostico}</p>
                                    </div>
                                ` : ''}
                                
                                <button class="btn btn-outline-primary btn-sm mt-2 ver-detalle" 
                                        data-id="${registro.id_historial}">
                                    <i class="fas fa-info-circle me-1"></i> Ver detalles completos
                                </button>
                            </div>
                        </div>
                    `;
                });
                
                document.getElementById('historialContainer').innerHTML = html;
                
                // Configurar eventos para los botones de detalle
                document.querySelectorAll('.ver-detalle').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const idHistorial = this.getAttribute('data-id');
                        mostrarDetalleCompleto(idHistorial);
                    });
                });
            }

            async function mostrarDetalleCompleto(idHistorial) {
                try {
                    const response = await fetch(`obtenerDetalleHistorial.php?id=${idHistorial}`);
                    if (!response.ok) throw new Error('Error al cargar detalles');
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        const registro = data.data;
                        const fecha = new Date(registro.fecha_procedimiento);
                        const fechaFormateada = fecha.toLocaleDateString('es-ES', {
                            weekday: 'long',
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        });
                        
                        let adjuntosHtml = '';
                        if (registro.adjuntos) {
                            const adjuntos = JSON.parse(registro.adjuntos);
                            adjuntosHtml = `
                                <div class="mb-3">
                                    <h6>Archivos Adjuntos</h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        ${adjuntos.map(adjunto => `
                                            <a href="${adjunto.ruta}" class="btn btn-outline-secondary btn-sm" target="_blank">
                                                <i class="fas fa-paperclip me-1"></i> ${adjunto.nombre}
                                            </a>
                                        `).join('')}
                                    </div>
                                </div>
                            `;
                        }
                        
                        let contenido = `
                            <div class="mb-3">
                                <h6>Fecha del Procedimiento</h6>
                                <p>${fechaFormateada}</p>
                            </div>
                            
                            ${registro.dentista ? `
                                <div class="mb-3">
                                    <h6>Atendido por</h6>
                                    <p>Dr. ${registro.dentista}</p>
                                </div>
                            ` : ''}
                            
                            ${registro.tratamiento ? `
                                <div class="mb-3">
                                    <h6>Tratamiento</h6>
                                    <p>${registro.tratamiento}</p>
                                </div>
                            ` : ''}
                            
                            ${registro.diagnostico ? `
                                <div class="mb-3">
                                    <h6>Diagnóstico</h6>
                                    <p>${registro.diagnostico}</p>
                                </div>
                            ` : ''}
                            
                            ${registro.procedimiento ? `
                                <div class="mb-3">
                                    <h6>Procedimiento Realizado</h6>
                                    <p>${registro.procedimiento}</p>
                                </div>
                            ` : ''}
                            
                            ${registro.observaciones ? `
                                <div class="mb-3">
                                    <h6>Observaciones</h6>
                                    <p>${registro.observaciones}</p>
                                </div>
                            ` : ''}
                            
                            ${registro.receta ? `
                                <div class="mb-3">
                                    <h6>Receta Médica</h6>
                                    <p>${registro.receta}</p>
                                </div>
                            ` : ''}
                            
                            ${registro.proxima_visita ? `
                                <div class="mb-3">
                                    <h6>Próxima Visita</h6>
                                    <p>${new Date(registro.proxima_visita).toLocaleDateString('es-ES')}</p>
                                </div>
                            ` : ''}
                            
                            ${adjuntosHtml}
                        `;
                        
                        document.getElementById('detalleHistorialContent').innerHTML = contenido;
                        const modal = new bootstrap.Modal(document.getElementById('detalleHistorialModal'));
                        modal.show();
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error al cargar los detalles del registro');
                }
            }
        });
    </script>
</body>
</html>