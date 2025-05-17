document.addEventListener("DOMContentLoaded", () => {
    fetchTratamientos();
});

function fetchTratamientos() {
    fetch("listar_tratamientos.php")
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById("tabla-tratamientos");
            tbody.innerHTML = "";

            if (data.length === 0 || data.error) {
                tbody.innerHTML = `<tr><td colspan="8">No se encontraron tratamientos.</td></tr>`;
                return;
            }

            data.forEach(tratamiento => {
                const tr = document.createElement("tr");

           tr.innerHTML = `
    <td>${tratamiento.id_tratamiento}</td>
    <td>${tratamiento.nombre}</td>
    <td>${tratamiento.especialidad || 'No asignada'}</td>
    <td>${tratamiento.descripcion}</td>
    <td>${tratamiento.duracion_estimada} min</td>
    <td>S/ ${parseFloat(tratamiento.costo).toFixed(2)}</td>
    <td>${tratamiento.requisitos || '-'}</td>
    <td class="text-end d-flex gap-2">
        <button class="btn btn-sm btn-warning d-flex align-items-center justify-content-center" 
                style="width: 35px; height: 35px;" 
                onclick="editarTratamiento(${tratamiento.id_tratamiento})">
            <i class="fas fa-edit"></i>
        </button>
        <button class="btn btn-sm btn-danger d-flex align-items-center justify-content-center btn-center" 
                style="width: 35px; height: 35px;" 
                data-id="${tratamiento.id_tratamiento}">
            <i class="fas fa-trash"></i>
        </button>
    </td>
`;

                tbody.appendChild(tr);
            });
        })
        .catch(error => {
            console.error("Error al cargar tratamientos:", error);
        });
}


function cargarRegistro() {
    window.location.href = 'registro_tratamiento/tratamiento_registro.html';
}



function editarTratamiento(id) {
    window.location.href = `editar_tratamiento/actualizar_tratamiento.html?id=${id}`;


}
// actualizar_tratamiento.js
document.addEventListener("DOMContentLoaded", () => {
    // Obtener ID de la URL
    const urlParams = new URLSearchParams(window.location.search);
    const idTratamiento = urlParams.get('id');


          $(document).on('click', '.btn-editar', function() {
        const id = $(this).data('id');
        
        // Validación robusta del ID
        if (typeof id === 'undefined' || id === null || isNaN(id) || id <= 0) {
            Swal.fire('Error', 'ID de cita inválido', 'error');
            return;
        }
        
        // Redirección al HTML (no al PHP)
        window.location.href = `editar_tratamiento/actualizar_tratamiento.html?id=${id}`; 
    });
});
// Función para eliminar tratamiento


        document.addEventListener("DOMContentLoaded", () => {
    fetchTratamientos();
});

// Función para eliminar tratamiento
$(document).on('click', '.btn-eliminar', function() {
    const id = $(this).data('id');

    Swal.fire({
        title: '¿Eliminar tratamiento?',
        text: "¡Esta acción no se puede deshacer!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Eliminar'
    }).then((result) => {
        if (result.isConfirmed) {
            eliminarTratamiento(id);
        }
    });
});

async function eliminarTratamiento(id) {
    try {
        // Cambiado a GET y usando el parámetro en la URL
        const response = await fetch(`eliminar_tratamiento.php?id=${id}`);
        const result = await response.json();

        if (result.success) {
            fetchTratamientos(); // Recarga la tabla
            Swal.fire('¡Eliminado!', result.message, 'success');
        } else {
            Swal.fire('Error', result.error, 'error');
        }
    } catch (error) {
        console.error("Error al eliminar tratamiento:", error);
        Swal.fire('Error', 'Ocurrió un problema al eliminar el tratamiento.', 'error');
    }
}