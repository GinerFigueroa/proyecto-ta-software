document.addEventListener('DOMContentLoaded', function() {
    // Datos de los servicios ordenados ascendentemente por nombre
    const servicios = [
        {
            id: 1,
            nombre: "Endodoncia",
            descripcion: "Tratamiento sin dolor, rápido y seguro de las raíces del diente en el que se procede a la extracción total del nervio dental.",
            imagen: "../assets/img/endoncia.jpg",
            precio: 1500
        },
        {
            id: 2,
            nombre: "Estética Dental",
            descripcion: "Blanqueamiento, carillas y coronas para una sonrisa radiante.",
            imagen: "../assets/img/blanqueamiento.jpg",
            precio: 1200
        },
        {
            id: 3,
            nombre: "Implante Dental",
            descripcion: "Los implantes dentales están compuestos por tornillos de titanio y zirconio altamente seguros y compatibles con la cavidad bucal, hueso y encía.",
            imagen: "../assets/img/Implante-Dental.jpg",
            precio: 2800
        },
        {
            id: 4,
            nombre: "Implantes Dentales",
            descripcion: "Recupera la funcionalidad y estética de tus dientes con nuestros implantes de titanio de última generación.",
            imagen: "../assets/img/implantedental.jpg",
            precio: 2500
        },
        {
            id: 5,
            nombre: "Odontopediatría",
            descripcion: "Cuidado dental especializado para niños desde los 3 años.",
            imagen: "../assets/img/Odontopediatría.jpg",
            precio: 900
        },
        {
            id: 6,
            nombre: "Ortodoncia",
            descripcion: "Brackets metálicos, estéticos y alineadores invisibles para una sonrisa perfecta.",
            imagen: "../assets/img/braces.jpg",
            precio: 1800
        },
        {
            id: 7,
            nombre: "Periodoncia",
            descripcion: "Tratamiento especializado para enfermedades de las encías y tejidos de soporte dental.",
            imagen: "../assets/img/periondoncia.jpg",
          
            precio: 1100
        },
        {
            id: 8,
            nombre: "Rehabilitación e Implantes",
            descripcion: "Especialidad dedicada a devolver la función masticatoria, restaurar y/o devolver la pérdida de dientes a través del uso de prótesis fija, removible e implantes.",
            imagen: "../assets/img/reabilitaciondental.jpg",
            precio: 3000
        }
    ].sort((a, b) => a.nombre.localeCompare(b.nombre));

    // Cargar servicios en el grid
    const servicesGrid = document.getElementById('servicesGrid');
    
    servicios.forEach(servicio => {
        const serviceItem = document.createElement('div');
        serviceItem.className = 'service-item';
        
        serviceItem.innerHTML = `
            <div class="service-image-container">
                <img src="${servicio.imagen}" alt="${servicio.nombre}" class="service-image">
                <button class="reserva-btn" data-id="${servicio.id}">
                    <i class="fas fa-calendar-alt"></i> Reservar
                </button>
            </div>
            <div class="service-info">
                <h3>${servicio.nombre}</h3>
                <p>${servicio.descripcion}</p>
                <p class="service-price">$${servicio.precio.toFixed(2)}</p>
            </div>
        `;
        
        servicesGrid.appendChild(serviceItem);
    });

    // Modal de reserva
    const modal = document.getElementById('reservaModal');
    const modalTitle = document.getElementById('modalServiceTitle');
    const servicioIdInput = document.getElementById('servicioId');
    const reservaForm = document.getElementById('reservaForm');
    const closeModal = document.querySelector('.close-modal');

    // Delegación de eventos para los botones de reserva
    document.addEventListener('click', function(e) {
        if (e.target.closest('.reserva-btn')) {
            const btn = e.target.closest('.reserva-btn');
            const servicioId = btn.getAttribute('data-id');
            const servicio = servicios.find(s => s.id == servicioId);
            
            modalTitle.textContent = `Reservar ${servicio.nombre}`;
            servicioIdInput.value = servicioId;
            modal.style.display = 'block';
        }
    });

    // Cerrar modal
    closeModal.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    // Cerrar al hacer clic fuera del modal
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });

    // Envío del formulario de reserva
    reservaForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            servicio: servicioIdInput.value,
            nombre: document.getElementById('nombre').value,
            email: document.getElementById('email').value,
            telefono: document.getElementById('telefono').value,
            fecha: document.getElementById('fecha').value
        };
        
        console.log('Reserva enviada:', formData);
        alert(`¡Gracias por tu reserva para ${modalTitle.textContent}! Te contactaremos pronto para confirmar.`);
        
        reservaForm.reset();
        modal.style.display = 'none';
    });

    // Lightbox para imágenes
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightbox-img');
    const lightboxCaption = document.getElementById('lightbox-caption');
    const closeLightbox = document.querySelector('.close-lightbox');

    // Delegación de eventos para las imágenes
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('service-image')) {
            const img = e.target;
            lightbox.style.display = 'block';
            lightboxImg.src = img.src;
            lightboxCaption.textContent = img.alt;
            
            // Ajustar tamaño manteniendo relación de aspecto
            const imgRatio = img.naturalWidth / img.naturalHeight;
            if(imgRatio > 1) {
                lightboxImg.style.width = 'auto';
                lightboxImg.style.height = '80vh';
            } else {
                lightboxImg.style.width = '80vw';
                lightboxImg.style.height = 'auto';
            }
        }
    });

    // Cerrar lightbox
    closeLightbox.addEventListener('click', function() {
        lightbox.style.display = 'none';
    });

    // Cerrar lightbox al hacer clic fuera
    window.addEventListener('click', function(event) {
        if (event.target === lightbox) {
            lightbox.style.display = 'none';
        }
    });

    // Cerrar con tecla ESC
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            if (modal.style.display === 'block') {
                modal.style.display = 'none';
            }
            if (lightbox.style.display === 'block') {
                lightbox.style.display = 'none';
            }
        }
    });

    // Mobile menu toggle
    const mobileBtn = document.querySelector('.mobile-menu-btn');
    const mainNav = document.querySelector('.main-nav');
    
    if(mobileBtn && mainNav) {
        mobileBtn.addEventListener('click', function() {
            mainNav.classList.toggle('active');
            this.innerHTML = mainNav.classList.contains('active') ? 
                '<i class="fas fa-times"></i>' : '<i class="fas fa-bars"></i>';
        });
    }
});