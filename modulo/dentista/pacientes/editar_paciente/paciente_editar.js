document.addEventListener('DOMContentLoaded', function() {
    const params = new URLSearchParams(window.location.search);
    const idPaciente = params.get('id_paciente');

    // Validar ID primero
    if (!idPaciente || isNaN(idPaciente)) {
        mostrarError('ID de paciente inválido', true);
        return;
    }

    cargarDatosPaciente(idPaciente);
});

function cargarDatosPaciente(id) {
    const loading = document.getElementById('loading');
    const formContainer = document.getElementById('form-container');
    
    fetch(`paciente_editar.php?id_paciente=${id}`)
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

function construirFormulario(paciente) {
    const formContainer = document.getElementById('form-container');
    const loading = document.getElementById('loading');
    
    const formHTML = `
        <form id="form-editar-paciente" novalidate>
            <input type="hidden" name="id_paciente" value="${paciente.id_paciente}">
            <input type="hidden" name="id_usuario" value="${paciente.id_usuario}">
            
            <div class="mb-3">
                <label class="form-label">Nombre Completo</label>
                <input type="text" class="form-control" 
                       value="${paciente.nombre_apellido}" readonly>
            </div>

            <div class="mb-3">
    <label class="form-label">Medicamentos Actuales</label>
    <textarea name="medicamentos" class="form-control" 
              placeholder="Listado de medicamentos...">${paciente.medicamentos || ''}</textarea>
</div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Fecha Nacimiento</label>
                    <input type="date" name="fecha_nacimiento" class="form-control" 
                           value="${paciente.fecha_nacimiento || ''}" required>
                    <div class="invalid-feedback">Fecha obligatoria</div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Género</label>
                    <select name="genero" class="form-select" required>
                        <option value="">Seleccionar...</option>
                        <option value="Masculino" ${paciente.genero === 'Masculino' ? 'selected' : ''}>Masculino</option>
                        <option value="Femenino" ${paciente.genero === 'Femenino' ? 'selected' : ''}>Femenino</option>
                        <option value="Otro" ${paciente.genero === 'Otro' ? 'selected' : ''}>Otro</option>
                    </select>
                    <div class="invalid-feedback">Seleccione un género</div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Alergias</label>
                <textarea name="alergias" class="form-control">${paciente.alergias || ''}</textarea>
            </div>
            

            <div class="mb-3">
                <label class="form-label">Enfermedades Crónicas</label>
                <textarea name="enfermedades_cronicas" class="form-control">${paciente.enfermedades_cronicas || ''}</textarea>
            </div>
                 

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Seguro Médico</label>
                    <input type="text" name="seguro_medico" class="form-control" 
                           value="${paciente.seguro_medico || ''}">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Número de Seguro</label>
                    <input type="text" name="numero_seguro" class="form-control" 
                           value="${paciente.numero_seguro || ''}">
                </div>
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

    // Agregar evento de submit
    document.getElementById('form-editar-paciente').addEventListener('submit', guardarCambios);
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
        id_paciente: parseInt(formData.get('id_paciente'), 10),
        id_usuario: parseInt(formData.get('id_usuario'), 10),
        fecha_nacimiento: formData.get('fecha_nacimiento'),
        genero: formData.get('genero'),
        alergias: formData.get('alergias'),
        enfermedades_cronicas: formData.get('enfermedades_cronicas'),
        medicamentos: formData.get('medicamentos'),
        seguro_medico: formData.get('seguro_medico'),
        numero_seguro: formData.get('numero_seguro')
    };

    fetch('actualizar_paciente.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    })
    .then(async response => {
        const text = await response.text();
        try {
            const result = JSON.parse(text);
            if (!response.ok) throw new Error(result.error || 'Error desconocido');
            
            Swal.fire({
                icon: 'success',
                title: '¡Guardado!',
                text: 'Cambios actualizados correctamente',
                timer: 2000
            }).then(() => window.location.href = '../pacientes.html');
            
        } catch (error) {
            throw new Error(text || 'Error en la respuesta del servidor');
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
        ${esFatal ? '<a href="../pacientes.html" class="btn btn-secondary mt-3">Volver al listado</a>' : ''}
    `;
    loading.classList.remove('d-none');
}