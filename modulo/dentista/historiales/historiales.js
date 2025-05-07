document.addEventListener('DOMContentLoaded', function() {
    cargarHistoriales();
});

function cargarHistoriales() {
    fetch('buscar.php')
        .then(response => response.json())
        .then(data => {
            let html = `
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Paciente</th>
                            <th>Fecha</th>
                            <th>Tratamiento</th>
                            <th>Diagnóstico</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>`;
            
            data.forEach(historial => {
                html += `
                    <tr>
                        <td>${historial.paciente}</td>
                        <td>${historial.fecha}</td>
                        <td>${historial.tratamiento}</td>
                        <td>${historial.diagnostico}</td>
                        <td>
                            <button onclick="editarHistorial(${historial.id})" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="eliminarHistorial(${historial.id})" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>`;
            });
            
            html += `</tbody></table>`;
            document.getElementById('tabla-historiales').innerHTML = html;
        });
}

function cargarRegistro() {
    window.location.href = 'registro_historial/historiales_registro.html';
   
}

function editarHistorial(id) {
    window.location.href = `ditar_historiales/actualizar_historial.html?id=${id}`;


}

async function eliminarHistorial(id) {
    if (confirm('¿Está seguro de eliminar este registro?')) {
        const response = await fetch(`eliminar.php?id=${id}`);
        const result = await response.json();
        if (result.success) {
            cargarHistoriales();
        }
        alert(result.message);
    }
}