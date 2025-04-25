

document.addEventListener('DOMContentLoaded', () => {
    // Cargar datos de citas via AJAX
    fetch('../php/api/citas/read.php')
        .then(response => response.json())
        .then(data => renderCitas(data));

    // Modal de nueva cita
    document.getElementById('nueva-cita').addEventListener('click', () => {
        document.getElementById('modal-titulo').textContent = 'Nueva Cita';
        document.getElementById('form-cita').reset();
        // Cargar select de pacientes y tratamientos
        cargarSelects();
        abrirModal();
    });
});

function renderCitas(citas) {
    const tbody = document.querySelector('#tabla-citas tbody');
    tbody.innerHTML = citas.map(cita => `
        <tr>
            <td>${cita.id}</td>
            <td>${cita.paciente_nombre}</td>
            <td>${cita.tratamiento_nombre}</td>
            <td>${new Date(cita.fecha_hora).toLocaleString()}</td>
            <td><span class="badge-${cita.estado}">${cita.estado}</span></td>
            <td>
                <button class="btn-editar" data-id="${cita.id}">Editar</button>
                <button class="btn-cancelar" data-id="${cita.id}">Cancelar</button>
            </td>
        </tr>
    `).join('');
}