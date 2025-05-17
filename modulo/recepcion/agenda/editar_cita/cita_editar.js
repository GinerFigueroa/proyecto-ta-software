document.addEventListener('DOMContentLoaded', function() {
    const params = new URLSearchParams(window.location.search);
    const idCita = params.get('id_cita');

    if (!idCita || isNaN(idCita)) {
        mostrarError('ID de cita inválido', true);
        return;
    }

    cargarDatosCita(idCita);
});

function cargarDatosCita(id) {
    const loading = document.getElementById('loading');
    const formContainer = document.getElementById('form-container');
    
    fetch(`cita_editar.php?id_cita=${id}`)
    .then(response => {
        if (!response.ok) throw new Error(`Error HTTP: ${response.status}`);
        return response.json();
    })
    .then(data => {
        if (data.error) throw new Error(data.error);
        construirFormulario(data);
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarError(`Error al cargar datos: ${error.message}`, true);
    });
}

function construirFormulario(cita) {
    const formContainer = document.getElementById('form-container');
    const loading = document.getElementById('loading');
    
    const formHTML = `
        <form id="form-editar-cita" novalidate>
            <input type="hidden" name="id_cita" value="${cita.id_cita}">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Paciente</label>
                    <input type="text" class="form-control" 
                           value="${cita.paciente || 'No disponible'}" readonly>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tratamiento</label>
                    <input type="text" class="form-control" 
                           value="${cita.tratamiento || 'No disponible'}" readonly>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Dentista</label>
                    <select name="id_dentista" class="form-select">
                        <option value="${cita.id_dentista || ''}">${cita.dentista || 'Seleccionar dentista'}</option>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Fecha y Hora</label>
                    <input type="datetime-local" name="fecha_hora" class="form-control" 
                           value="${moment(cita.fecha_hora).format('YYYY-MM-DDTHH:mm')}" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Duración (minutos)</label>
                    <input type="number" name="duracion" class="form-control" 
                           value="${cita.duracion || 30}" min="15" max="240" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select" required>
                        <option value="Pendiente" ${cita.estado === 'Pendiente' ? 'selected' : ''}>Pendiente</option>
                        <option value="Confirmada" ${cita.estado === 'Confirmada' ? 'selected' : ''}>Confirmada</option>
                        <option value="Completada" ${cita.estado === 'Completada' ? 'selected' : ''}>Completada</option>
                        <option value="Cancelada" ${cita.estado === 'Cancelada' ? 'selected' : ''}>Cancelada</option>
                        <option value="No asistió" ${cita.estado === 'No asistió' ? 'selected' : ''}>No asistió</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Notas</label>
                <textarea name="notas" class="form-control">${cita.notas || ''}</textarea>
            </div>

            <button type="submit" class="btn btn-primary" id="btn-guardar">
                <span class="spinner-border spinner-border-sm d-none" id="submit-spinner"></span>
                Guardar Cambios
            </button>
        </form>
    `;

    formContainer.innerHTML = formHTML;
    loading.classList.add('d-none');
    formContainer.classList.remove('d-none');

    document.getElementById('form-editar-cita').addEventListener('submit', guardarCambios);
}

function guardarCambios(e) {
    e.preventDefault();
    const form = e.target;
    const btnSubmit = document.getElementById('btn-guardar');
    const spinner = document.getElementById('submit-spinner');

    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }

    btnSubmit.disabled = true;
    spinner.classList.remove('d-none');

    const formData = new FormData(form);
    const data = {
        id_cita: formData.get('id_cita'),
        id_dentista: formData.get('id_dentista'),
        fecha_hora: formData.get('fecha_hora'),
        duracion: formData.get('duracion'),
        estado: formData.get('estado'),
        notas: formData.get('notas')
    };

    fetch('actualizar_cita.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Guardado!',
                text: 'Cambios actualizados correctamente',
                timer: 2000
            }).then(() => window.location.href = '../citas.html');
        } else {
            throw new Error(result.error || 'Error desconocido');
        }
    })
    .catch(error => {
        Swal.fire('Error', error.message, 'error');
    })
    .finally(() => {
        btnSubmit.disabled = false;
        spinner.classList.add('d-none');
    });
}

function mostrarError(mensaje, esFatal = false) {
    const loading = document.getElementById('loading');
    loading.innerHTML = `
        <div class="alert alert-danger d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <div>${mensaje}</div>
        </div>
        ${esFatal ? '<a href="../citas.html" class="btn btn-secondary mt-3">Volver al listado</a>' : ''}
    `;
    loading.classList.remove('d-none');
}