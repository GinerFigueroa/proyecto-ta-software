$(document).ready(function () {
    let tablaCitas;

    function inicializarTabla() {
        if ($.fn.DataTable.isDataTable('#tablaCitas')) {
            tablaCitas.destroy(); // Eliminar instancia anterior si existe
            $('#tablaCitas').empty(); // Vaciar para reiniciar completamente
            $('#tablaCitas').html(`
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Paciente</th>
                        <th>Tratamiento</th>
                        <th>Dentista</th>
                        <th>Fecha</th>
                        <th>Duración</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
            `); // reconstruye el thead si es necesario
        }

        tablaCitas = $('#tablaCitas').DataTable({
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel me-1"></i> Excel',
                    className: 'btn btn-success',
                    exportOptions: { columns: ':not(:last-child)' }
                },
                {
                    extend: 'pdf',
                    text: '<i class="fas fa-file-pdf me-1"></i> PDF',
                    className: 'btn btn-danger',
                    exportOptions: { columns: ':not(:last-child)' }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print me-1"></i> Imprimir',
                    className: 'btn btn-primary',
                    exportOptions: { columns: ':not(:last-child)' }
                }
            ],
            ajax: {
                url: 'buscar.php',
                dataSrc: 'data',
                error: function (xhr) {
                    console.error("Error al cargar citas:", xhr.responseText);
                    $('#tablaCitas tbody').html(
                        `<tr><td colspan="8" class="text-center text-danger">Error al cargar los datos</td></tr>`
                    );
                }
            },
            columns: [
                { data: 'id_cita' },
                { data: 'paciente' },
                { data: 'tratamiento' },
                { data: 'dentista' },
                {
                    data: 'fecha_hora',
                    render: function (data) {
                        return moment(data).format('DD/MM/YYYY HH:mm');
                    }
                },
                {
                    data: 'duracion',
                    render: function (data) {
                        return data + ' min';
                    }
                },
                {
                    data: 'estado',
                    render: function (data) {
                        const clases = {
                            'Pendiente': 'bg-warning',
                            'Confirmada': 'bg-primary',
                            'Completada': 'bg-success',
                            'Cancelada': 'bg-secondary',
                            'No asistió': 'bg-danger'
                        };
                        return `<span class="badge ${clases[data]}">${data}</span>`;
                    }
                },
                {
                    data: 'id_cita',
                    render: function (id_cita) {
                        return `
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-primary btn-editar" data-id="${id_cita}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger btn-eliminar" data-id="${id_cita}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            order: [[4, 'asc']],
            language: {
                search: "Buscar:",
                lengthMenu: "Mostrar _MENU_ registros",
                info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                paginate: {
                    first: "Primero",
                    last: "Último",
                    next: "Siguiente",
                    previous: "Anterior"
                },
                buttons: {
                    copyTitle: 'Copiado al portapapeles',
                    copySuccess: {
                        _: '%d filas copiadas',
                        1: '1 fila copiada'
                    }
                }
            },
            pageLength: 10,
            responsive: true
        });
    }

    function cargarEstadisticas() {
        $.ajax({
            url: 'estadisticas.php',
            method: 'GET',
            success: function (response) {
                $('#citas-hoy').text(response.hoy || 0);
                $('#citas-completadas').text(response.completadas || 0);
                $('#citas-pendientes').text(response.pendientes || 0);
                $('#citas-canceladas').text(response.canceladas || 0);
            },
            error: function (xhr) {
                console.error("Error al cargar estadísticas:", xhr.responseText);
            }
        });
    }

    function cargarFiltros() {
        $.ajax({
            url: 'funciones.php?accion=cargarDentistas',
            method: 'GET',
            success: function (response) {
                const select = $('#filtro-dentista');
                select.empty().append('<option value="">Todos</option>');
                response.forEach(dentista => {
                    select.append(`<option value="${dentista.id_dentista}">${dentista.nombre_completo}</option>`);
                });
            },
            error: function (xhr) {
                console.error("Error al cargar dentistas:", xhr.responseText);
            }
        });
    }

    function aplicarFiltros() {
        const estado = $('#filtro-estado').val();
        const dentista = $('#filtro-dentista').val();
        const fechaInicio = $('#filtro-fecha-inicio').val();
        const fechaFin = $('#filtro-fecha-fin').val();

        let url = `buscar.php?estado=${estado}&dentista=${dentista}`;
        if (fechaInicio) url += `&fecha_inicio=${fechaInicio}`;
        if (fechaFin) url += `&fecha_fin=${fechaFin}`;

        tablaCitas.ajax.url(url).load();
    }

    $('#filtro-estado, #filtro-dentista, #filtro-fecha-inicio, #filtro-fecha-fin').on('change', aplicarFiltros);

    $(document).on('click', '.btn-eliminar', function () {
        const id = $(this).data('id');

        Swal.fire({
            title: '¿Eliminar Cita?',
            text: "¡Esta acción no se puede deshacer!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Eliminar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'eliminar.php',
                    method: 'POST',
                    data: { id_cita: id },
                    success: function () {
                        tablaCitas.ajax.reload();
                        Swal.fire('¡Eliminado!', 'La cita fue eliminada', 'success');
                    },
                    error: function () {
                        Swal.fire('Error', 'No se pudo eliminar la cita', 'error');
                    }
                });
            }
        });
    });

    $(document).on('click', '.btn-editar', function () {
        const id = $(this).data('id');

        if (typeof id === 'undefined' || id === null || isNaN(id) || id <= 0) {
            Swal.fire('Error', 'ID de cita inválido', 'error');
            return;
        }

        window.location.href = `editar_cita/cita_editar.html?id_cita=${id}`;
    });

    // Inicialización única
    inicializarTabla();
    cargarEstadisticas();
    cargarFiltros();

    // Actualización periódica
    setInterval(() => {
        tablaCitas.ajax.reload();
        cargarEstadisticas();
    }, 300000); // 5 minutos
});
