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
    <title>Mis Pagos - Clínica Dental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .pago-card {
            border-left: 4px solid;
            transition: all 0.3s ease;
        }
        .pago-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .estado-pendiente {
            border-left-color: #ffc107;
        }
        .estado-completado {
            border-left-color: #28a745;
        }
        .estado-reembolsado {
            border-left-color: #17a2b8;
        }
        .estado-cancelado {
            border-left-color: #dc3545;
        }
        .badge-metodo {
            font-size: 0.8rem;
        }
        .metodo-efectivo {
            background-color: #28a745;
        }
        .metodo-tarjeta-credito {
            background-color: #007bff;
        }
        .metodo-tarjeta-debito {
            background-color: #6f42c1;
        }
        .metodo-transferencia {
            background-color: #fd7e14;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h1><i class="fas fa-receipt me-2"></i>Mis Pagos</h1>
            <div>
                <span class="badge bg-primary me-2">Total: <span id="totalPagos">Cargando...</span></span>
                <span class="badge bg-success">Pagado: <span id="totalPagado">Cargando...</span></span>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" id="filtroBusqueda" class="form-control" placeholder="Buscar por referencia...">
                </div>
            </div>
            <div class="col-md-4">
                <select id="filtroEstado" class="form-select">
                    <option value="">Todos los estados</option>
                    <option value="Pendiente">Pendiente</option>
                    <option value="Completado">Completado</option>
                    <option value="Reembolsado">Reembolsado</option>
                    <option value="Cancelado">Cancelado</option>
                </select>
            </div>
            <div class="col-md-4">
                <select id="filtroMetodo" class="form-select">
                    <option value="">Todos los métodos</option>
                    <option value="Efectivo">Efectivo</option>
                    <option value="Tarjeta crédito">Tarjeta crédito</option>
                    <option value="Tarjeta débito">Tarjeta débito</option>
                    <option value="Transferencia">Transferencia</option>
                </select>
            </div>
        </div>

        <div id="pagosContainer">
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p>Cargando tus pagos...</p>
            </div>
        </div>

        <!-- Modal para detalles -->
        <div class="modal fade" id="detallePagoModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detalles del Pago</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="detallePagoContent">
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
            let todosLosPagos = [];
            
            // Cargar pagos al iniciar
            cargarPagos();

            // Configurar eventos de filtrado
            document.getElementById('filtroBusqueda').addEventListener('input', filtrarPagos);
            document.getElementById('filtroEstado').addEventListener('change', filtrarPagos);
            document.getElementById('filtroMetodo').addEventListener('change', filtrarPagos);

            async function cargarPagos() {
                try {
                    const response = await fetch('obtenerPagos.php');
                    if (!response.ok) throw new Error('Error al cargar pagos');
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        todosLosPagos = data.data;
                        actualizarTotales(todosLosPagos);
                        mostrarPagos(todosLosPagos);
                    } else {
                        document.getElementById('pagosContainer').innerHTML = `
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                No se encontraron registros de pagos.
                            </div>
                        `;
                    }
                } catch (error) {
                    console.error('Error:', error);
                    document.getElementById('pagosContainer').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Error al cargar los pagos. Por favor intenta nuevamente.
                        </div>
                    `;
                }
            }

            function actualizarTotales(pagos) {
                const total = pagos.reduce((sum, pago) => sum + parseFloat(pago.monto), 0);
                const pagado = pagos
                    .filter(pago => pago.estado === 'Completado')
                    .reduce((sum, pago) => sum + parseFloat(pago.monto), 0);
                
                document.getElementById('totalPagos').textContent = `$${total.toFixed(2)}`;
                document.getElementById('totalPagado').textContent = `$${pagado.toFixed(2)}`;
            }

            function mostrarPagos(pagos) {
                if (pagos.length === 0) {
                    document.getElementById('pagosContainer').innerHTML = `
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            No se encontraron pagos que coincidan con los filtros.
                        </div>
                    `;
                    return;
                }

                let html = '';
                
                pagos.forEach(pago => {
                    const fechaPago = pago.fecha_pago 
                        ? new Date(pago.fecha_pago).toLocaleDateString('es-ES') 
                        : 'No aplica';
                    
                    const metodoClase = pago.metodo_pago 
                        ? `metodo-${pago.metodo_pago.toLowerCase().replace(' ', '-')}`
                        : 'bg-secondary';
                    
                    html += `
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card pago-card estado-${pago.estado.toLowerCase()}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="badge bg-${pago.estado === 'Completado' ? 'success' : 
                                                               pago.estado === 'Pendiente' ? 'warning' :
                                                               pago.estado === 'Reembolsado' ? 'info' : 'danger'}">
                                            ${pago.estado}
                                        </span>
                                        <small class="text-muted">#${pago.id_pago}</small>
                                    </div>
                                    
                                    <h5 class="card-title">$${parseFloat(pago.monto).toFixed(2)}</h5>
                                    
                                    ${pago.metodo_pago ? `
                                        <span class="badge badge-metodo ${metodoClase} mb-2">
                                            <i class="fas fa-${pago.metodo_pago === 'Efectivo' ? 'money-bill-wave' :
                                                              pago.metodo_pago.includes('Tarjeta') ? 'credit-card' :
                                                              'exchange-alt'} me-1"></i>
                                            ${pago.metodo_pago}
                                        </span>
                                    ` : ''}
                                    
                                    <p class="card-text">
                                        <i class="far fa-calendar me-1"></i> ${fechaPago}
                                    </p>
                                    
                                    ${pago.referencia ? `
                                        <p class="card-text small text-muted">
                                            <i class="fas fa-hashtag me-1"></i> Ref: ${pago.referencia}
                                        </p>
                                    ` : ''}
                                    
                                    <button class="btn btn-outline-primary btn-sm ver-detalle" 
                                            data-id="${pago.id_pago}">
                                        <i class="fas fa-info-circle me-1"></i> Ver detalles
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                document.getElementById('pagosContainer').innerHTML = `
                    <div class="row">
                        ${html}
                    </div>
                `;
                
                // Configurar eventos para los botones de detalle
                document.querySelectorAll('.ver-detalle').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const idPago = this.getAttribute('data-id');
                        mostrarDetallePago(idPago);
                    });
                });
            }

            function filtrarPagos() {
                const texto = document.getElementById('filtroBusqueda').value.toLowerCase();
                const estado = document.getElementById('filtroEstado').value;
                const metodo = document.getElementById('filtroMetodo').value;
                
                const pagosFiltrados = todosLosPagos.filter(pago => {
                    const coincideTexto = pago.referencia && pago.referencia.toLowerCase().includes(texto);
                    const coincideEstado = estado === '' || pago.estado === estado;
                    const coincideMetodo = metodo === '' || pago.metodo_pago === metodo;
                    
                    return coincideTexto && coincideEstado && coincideMetodo;
                });
                
                mostrarPagos(pagosFiltrados);
            }

            async function mostrarDetallePago(idPago) {
                try {
                    const response = await fetch(`obtenerDetallePago.php?id=${idPago}`);
                    if (!response.ok) throw new Error('Error al cargar detalles');
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        const pago = data.data;
                        const fechaPago = pago.fecha_pago 
                            ? new Date(pago.fecha_pago).toLocaleDateString('es-ES', { 
                                weekday: 'long', 
                                year: 'numeric', 
                                month: 'long', 
                                day: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                              }) 
                            : 'No aplica';
                        
                        let contenido = `
                            <div class="mb-3">
                                <h6>Monto</h6>
                                <p>$${parseFloat(pago.monto).toFixed(2)}</p>
                            </div>
                            
                            <div class="mb-3">
                                <h6>Estado</h6>
                                <span class="badge bg-${pago.estado === 'Completado' ? 'success' : 
                                                   pago.estado === 'Pendiente' ? 'warning' :
                                                   pago.estado === 'Reembolsado' ? 'info' : 'danger'}">
                                    ${pago.estado}
                                </span>
                            </div>
                            
                            <div class="mb-3">
                                <h6>Fecha de Pago</h6>
                                <p>${fechaPago}</p>
                            </div>
                        `;
                        
                        if (pago.metodo_pago) {
                            contenido += `
                                <div class="mb-3">
                                    <h6>Método de Pago</h6>
                                    <p>
                                        <span class="badge ${pago.metodo_pago === 'Efectivo' ? 'bg-success' :
                                                          pago.metodo_pago === 'Tarjeta crédito' ? 'bg-primary' :
                                                          pago.metodo_pago === 'Tarjeta débito' ? 'bg-purple' : 'bg-orange'}">
                                            ${pago.metodo_pago}
                                        </span>
                                    </p>
                                </div>
                            `;
                        }
                        
                        if (pago.referencia) {
                            contenido += `
                                <div class="mb-3">
                                    <h6>Referencia</h6>
                                    <p>${pago.referencia}</p>
                                </div>
                            `;
                        }
                        
                        if (pago.notas) {
                            contenido += `
                                <div class="mb-3">
                                    <h6>Notas</h6>
                                    <p>${pago.notas}</p>
                                </div>
                            `;
                        }
                        
                        if (pago.cita_info) {
                            contenido += `
                                <div class="mb-3">
                                    <h6>Información de la Cita</h6>
                                    <p>${pago.cita_info}</p>
                                </div>
                            `;
                        }
                        
                        document.getElementById('detallePagoContent').innerHTML = contenido;
                        const modal = new bootstrap.Modal(document.getElementById('detallePagoModal'));
                        modal.show();
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error al cargar los detalles del pago');
                }
            }
        });
    </script>
</body>
</html>