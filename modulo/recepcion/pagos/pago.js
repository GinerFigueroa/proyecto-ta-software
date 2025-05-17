// Configuración inicial de DataTables
$(document).ready(function() {
    const tablaPagos = $('#tablaPagos').DataTable({
        ajax: {
            url: 'buscar_pago.php',
            dataSrc: 'data'
        },
        columns: [
            { data: 'id_pago' },
            { data: 'paciente' },
            { data: 'tratamiento' },
            { data: 'monto_formateado' },
            { data: 'metodo_pago' },
            { 
                data: 'estado_visual',
                render: function(data, type, row) {
                    let badgeClass = '';
                    switch(row.estado) {
                        case 'Completado': badgeClass = 'bg-success'; break;
                        case 'Pendiente': badgeClass = 'bg-warning text-dark'; break;
                        case 'Reembolsado': badgeClass = 'bg-info text-dark'; break;
                        case 'Cancelado': badgeClass = 'bg-danger'; break;
                        default: badgeClass = 'bg-secondary';
                    }
                    return `<span class="badge ${badgeClass}">${data}</span>`;
                }
            },
            { data: 'fecha_pago_formateada' },
            {
                data: 'id_pago',
                render: function(data, type, row) {
                    return `
                        <div class="btn-group" role="group">
                            <a href="editar_pago/pago_editar.html?id=${data}" class="btn btn-sm btn-primary">✏️ Editar</a>
                            <button onclick="confirmarEliminar(${data})" class="btn btn-sm btn-danger">🗑️ Eliminar</button>
                        </div>
                    `;
                },
                orderable: false
            }
        ],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
        }
    });

    // Cargar citas disponibles en el formulario de registro
    if (window.location.pathname.includes('pago_registro.html')) {
        cargarCitasDisponibles();
        
        // Configurar formulario de registro
        $('#formPago').on('submit', function(e) {
            e.preventDefault();
            registrarPago();
        });
    }

    // Configurar formulario de edición
    if (window.location.pathname.includes('pago_editar.html')) {
        const urlParams = new URLSearchParams(window.location.search);
        const idPago = urlParams.get('id');
        
        if (idPago) {
            cargarDatosPago(idPago);
            
            $('#formEditarPago').on('submit', function(e) {
                e.preventDefault();
                actualizarPago(idPago);
            });
        } else {
            alert('No se proporcionó ID de pago');
            window.location.href = 'pago.html';
        }
    }
});

// Funciones para el formulario de registro
function cargarCitasDisponibles() {
    console.log("Iniciando carga de citas...");
    
    $.ajax({
        url: 'crear_pago/obtener_datos.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log("Respuesta recibida:", response);
            
            if (response.success) {
                console.log("Datos recibidos:", response.data);
                const select = $('#id_cita');
                select.empty();
                
                if (response.data && response.data.length > 0) {
                    select.append('<option value="">Seleccionar cita...</option>');
                    
                    response.data.forEach(cita => {
                        console.log("Procesando cita:", cita);
                        select.append(`<option value="${cita.id_cita}" data-monto="${cita.monto_sugerido || ''}">${cita.info_cita}</option>`);
                    });
                    
                    select.on('change', function() {
                        const monto = $(this).find(':selected').data('monto');
                        if (monto) {
                            $('#monto').val(monto);
                        }
                    });
                } else {
                    select.append('<option value="">No hay citas disponibles para pago</option>');
                }
            } else {
                console.error('Error al cargar citas:', response.message);
                $('#id_cita').html('<option value="">Error al cargar citas</option>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error AJAX:', error);
            console.log("Estado:", status);
            console.log("Respuesta:", xhr.responseText);
            $('#id_cita').html('<option value="">Error de conexión</option>');
        }
    });
}

       


function registrarPago() {
    const formData = {
        id_cita: $('#id_cita').val(),
        monto: $('#monto').val(),
        metodo_pago: $('#metodo_pago').val(),
        referencia: $('#referencia').val(),
        notas: $('#notas').val()
    };
    
    if (!formData.id_cita) {
        alert('Por favor seleccione una cita');
        return;
    }
    
    $.ajax({
        url: 'crear_pago/registrar_pago.php',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(formData),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('Pago registrado correctamente');
                window.location.href = 'pago.html';
            } else {
                alert('Error al registrar pago: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            alert('Error al registrar pago: ' + error);
        }
    });
}

// Funciones para el formulario de edición
function cargarDatosPago(idPago) {
    $.ajax({
        url: `editar_pago/obtener_pago.php?id=${idPago}`,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const pago = response.data;
                
                $('#id_pago').val(pago.id_pago);
                $('#info_paciente').val(pago.info_paciente);
                $('#tratamiento').val(pago.tratamiento);
                $('#monto').val(pago.monto);
                $('#metodo_pago').val(pago.metodo_pago);
                $('#estado').val(pago.estado);
                $('#referencia').val(pago.referencia);
                $('#fecha_pago').val(pago.fecha_pago_iso);
                $('#notas').val(pago.notas);
            } else {
                alert('Error al cargar pago: ' + response.message);
                window.location.href = '../pago.html';
            }
        },
        error: function(xhr, status, error) {
            alert('Error al cargar pago: ' + error);
            window.location.href = 'pago.html';
        }
    });
}

function actualizarPago(idPago) {
    const formData = {
        id_pago: idPago,
        monto: $('#monto').val(),
        metodo_pago: $('#metodo_pago').val(),
        estado: $('#estado').val(),
        referencia: $('#referencia').val(),
        fecha_pago: $('#fecha_pago').val(),
        notas: $('#notas').val()
    };
    
    $.ajax({
        url: 'editar_pago/actualizar_pago.php',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(formData),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('Pago actualizado correctamente');
                window.location.href = 'pago.html';
            } else {
                alert('Error al actualizar pago: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            alert('Error al actualizar pago: ' + error);
        }
    });
}

// Función para eliminar pagos
function confirmarEliminar(idPago) {
    if (confirm('¿Está seguro que desea eliminar este pago? Esta acción no se puede deshacer.')) {
        $.ajax({
            url: 'eliminar.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ id: idPago }),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Pago eliminado correctamente');
                    $('#tablaPagos').DataTable().ajax.reload();
                } else {
                    alert('Error al eliminar pago: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                alert('Error al eliminar pago: ' + error);
            }
        });
    }
}

