document.addEventListener('DOMContentLoaded', function() {
    cargarEspecialidades();
    
    document.getElementById('form-registro-tratamiento').addEventListener('submit', function(e) {
        e.preventDefault();
        registrarTratamiento();
    });
});

function cargarEspecialidades() {
    fetch('obtener_especialidades.php')
    .then(response => {
        if (!response.ok) throw new Error('Error al cargar especialidades');
        return response.json();
    })
    .then(data => {
        const select = document.getElementById('id_especialidad');
        select.innerHTML = '<option value="">Seleccione una especialidad...</option>';
        
        data.forEach(especialidad => {
            const option = document.createElement('option');
            option.value = especialidad.id_especialidad;
            option.textContent = especialidad.nombre;
            select.appendChild(option);
        });
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error', 'No se pudieron cargar las especialidades', 'error');
    });
}

function registrarTratamiento() {
    const form = document.getElementById('form-registro-tratamiento');
    
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }

    const formData = {
        nombre: document.getElementById('nombre').value,
        id_especialidad: document.getElementById('id_especialidad').value || null,
        descripcion: document.getElementById('descripcion').value,
        duracion_estimada: document.getElementById('duracion').value,
        costo: document.getElementById('costo').value,
        requisitos: document.getElementById('requisitos').value
    };

    fetch('registrar_tratamiento.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData)
    })
    .then(async response => {
        const result = await response.json();
        if (!response.ok) throw new Error(result.error || 'Error desconocido');
        
        Swal.fire({
            icon: 'success',
            title: '¡Registro exitoso!',
            text: 'Tratamiento registrado correctamente',
            timer: 2000
        }).then(() => {
            window.location.href = '../tratamientos.html';
        });
    })
    .catch(error => {
        Swal.fire('Error', error.message, 'error');
    });
}