document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const idHistorial = urlParams.get('id');
    
    if (!idHistorial) {
        mostrarError('ID de historial no proporcionado');
        return;
    }
    
    cargarHistorial(idHistorial);
    cargarTratamientos();
});

async function cargarHistorial(id) {
    try {
        const response = await fetch(`actualizar_historial.php?id=${id}`);
        if (!response.ok) throw new Error('Error al cargar historial');
        
        const historial = await response.json();
        
        if (historial.error) {
            mostrarError(historial.error);
            return;
        }

        // Llenar campos del formulario
        document.getElementById('id_historial').value = historial.id_historial;
        document.getElementById('paciente').value = historial.paciente;
        document.getElementById('fecha_procedimiento').value = formatDateTime(historial.fecha_procedimiento);
        document.getElementById('diagnostico').value = historial.diagnostico;
        document.getElementById('procedimiento').value = historial.procedimiento;
        document.getElementById('observaciones').value = historial.observaciones;
        document.getElementById('receta').value = historial.receta;
        document.getElementById('proxima_visita').value = historial.proxima_visita;
        
        // Mostrar formulario
        document.getElementById('loading').style.display = 'none';
        document.getElementById('formEditarHistorial').style.display = 'block';
        
    } catch (error) {
        mostrarError(error.message);
    }
}

// Corregir la función cargarHistorial
async function cargarHistorial(id) {
    try {
        const response = await fetch(`actualizar_historial.php?id=${id}`);
        if (!response.ok) throw new Error('Error al cargar historial');
        
        const historial = await response.json();
        
        if (historial.error) {
            mostrarError(historial.error);
            return;
        }

        // Formatear fecha para el input datetime-local
        const fecha = new Date(historial.fecha_procedimiento);
        const fechaFormateada = fecha.toISOString().slice(0, 16);

        // Llenar campos del formulario
        document.getElementById('id_historial').value = historial.id_historial;
        document.getElementById('paciente').value = historial.paciente;
        document.getElementById('fecha_procedimiento').value = fechaFormateada;
        document.getElementById('diagnostico').value = historial.diagnostico || '';
        document.getElementById('procedimiento').value = historial.procedimiento || '';
        document.getElementById('observaciones').value = historial.observaciones || '';
        document.getElementById('receta').value = historial.receta || '';
        document.getElementById('proxima_visita').value = historial.proxima_visita || '';
        
        // Mostrar formulario
        document.getElementById('loading').style.display = 'none';
        document.getElementById('formEditarHistorial').style.display = 'block';
        
    } catch (error) {
        mostrarError(error.message);
    }
}

async function actualizarHistorial(event) {
    event.preventDefault();
    
    const formData = {
        id_historial: document.getElementById('id_historial').value,
        fecha_procedimiento: document.getElementById('fecha_procedimiento').value,
        id_tratamiento: document.getElementById('id_tratamiento').value || null,
        diagnostico: document.getElementById('diagnostico').value,
        procedimiento: document.getElementById('procedimiento').value,
        observaciones: document.getElementById('observaciones').value,
        receta: document.getElementById('receta').value,
        proxima_visita: document.getElementById('proxima_visita').value
    };

    try {
        const btn = event.target.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Guardando...';

        const response = await fetch('actualizar_editar.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });

        if (!response.ok) throw new Error('Error en la respuesta del servidor');
        
        const result = await response.json();
        
        if (result.success) {
            mostrarMensaje('success', result.message, true);
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        mostrarMensaje('danger', error.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save me-2"></i>Guardar Cambios';
    }
}

function formatDateTime(dateTimeString) {
    const date = new Date(dateTimeString);
    return date.toISOString().slice(0, 16);
}

function mostrarMensaje(tipo, mensaje, redireccionar = false) {
    const alerta = document.createElement('div');
    alerta.className = `alert alert-${tipo} alert-dismissible fade show`;
    alerta.role = "alert";
    alerta.innerHTML = `
        ${mensaje}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    const contenedor = document.querySelector('.card-body');
    contenedor.insertBefore(alerta, contenedor.firstChild);
    
    if (redireccionar) {
        setTimeout(() => {
            window.location.href = '../historiales.html';
        }, 1500);
    }
}

function mostrarError(mensaje) {
    document.getElementById('loading').innerHTML = `
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>${mensaje}
        </div>
    `;
}
