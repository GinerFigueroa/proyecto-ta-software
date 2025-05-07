document.addEventListener('DOMContentLoaded', function() {
    cargarUsuarios();
    
    document.getElementById('form-registro-paciente').addEventListener('submit', function(e) {
        e.preventDefault();
        registrarPaciente();
    });
});

function cargarUsuarios() {
    fetch('obtener_usuarios.php')
    .then(response => response.json())
    .then(data => {
        const select = document.getElementById('select-usuario');
        select.innerHTML = '<option value="">Seleccionar usuario...</option>';
        
        data.forEach(usuario => {
            const option = document.createElement('option');
            option.value = usuario.id_usuario;
            option.textContent = usuario.nombre_apellido;
            select.appendChild(option);
        });
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error', 'No se pudieron cargar los usuarios', 'error');
    });
}

function registrarPaciente() {
    const form = document.getElementById('form-registro-paciente');
    const formData = new FormData(form);
    
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }

    const data = {
        id_usuario: formData.get('id_usuario') || null,
        fecha_nacimiento: formData.get('fecha_nacimiento'),
        genero: formData.get('genero'),
        alergias: formData.get('alergias'),
        enfermedades_cronicas: formData.get('enfermedades_cronicas'),
        medicamentos: formData.get('medicamentos'),
        seguro_medico: formData.get('seguro_medico'),
        numero_seguro: formData.get('numero_seguro')
    };

    fetch('registrar_paciente.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(async response => {
        const result = await response.json();
        if (!response.ok) throw new Error(result.error);
        
        Swal.fire({
            icon: 'success',
            title: '¡Registro exitoso!',
            text: 'Paciente registrado correctamente',
            timer: 2000
        }).then(() => {
            window.location.href = '../pacientes.html';
        });
    })
    .catch(error => {
        Swal.fire('Error', error.message, 'error');
    });
}