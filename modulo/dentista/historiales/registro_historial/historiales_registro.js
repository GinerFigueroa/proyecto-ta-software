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


// Corregir la función guardarHistorial
async function guardarHistorial(event) {
    event.preventDefault();
    
    // Validar campos obligatorios primero
    if (!document.getElementById('id_paciente').value || 
        !document.getElementById('fecha_procedimiento').value || 
        !document.getElementById('diagnostico').value) {
        mostrarAlerta('danger', 'Los campos obligatorios (*) deben ser completados');
        return;
    }

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

    try {
        const btn = event.target.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Guardando...';

        const response = await fetch('registrar_historial.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });

        if (!response.ok) throw new Error('Error en la respuesta del servidor');
        
        const result = await response.json();
        
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: result.message,
                timer: 2000
            }).then(() => {
                window.location.href = '../../historiales.html';
              
            });
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire('Error', error.message, 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save me-2"></i>Guardar Historial';
    }
}


// Ejemplo en el código de registro (frontend)
async function registrarHistorial(event) {
    event.preventDefault();
    
    try {
        const response = await fetch('registrar_historial.php', {
            method: 'POST',
            body: JSON.stringify(datos)
        });

        if (response.ok) {
            await Swal.fire('Éxito', 'Historial registrado', 'success');
            fetchHistoriales(); // <--- Recargar la lista
        }
    } catch (error) {
        console.error("Error:", error);
    }
}