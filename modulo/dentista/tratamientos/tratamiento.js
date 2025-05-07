document.addEventListener('DOMContentLoaded', function() {
    cargarTratamientos();
});

function cargarTratamientos() {
    fetch('buscar.php')
        .then(response => response.json())
        .then(data => {
            const tabla = document.getElementById('tabla-tratamientos');
            tabla.innerHTML = data.map(tratamiento => `
                <tr>
                    <td>${tratamiento.nombre}</td>
                    <td>${tratamiento.duracion} min</td>
                    <td>$${tratamiento.costo}</td>
                    <td>${tratamiento.especialidad}</td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-warning" onclick="editarTratamiento(${tratamiento.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="eliminarTratamiento(${tratamiento.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        });
}

function cargarRegistro() {
    window.location.href = 'registro_tratamiento/tratamiento_registro.html';
}

async function eliminarTratamiento(id) {
    if (confirm('¿Está seguro de eliminar este tratamiento?')) {
        const response = await fetch(`eliminar.php?id=${id}`);
        const result = await response.json();
        if (result.success) {
            cargarTratamientos();
            alert(result.message);
        }
    }
}

function editarTratamiento(id) {
    window.location.href = `editar_tratamiento/actualizar_tratamiento.html?id=${id}`;
}