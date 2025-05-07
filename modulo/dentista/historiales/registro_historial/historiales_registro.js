document.addEventListener('DOMContentLoaded', function() {
    cargarSelectores();
    
    document.getElementById('formHistorial').addEventListener('submit', function(e) {
        e.preventDefault();
        guardarHistorial(e);
    });
});

function cargarSelectores() {
    // Cargar pacientes
    fetch('obtener_historial.php?tipo=pacientes')
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            return response.json();
        })
        .then(data => {
            const select = document.getElementById('id_paciente');
            select.innerHTML = '<option value="">Seleccionar paciente...</option>' + 
                data.map(paciente => 
                    `<option value="${paciente.id_paciente}">${paciente.nombre_apellido}</option>`
                ).join('');
        })
        .catch(error => {
            console.error('Error cargando pacientes:', error);
            mostrarAlerta('danger', 'Error al cargar pacientes');
        });

    // Cargar tratamientos
    fetch('obtener_historial.php?tipo=tratamientos')
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            return response.json();
        })
        .then(data => {
            const select = document.getElementById('id_tratamiento');
            select.innerHTML = '<option value="">Seleccionar tratamiento...</option>' + 
                data.map(tratamiento => 
                    `<option value="${tratamiento.id_tratamiento}">${tratamiento.nombre}</option>`
                ).join('');
        })
        .catch(error => {
            console.error('Error cargando tratamientos:', error);
            mostrarAlerta('warning', 'Error al cargar tratamientos');
        });
}

function guardarHistorial(event) {
    event.preventDefault();
    
    const formData = {
        id_paciente: document.getElementById('id_paciente').value,
        fecha_procedimiento: document.getElementById('fecha_procedimiento').value,
        id_tratamiento: document.getElementById('id_tratamiento').value || null,
        diagnostico: document.getElementById('diagnostico').value,
        procedimiento: document.getElementById('procedimiento').value || null,
        observaciones: document.getElementById('observaciones').value || null,
        receta: document.getElementById('receta').value || null,
        proxima_visita: document.getElementById('proxima_visita').value || null
    };

    // Validación de campos obligatorios
    if (!formData.id_paciente || !formData.diagnostico || !formData.fecha_procedimiento) {
        mostrarAlerta('danger', 'Los campos obligatorios (*) deben ser completados');
        return;
    }

    fetch('registrar_historial.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData)
    })
    .then(response => {
        if (!response.ok) throw new Error('Error en la respuesta del servidor');
        return response.json();
    })
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: data.message,
                timer: 2000
            }).then(() => {
                window.location.href = '../historiales.html';
            });
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error', error.message, 'error');
    });
}

function mostrarAlerta(tipo, mensaje) {
    const alerta = document.createElement('div');
    alerta.className = `alert alert-${tipo} alert-dismissible fade show mt-3`;
    alerta.role = "alert";
    alerta.innerHTML = `
        ${mensaje}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    const contenedor = document.querySelector('.card-body');
    contenedor.insertBefore(alerta, contenedor.firstChild);
}