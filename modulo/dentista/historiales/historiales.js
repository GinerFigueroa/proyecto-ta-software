// historiales.js
document.addEventListener("DOMContentLoaded", () => {
    fetchHistoriales();
    setupEventListeners();
});

async function fetchHistoriales() {
    try {
        const response = await fetch('buscar.php');
        if (!response.ok) throw new Error('Error al cargar historiales');
        
        const data = await response.json();
        renderHistoriales(data);
        
    } catch (error) {
        console.error("Error:", error);
        mostrarError('Error al cargar historiales: ' + error.message);
    }
}

function renderHistoriales(data) {
    const tbody = document.getElementById("tabla-historiales");
    tbody.innerHTML = "";

    if (data.error || !data.length) {
        tbody.innerHTML = `<tr><td colspan="11">${data.error || 'No se encontraron historiales'}</td></tr>`;
        return;
    }

    data.forEach(historial => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td>${historial.id_historial}</td>
            <td>${historial.paciente}</td>
            <td>${historial.tratamiento || 'N/A'}</td>
            <td>${new Date(historial.fecha).toLocaleDateString()}</td>
            <td>${truncateText(historial.diagnostico, 20)}</td>
            <td>${truncateText(historial.procedimiento, 20) || 'N/A'}</td>
            <td>${truncateText(historial.observaciones, 15) || 'N/A'}</td>
            <td>${historial.receta ? '✅' : '❌'}</td>
            <td>${historial.proxima_visita || 'Pendiente'}</td>
            <td>${historial.adjuntos ? '📎' : ''}</td>
            <td>
                <button class="btn btn-sm btn-warning me-2" 
                        onclick="editarHistorial(${historial.id_historial})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger btn-eliminar" 
                        data-id="${historial.id_historial}">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function setupEventListeners() {
    // Delegación de eventos para eliminación
    document.getElementById('tabla-historiales').addEventListener('click', (e) => {
        if(e.target.closest('.btn-eliminar')) {
            const id = e.target.closest('.btn-eliminar').dataset.id;
            confirmarEliminacion(id);
        }
    });
}

function truncateText(text, maxLength) {
    return text?.length > maxLength ? text.substring(0, maxLength) + '...' : text;
}

function editarHistorial(id) {
    window.location.href = `editar_historiales/actualizar_historial.html?id=${id}`;
}

async function confirmarEliminacion(id) {
    const { isConfirmed } = await Swal.fire({
        title: '¿Eliminar historial?',
        text: "¡Esta acción no se puede deshacer!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Eliminar'
    });

    if (isConfirmed) {
        try {
            const response = await fetch(`eliminar.php?id=${id}`, { method: 'DELETE' });
            const result = await response.json();
            
            if (!response.ok) throw new Error(result.error || 'Error en la solicitud');
            
            if (result.success) {
                await Swal.fire('¡Eliminado!', result.message, 'success');
                fetchHistoriales();
            }
        } catch (error) {
            Swal.fire('Error', error.message, 'error');
            console.error("Error al eliminar:", error);
        }
    }
}

function mostrarError(mensaje) {
    const tbody = document.getElementById("tabla-historiales");
    tbody.innerHTML = `<tr><td colspan="11" class="text-danger">${mensaje}</td></tr>`;
}

// Función para recargar la tabla
function refreshTable() {
    fetchHistoriales();
}

// Exportar funciones necesarias para HTML
window.editarHistorial = editarHistorial;
window.confirmarEliminacion = confirmarEliminacion;

