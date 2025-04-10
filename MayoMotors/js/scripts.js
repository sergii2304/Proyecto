// Archivo con funciones JavaScript comunes para la aplicación

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
    
    // Validaciones de formularios
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
    
    // Animación para tarjetas de coches
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
    
    // Inicializar menú desplegable
    const dropdownToggles = document.querySelectorAll('.dropdown');
    
    dropdownToggles.forEach(function(dropdown) {
        dropdown.addEventListener('click', function(event) {
            const dropdownContent = this.querySelector('.dropdown-content');
            
            if (dropdownContent) {
                if (dropdownContent.style.display === 'block') {
                    dropdownContent.style.display = 'none';
                } else {
                    // Cerrar otros menús desplegables
                    document.querySelectorAll('.dropdown-content').forEach(function(content) {
                        content.style.display = 'none';
                    });
                    
                    dropdownContent.style.display = 'block';
                }
                
                event.stopPropagation();
            }
        });
    });
    
    // Cerrar menús desplegables al hacer clic fuera
    document.addEventListener('click', function() {
        document.querySelectorAll('.dropdown-content').forEach(function(content) {
            content.style.display = 'none';
        });
    });
});

// Función para mostrar una alerta temporal
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

// Función para validar formularios
function validarFormulario(form) {
    let valido = true;
    
    // Validar campos requeridos
    form.querySelectorAll('[required]').forEach(function(campo) {
        if (!campo.value.trim()) {
            mostrarError(campo, 'Este campo es obligatorio');
            valido = false;
        } else {
            limpiarError(campo);
        }
    });
    
    // Validar email
    const email = form.querySelector('input[type="email"]');
    if (email && email.value.trim() && !validarEmail(email.value)) {
        mostrarError(email, 'Introduce un email válido');
        valido = false;
    }
    
    // Validar teléfono (formato español: 9 dígitos)
    const telefono = form.querySelector('input[type="tel"]');
    if (telefono && telefono.value.trim() && !validarTelefono(telefono.value)) {
        mostrarError(telefono, 'Introduce un teléfono válido (9 dígitos)');
        valido = false;
    }
    
    return valido;
}

// Función para mostrar mensaje de error
function mostrarError(campo, mensaje) {
    limpiarError(campo);
    
    campo.classList.add('is-invalid');
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'invalid-feedback';
    errorDiv.textContent = mensaje;
    
    campo.parentNode.appendChild(errorDiv);
}

// Función para limpiar mensaje de error
function limpiarError(campo) {
    campo.classList.remove('is-invalid');
    
    const errorDiv = campo.parentNode.querySelector('.invalid-feedback');
    if (errorDiv) {
        errorDiv.remove();
    }
}

// Función para validar email
function validarEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Función para validar teléfono (formato español: 9 dígitos)
function validarTelefono(telefono) {
    const re = /^[0-9]{9}$/;
    return re.test(telefono);
}