document.addEventListener('DOMContentLoaded', function() {
    // Inicializar DataTable
    const table = $('#tabla-citas').DataTable({
      ajax: {
        url: '../../php/api/citas/read.php',
        dataSrc: '',
        headers: {
          'Authorization': 'Bearer ' + localStorage.getItem('jwt_token')
        }
      },
      columns: [
        { data: 'id_cita' },
        { data: 'paciente' },
        { data: 'tratamiento' },
        { 
          data: 'fecha_hora',
          render: function(data) {
            return new Date(data).toLocaleString('es-ES');
          }
        },
        { 
          data: 'estado',
          render: function(data) {
            return `<span class="badge badge-${data}">${data.charAt(0).toUpperCase() + data.slice(1)}</span>`;
          }
        },
        {
          data: null,
          render: function(data) {
            return `
              <button class="btn-editar" data-id="${data.id_cita}">
                <i class="fas fa-edit"></i>
              </button>
              <button class="btn-eliminar" data-id="${data.id_cita}">
                <i class="fas fa-trash"></i>
              </button>
            `;
          },
          orderable: false
        }
      ],
      language: {
        url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
      }
    });
  
    // Nueva cita
    $('#nueva-cita').click(function() {
      $('#modal-cita').modal('show');
      $('#form-cita')[0].reset();
      $('#form-cita').data('id', '');
    });
  
    // Editar cita
    $('#tabla-citas tbody').on('click', '.btn-editar', function() {
      const data = table.row($(this).parents('tr')).data();
      $('#form-cita').data('id', data.id_cita);
      $('#paciente-id').val(data.id_paciente);
      $('#tratamiento-id').val(data.id_tratamiento);
      $('#fecha-hora').val(data.fecha_hora.replace(' ', 'T'));
      $('#modal-cita').modal('show');
    });
  
    // Eliminar cita
    $('#tabla-citas tbody').on('click', '.btn-eliminar', function() {
      const id = $(this).data('id');
      if (confirm('¿Está seguro de eliminar esta cita?')) {
        $.ajax({
          url: `../../php/api/citas/delete.php?id=${id}`,
          type: 'DELETE',
          headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('jwt_token')
          },
          success: function() {
            table.ajax.reload();
            showToast('Cita eliminada correctamente', 'success');
          }
        });
      }
    });
  
    // Submit formulario
    $('#form-cita').submit(function(e) {
      e.preventDefault();
      const id = $(this).data('id');
      const url = id ? `../../php/api/citas/update.php?id=${id}` : '../../php/api/citas/create.php';
      const method = id ? 'PUT' : 'POST';
  
      $.ajax({
        url: url,
        type: method,
        data: $(this).serialize(),
        headers: {
          'Authorization': 'Bearer ' + localStorage.getItem('jwt_token')
        },
        success: function() {
          table.ajax.reload();
          $('#modal-cita').modal('hide');
          showToast(`Cita ${id ? 'actualizada' : 'creada'} correctamente`, 'success');
        }
      });
    });
  
    // Helper para notificaciones
    function showToast(message, type) {
      const toast = $(`<div class="toast toast-${type}">${message}</div>`);
      $('body').append(toast);
      setTimeout(() => toast.fadeOut(), 3000);
    }
  });