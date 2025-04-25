document.addEventListener('DOMContentLoaded', function() {
    // Cargar citas del cliente
    loadCitas();
  
    // Inicializar datepicker
    $('#fecha-cita').flatpickr({
      enableTime: true,
      dateFormat: "Y-m-d H:i",
      minDate: "today",
      time_24hr: true,
      locale: "es"
    });
  
    // Nueva cita
    document.getElementById('btn-nueva-cita').addEventListener('click', function() {
      document.getElementById('modal-cita').classList.add('active');
    });
  
    // Cerrar modal
    document.querySelector('.modal .close').addEventListener('click', closeModal);
  
    // Submit formulario
    document.getElementById('form-cita').addEventListener('submit', submitCita);
  });
  
  function loadCitas() {
    fetch('../../php/api/citas/mis-citas.php', {
      headers: {
        'Authorization': 'Bearer ' + localStorage.getItem('jwt_token')
      }
    })
    .then(response => response.json())
    .then(data => {
      const container = document.getElementById('citas-container');
      container.innerHTML = '';
  
      if (data.length === 0) {
        container.innerHTML = '<p class="no-results">No tienes citas agendadas</p>';
        return;
      }
  
      data.forEach(cita => {
        const card = document.createElement('div');
        card.className = `appointment-card ${cita.estado}`;
        card.innerHTML = `
          <div class="appointment-info">
            <h3>${cita.tratamiento}</h3>
            <p><i class="far fa-calendar"></i> ${formatDate(cita.fecha_hora)}</p>
            <p><i class="fas fa-user-md"></i> ${cita.doctor || 'Por asignar'}</p>
            <p><i class="fas fa-info-circle"></i> Estado: ${cita.estado}</p>
          </div>
          <div class="appointment-actions">
            ${cita.estado === 'pendiente' ? `
              <button class="btn btn-outline btn-cancelar" data-id="${cita.id_cita}">
                Cancelar
              </button>` : ''
            }
          </div>
        `;
        container.appendChild(card);
      });
  
      // Event listeners para botones de cancelar
      document.querySelectorAll('.btn-cancelar').forEach(btn => {
        btn.addEventListener('click', cancelarCita);
      });
    });
  }
  
  function cancelarCita(e) {
    const id = e.target.dataset.id;
    if (confirm('¿Estás seguro de cancelar esta cita?')) {
      fetch(`../../php/api/citas/cancelar.php?id=${id}`, {
        method: 'PUT',
        headers: {
          'Authorization': 'Bearer ' + localStorage.getItem('jwt_token')
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showAlert('Cita cancelada correctamente', 'success');
          loadCitas();
        }
      });
    }
  }
  
  function submitCita(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
  
    fetch('../../php/api/citas/create.php', {
      method: 'POST',
      headers: {
        'Authorization': 'Bearer ' + localStorage.getItem('jwt_token')
      },
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showAlert('Cita agendada correctamente', 'success');
        form.reset();
        closeModal();
        loadCitas();
      }
    });
  }
  
  // Helpers
  function formatDate(dateString) {
    const options = { 
      weekday: 'long', 
      year: 'numeric', 
      month: 'long', 
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    };
    return new Date(dateString).toLocaleDateString('es-ES', options);
  }
  
  function closeModal() {
    document.getElementById('modal-cita').classList.remove('active');
  }
  
  function showAlert(message, type) {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;
    document.body.appendChild(alert);
    setTimeout(() => alert.remove(), 3000);
  }