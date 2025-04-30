document.addEventListener('DOMContentLoaded', function() {
    // Cerrar automáticamente las alertas después de 5 segundos
    const alertas = document.querySelectorAll('.alert');
    
    if (alertas.length > 0) {
        setTimeout(function() {
            alertas.forEach(function(alerta) {
                alerta.style.transition = 'opacity 1s ease';
                alerta.style.opacity = '0';
                
                setTimeout(function() {
                    alerta.remove();
                }, 1000);
            });
        }, 5000);
    }
    
    // Validar los formularios
    const formRegistro = document.getElementById('form-registro');
    
    if (formRegistro) {
        formRegistro.addEventListener('submit', function(event) {
            const password = document.getElementById('contrasena').value;
            const confirm = document.getElementById('repetir_contrasena').value;
            
            if (password !== confirm) {
                event.preventDefault();
                alert('Las contraseñas no coinciden');
            }
        });
    }
    
    // Animación para las tarjetas de los coches
    const carCards = document.querySelectorAll('.car-card');
    
    carCards.forEach(function(card) {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 10px 20px rgba(0, 0, 0, 0.15)';
            this.style.transition = 'all 0.3s ease';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 0 10px rgba(0, 0, 0, 0.1)';
        });
    });
    
    // Iniciar menú
    const dropdownToggles = document.querySelectorAll('.dropdown');
    
    dropdownToggles.forEach(function(dropdown) {
        dropdown.addEventListener('click', function(event) {
            const dropdownContent = this.querySelector('.dropdown-content');
            
            if (dropdownContent) {
                if (dropdownContent.style.display === 'block') {
                    dropdownContent.style.display = 'none';
                } else {
                    // Cerrar otros menús
                    document.querySelectorAll('.dropdown-content').forEach(function(content) {
                        content.style.display = 'none';
                    });
                    
                    dropdownContent.style.display = 'block';
                }
                
                event.stopPropagation();
            }
        });
    });
    
    // Cerrar los menús al hacer clic fuera
    document.addEventListener('click', function() {
        document.querySelectorAll('.dropdown-content').forEach(function(content) {
            content.style.display = 'none';
        });
    });
    
    // Buscar badges (signos de exclamación)
    const badges = document.querySelectorAll('.badge');
    
    // Aplicar animación de rebote a los badges
    badges.forEach(function(badge) {
        badge.style.animation = 'bounce 2s ease-in-out infinite';
    });
    
    // Animar el número de teléfono
    const phoneNumber = document.querySelector('.marquee-text .text-primary:nth-of-type(2)');
    if (phoneNumber) {
        phoneNumber.style.animation = 'pulse 3s ease-in-out infinite';
    }
    
    // Efecto al hacer hover en el marquee
    const marquee = document.querySelector('.marquee-container');
    const marqueeText = document.querySelector('.marquee-text');
    
    if (marquee && marqueeText) {
        marquee.addEventListener('mouseover', function() {
            marqueeText.style.textShadow = '0 0 5px rgba(255, 255, 255, 0.7)';
        });
        
        marquee.addEventListener('mouseout', function() {
            marqueeText.style.textShadow = 'none';
        });
    }
    
    // Gestión para la pantalla de carga
    // Verificar si estamos en la página principal
    const isHomePage = window.location.pathname.endsWith('index.php') || 
                       window.location.pathname === '/' || 
                       window.location.pathname.endsWith('/');
    
    if (isHomePage) {
        // Configuración específica para la página principal (10 segundos)
        const wrapper = document.getElementById('wrapper');
        if (wrapper) {
            wrapper.style.visibility = 'hidden';
        }
        
        const loader = document.getElementById('loader-wrapper');
        if (loader) {
            loader.classList.add('home-loader');
            loader.classList.add('extended-loading');
        }
    } else {
        // Configuración para otras páginas (5 segundos)
        const loader = document.getElementById('loader-wrapper');
        if (loader) {
        }
    }
});

// Alerta temporal
function mostrarAlerta(mensaje, tipo = 'success') {
    const alertaDiv = document.createElement('div');
    alertaDiv.className = 'alert alert-' + tipo;
    alertaDiv.innerHTML = mensaje;
    
    const contenedor = document.querySelector('.main');
    contenedor.prepend(alertaDiv);
    
    setTimeout(function() {
        alertaDiv.style.transition = 'opacity 1s ease';
        alertaDiv.style.opacity = '0';
        
        setTimeout(function() {
            alertaDiv.remove();
        }, 1000);
    }, 5000);
}

// Validar los formularios
function validarFormulario(form) {
    let valido = true;
    
    // Validar los campos requeridos
    form.querySelectorAll('[required]').forEach(function(campo) {
        if (!campo.value.trim()) {
            mostrarError(campo, 'Este campo es obligatorio');
            valido = false;
        } else {
            limpiarError(campo);
        }
    });
    
    // Validar el campo email
    const email = form.querySelector('input[type="email"]');
    if (email && email.value.trim() && !validarEmail(email.value)) {
        mostrarError(email, 'Introduce un email válido');
        valido = false;
    }
    
    // Validar el campo teléfono
    const telefono = form.querySelector('input[type="tel"]');
    if (telefono && telefono.value.trim() && !validarTelefono(telefono.value)) {
        mostrarError(telefono, 'Introduce un teléfono válido (9 dígitos)');
        valido = false;
    }
    
    return valido;
}

// Mostrar el mensaje de error
function mostrarError(campo, mensaje) {
    limpiarError(campo);
    
    campo.classList.add('is-invalid');
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'invalid-feedback';
    errorDiv.textContent = mensaje;
    
    campo.parentNode.appendChild(errorDiv);
}

// Limpiar el mensaje de error
function limpiarError(campo) {
    campo.classList.remove('is-invalid');
    
    const errorDiv = campo.parentNode.querySelector('.invalid-feedback');
    if (errorDiv) {
        errorDiv.remove();
    }
}

// Validar el email
function validarEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Validar el teléfono
function validarTelefono(telefono) {
    const re = /^[0-9]{9}$/;
    return re.test(telefono);
}