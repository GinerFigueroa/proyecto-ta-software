// Agregar estas nuevas funciones al archivo scripts.js
function mostrarModalPago(idCita) {
    // Crear modal dinámico
    const modalHTML = `
        <div class="modal fade" id="pagoModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Registrar Pago</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="form-pago">
                            <input type="hidden" id="id_cita" value="${idCita}">
                            
                            <div class="mb-3">
                                <label for="monto" class="form-label">Monto</label>
                                <input type="number" class="form-control" id="monto" step="0.01" min="0" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="metodo_pago" class="form-label">Método de Pago</label>
                                <select class="form-select" id="metodo_pago" required>
                                    <option value="">Seleccione método</option>
                                    <option value="Efectivo">Efectivo</option>
                                    <option value="Tarjeta crédito">Tarjeta de crédito</option>
                                    <option value="Tarjeta débito">Tarjeta de débito</option>
                                    <option value="Transferencia">Transferencia</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="referencia" class="form-label">Referencia/Número</label>
                                <input type="text" class="form-control" id="referencia">
                            </div>
                            
                            <div class="mb-3">
                                <label for="notas_pago" class="form-label">Notas</label>
                                <textarea class="form-control" id="notas_pago" rows="3"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="registrar-pago">Registrar Pago</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    const pagoModal = new bootstrap.Modal(document.getElementById('pagoModal'));
    
    // Evento para registrar pago
    document.getElementById('registrar-pago').addEventListener('click', function() {
        const datos = {
            id_cita: document.getElementById('id_cita').value,
            monto: document.getElementById('monto').value,
            metodo_pago: document.getElementById('metodo_pago').value,
            referencia: document.getElementById('referencia').value,
            notas: document.getElementById('notas_pago').value
        };
        
        if (!datos.monto || !datos.metodo_pago) {
            mostrarMensaje('Monto y método de pago son requeridos', 'danger');
            return;
        }
        
        fetch('api.php?action=registrarPago', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(datos)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarMensaje(data.message, 'success');
                pagoModal.hide();
                cargarCitas();
                // Eliminar el modal del DOM después de cerrarlo
                setTimeout(() => {
                    document.getElementById('pagoModal').remove();
                }, 500);
            } else {
                mostrarMensaje(data.message, 'danger');
            }
        })
        .catch(error => {
            mostrarMensaje('Error al registrar el pago', 'danger');
            console.error('Error:', error);
        });
    });
    
    pagoModal.show();
    
    // Eliminar el modal cuando se cierre
    document.getElementById('pagoModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function verPagosCita(idCita) {
    fetch(`api.php?action=getPagosCita&id_cita=${idCita}`)
        .then(response => response.json())
        .then(data => {
            if (data.length === 0) {
                mostrarMensaje('No hay pagos registrados para esta cita', 'info');
                return;
            }
            
            let html = `
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Monto</th>
                                <th>Método</th>
                                <th>Referencia</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            data.forEach(pago => {
                const fecha = new Date(pago.fecha_pago);
                const fechaFormateada = fecha.toLocaleDateString('es-ES', {
                    day: '2-digit', month: '2-digit', year: 'numeric',
                    hour: '2-digit', minute: '2-digit'
                });
                
                let claseEstado = '';
                if (pago.estado === 'Completado') claseEstado = 'bg-success';
                else if (pago.estado === 'Cancelado') claseEstado = 'bg-danger';
                else if (pago.estado === 'Reembolsado') claseEstado = 'bg-info';
                else claseEstado = 'bg-warning';
                
                html += `
                    <tr>
                        <td>${fechaFormateada}</td>
                        <td>$${pago.monto.toFixed(2)}</td>
                        <td>${escapeHtml(pago.metodo_pago)}</td>
                        <td>${escapeHtml(pago.referencia || 'N/A')}</td>
                        <td><span class="badge ${claseEstado}">${pago.estado}</span></td>
                    </tr>
                `;
            });
            
            html += `
                        </tbody>
                    </table>
                </div>
            `;
            
            // Mostrar en un modal
            const modalHTML = `
                <div class="modal fade" id="verPagosModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Historial de Pagos</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                ${html}
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', modalHTML);
            const verPagosModal = new bootstrap.Modal(document.getElementById('verPagosModal'));
            verPagosModal.show();
            
            // Eliminar el modal cuando se cierre
            document.getElementById('verPagosModal').addEventListener('hidden.bs.modal', function() {
                this.remove();
            });
        })
        .catch(error => {
            mostrarMensaje('Error al cargar los pagos', 'danger');
            console.error('Error:', error);
        });
}

// Modificar la función cargarCitas para incluir botones de pago
function cargarCitas() {
    fetch('api.php?action=getCitas')
        .then(response => response.json())
        .then(data => {
            const contenedor = document.getElementById('lista-citas');
            
            if (data.length === 0) {
                contenedor.innerHTML = '<div class="alert alert-info">No tienes citas programadas.</div>';
                return;
            }
            
            let html = `
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
            `;
            
            data.forEach(cita => {
                const fecha = new Date(cita.fecha_hora);
                const fechaFormateada = fecha.toLocaleDateString('es-ES', {
                    day: '2-digit', month: '2-digit', year: 'numeric'
                });
                const horaFormateada = fecha.toLocaleTimeString('es-ES', {
                    hour: '2-digit', minute: '2-digit'
                });
                
                let claseEstado = '';
                if (cita.estado === 'Confirmada') claseEstado = 'bg-success';
                else if (cita.estado === 'Cancelada') claseEstado = 'bg-danger';
                else if (cita.estado === 'Completada') claseEstado = 'bg-secondary';
                else claseEstado = 'bg-warning';
                
                html += `
                    <tr>
                        <td>${fechaFormateada} ${horaFormateada}</td>
                        <td>${escapeHtml(cita.tratamiento)}</td>
                        <td>
                            <span class="badge ${claseEstado}">${cita.estado}</span>
                        </td>
                        <td>
                `;
                
                if (cita.estado === 'Pendiente' || cita.estado === 'Confirmada') {
                    html += `
                        <button class="btn btn-sm btn-outline-danger cancelar-cita me-1" 
                                data-id="${cita.id_cita}">
                            Cancelar
                        </button>
                    `;
                }
                
                // Botón para ver pagos
                html += `
                    <button class="btn btn-sm btn-outline-info ver-pagos me-1" 
                            data-id="${cita.id_cita}">
                        Ver Pagos
                    </button>
                `;
                
                // Botón para registrar pago (solo si la cita está completada o confirmada)
                if (cita.estado === 'Confirmada' || cita.estado === 'Completada') {
                    html += `
                        <button class="btn btn-sm btn-success registrar-pago" 
                                data-id="${cita.id_cita}">
                            Pagar
                        </button>
                    `;
                }
                
                html += `
                        </td>
                    </tr>
                `;
            });
            
            html += `
                        </tbody>
                    </table>
                </div>
            `;
            
            contenedor.innerHTML = html;
            
            // Agregar eventos a los botones
            document.querySelectorAll('.cancelar-cita').forEach(btn => {
                btn.addEventListener('click', function() {
                    citaACancelar = this.dataset.id;
                    cancelarModal.show();
                });
            });
            
            document.querySelectorAll('.ver-pagos').forEach(btn => {
                btn.addEventListener('click', function() {
                    verPagosCita(this.dataset.id);
                });
            });
            
            document.querySelectorAll('.registrar-pago').forEach(btn => {
                btn.addEventListener('click', function() {
                    mostrarModalPago(this.dataset.id);
                });
            });
        })
        .catch(error => {
            document.getElementById('lista-citas').innerHTML = `
                <div class="alert alert-danger">
                    Error al cargar las citas. Por favor intenta nuevamente.
                </div>
            `;
            console.error('Error:', error);
        });
}