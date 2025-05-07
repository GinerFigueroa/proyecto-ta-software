document.addEventListener('DOMContentLoaded', function() {
    const citasContainer = document.getElementById('citasContainer');
    const filtroBusqueda = document.getElementById('filtroBusqueda');
    const filtroEstado = document.getElementById('filtroEstado');
    const detalleCitaModal = new bootstrap.Modal(document.getElementById('detalleCitaModal'));
    
    let todasLasCitas = [];

    // Cargar citas al iniciar
    cargarCitas();

    // Configurar eventos de filtrado
    filtroBusqueda.addEventListener('input', filtrarCitas);
    filtroEstado.addEventListener('change', filtrarCitas);

    async function cargarCitas() {
        try {
            const response = await fetch('MostrarCitas.php');
            
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.message || 'Error al obtener citas');
            }
            
            mostrarCitas(data.data);
            
        } catch (error) {
            console.error('Error:', error);
            mostrarError(error.message);
        }
    }

    function mostrarCitas(citas) {
        if (citas.length === 0) {
            citasContainer.innerHTML = `
                <div class="empty-state">
                    <i class="far fa-calendar-times fa-3x mb-3"></i>
                    <h4>No tienes citas programadas</h4>
                    <p>Puedes agendar una nueva cita haciendo clic en el botón "Nueva Cita"</p>
                </div>
            `;
            return;
        }

        let html = '';
        citas.forEach(cita => {
            const fechaHora = new Date(cita.fecha_hora);
            const fecha = fechaHora.toLocaleDateString('es-ES', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            const hora = fechaHora.toLocaleTimeString('es-ES', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });

            html += `
                <div class="col-md-6 col-lg-4">
                    <div class="card card-cita h-100" data-id="${cita.id_cita}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge rounded-pill badge-estado estado-${cita.estado.toLowerCase().replace(' ', '-')}">
                                    ${cita.estado}
                                </span>
                                <small class="text-muted">#${cita.id_cita}</small>
                            </div>
                            <h5 class="card-title">${cita.tratamiento}</h5>
                            <p class="card-text">
                                <i class="far fa-calendar me-2"></i>${fecha}<br>
                                <i class="far fa-clock me-2"></i>${hora} (${cita.duracion} mins)
                            </p>
                            ${cita.dentista ? `<p class="card-text"><i class="fas fa-user-md me-2"></i>Dr. ${cita.dentista}</p>` : ''}
                            <button class="btn btn-outline-primary btn-sm ver-detalle" data-id="${cita.id_cita}">
                                <i class="fas fa-info-circle me-1"></i> Ver detalles
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });

        citasContainer.innerHTML = html;
        
        // Configurar eventos para los botones de detalle
        document.querySelectorAll('.ver-detalle').forEach(btn => {
            btn.addEventListener('click', function() {
                const idCita = this.getAttribute('data-id');
                mostrarDetalleCita(idCita);
            });
        });
    }

    function filtrarCitas() {
        const texto = filtroBusqueda.value.toLowerCase();
        const estado = filtroEstado.value;
        
        const citasFiltradas = todasLasCitas.filter(cita => {
            const coincideTexto = 
                cita.tratamiento.toLowerCase().includes(texto) || 
                (cita.dentista && cita.dentista.toLowerCase().includes(texto));
            
            const coincideEstado = estado === '' || cita.estado === estado;
            
            return coincideTexto && coincideEstado;
        });
        
        mostrarCitas(citasFiltradas);
    }

    function mostrarDetalleCita(idCita) {
        const cita = todasLasCitas.find(c => c.id_cita == idCita);
        if (!cita) return;

        const fechaHora = new Date(cita.fecha_hora);
        const fecha = fechaHora.toLocaleDateString('es-ES', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        const hora = fechaHora.toLocaleTimeString('es-ES', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });

        let contenido = `
            <div class="mb-3">
                <h6>Tratamiento</h6>
                <p>${cita.tratamiento}</p>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <h6>Fecha</h6>
                    <p>${fecha}</p>
                </div>
                <div class="col-md-6">
                    <h6>Hora</h6>
                    <p>${hora} (${cita.duracion} minutos)</p>
                </div>
            </div>
            <div class="mb-3">
                <h6>Estado</h6>
                <span class="badge rounded-pill badge-estado estado-${cita.estado.toLowerCase().replace(' ', '-')}">
                    ${cita.estado}
                </span>
            </div>
        `;

        if (cita.dentista) {
            contenido += `
                <div class="mb-3">
                    <h6>Dentista</h6>
                    <p>Dr. ${cita.dentista}</p>
                </div>
            `;
        }

        if (cita.notas) {
            contenido += `
                <div class="mb-3">
                    <h6>Notas adicionales</h6>
                    <p>${cita.notas}</p>
                </div>
            `;
        }

        document.getElementById('detalleCitaContent').innerHTML = contenido;
        detalleCitaModal.show();
    }
});