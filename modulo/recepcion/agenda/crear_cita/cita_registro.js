document.addEventListener('DOMContentLoaded', function() {
    cargarSelectores();
    
    document.getElementById('form-registro-cita').addEventListener('submit', function(e) {
        e.preventDefault();
        registrarCita();
    });
});

// Cargar selectores
function cargarSelectores() {
    // Pacientes
    fetch('obtener_usuario_cita.php?accion=cargarPacientes')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('id_paciente');
            select.innerHTML = '<option value="">Seleccionar paciente...</option>';
            
            if(data && data.length > 0) {
                data.forEach(paciente => {
                    const option = document.createElement('option');
                    option.value = paciente.id_paciente;
                    option.textContent = paciente.nombre_completo;
                    select.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'No se pudieron cargar los pacientes', 'error');
        });

    // Tratamientos
    fetch('obtener_usuario_cita.php?accion=cargarTratamientos')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('id_tratamiento');
            select.innerHTML = '<option value="">Seleccionar tratamiento...</option>';
            
            if(data && data.length > 0) {
                data.forEach(tratamiento => {
                    const option = document.createElement('option');
                    option.value = tratamiento.id_tratamiento;
                    option.setAttribute('data-duracion', tratamiento.duracion_estimada);
                    option.textContent = tratamiento.nombre;
                    select.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'No se pudieron cargar los tratamientos', 'error');
        });

    // Dentistas
    fetch('obtener_usuario_cita.php?accion=cargarDentistas')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('id_dentista');
            select.innerHTML = '<option value="">Seleccionar dentista...</option>';
            
            if(data && data.length > 0) {
                data.forEach(dentista => {
                    const option = document.createElement('option');
                    option.value = dentista.id_dentista;
                    option.textContent = dentista.nombre_completo;
                    select.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'No se pudieron cargar los dentistas', 'error');
        });
}

function registrarCita() {
    const form = document.getElementById('form-registro-cita');
    const formData = new FormData(form);
    
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }

    const data = {
        id_paciente: formData.get('id_paciente'),
        id_tratamiento: formData.get('id_tratamiento'),
        id_dentista: formData.get('id_dentista'),
        fecha_hora: formData.get('fecha_hora'),
        duracion: formData.get('duracion'),
        estado: formData.get('estado'),
        notas: formData.get('notas'),
        recordatorio_enviado: formData.get('recordatorio_enviado'),
        creado_por: 4 // ID del usuario logueado (deberías obtenerlo de la sesión)
    };

    fetch('registrar_cita.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(async response => {
        const result = await response.json();
        if (!response.ok) throw new Error(result.error || 'Error al registrar la cita');
        
        Swal.fire({
            icon: 'success',
            title: '¡Registro exitoso!',
            text: 'Cita registrada correctamente',
            timer: 2000
        }).then(() => {
            window.location.href = '../citas.html';
        });
    })
    .catch(error => {
        Swal.fire('Error', error.message, 'error');
    });
}
