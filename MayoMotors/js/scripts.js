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
                // Reemplazar alert nativo por función personalizada
                mostrarAlerta('Las contraseñas no coinciden', 'danger');
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
    
    // Cerrar modales de confirmación al hacer clic fuera
    document.addEventListener('click', function(event) {
        const modal = document.getElementById('customConfirmModal');
        if (modal && event.target === modal) {
            closeConfirmModal();
        }
    });
    
    // Añadir tecla Escape para cerrar modales
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeConfirmModal();
        }
    });
    
    // Manejador global para botones de eliminar
    
    document.addEventListener('click', function(event) {
        // Comprobar si es un botón de eliminar coche
        const deleteCarButton = event.target.closest('.delete-car-btn');
        if (deleteCarButton) {
            event.preventDefault();
            event.stopPropagation();
            
            const href = deleteCarButton.getAttribute('href');
            
            mostrarConfirmacion('¿Estás seguro de que deseas eliminar este coche? Esta acción no se puede deshacer.', function(confirmed) {
                if (confirmed) {
                    window.location.href = href;
                }
            });
            
            return false;
        }
        
        // Comprobar si es un botón de eliminar usuario
        const deleteUserButton = event.target.closest('a[href*="eliminar"]');
        if (deleteUserButton && !deleteUserButton.classList.contains('delete-car-btn')) {
            // Verificar que estamos en la página de administración de usuarios
            if (window.location.pathname.includes('admin_usuarios.php')) {
                event.preventDefault();
                event.stopPropagation();
                
                const href = deleteUserButton.getAttribute('href');
                
                mostrarConfirmacion('¿Estás seguro de que deseas eliminar este usuario? Esta acción eliminará también todos sus coches publicados y no se puede deshacer.', function(confirmed) {
                    if (confirmed) {
                        window.location.href = href;
                    }
                });
                
                return false;
            }
        }
    }, true); // true hace que el evento se ejecute en la fase de captura
});

// Alerta temporal
function mostrarAlerta(mensaje, tipo = 'success') {
    const alertaDiv = document.createElement('div');
    alertaDiv.className = 'alert alert-' + tipo;
    alertaDiv.innerHTML = mensaje;
    
    const contenedor = document.querySelector('.main');
    
    if (contenedor) {
        contenedor.prepend(alertaDiv);
        
        setTimeout(function() {
            alertaDiv.style.transition = 'opacity 1s ease';
            alertaDiv.style.opacity = '0';
            
            setTimeout(function() {
                alertaDiv.remove();
            }, 1000);
        }, 5000);
    } else {
        // Fallback si no encontramos el contenedor principal
        // Mostrar un mensaje en la consola
        console.error('No se pudo encontrar el contenedor .main para mostrar la alerta');
    }
}

// Variable global para almacenar el callback de confirmación
let confirmCallback = null;

// Función para mostrar diálogo de confirmación personalizado
function mostrarConfirmacion(mensaje, callback) {
    // Guardar el callback para usarlo más tarde
    confirmCallback = callback;
    
    // Verificar si el modal ya existe
    let modal = document.getElementById('customConfirmModal');
    
    if (!modal) {
        // Crear el modal si no existe
        modal = document.createElement('div');
        modal.id = 'customConfirmModal';
        modal.className = 'custom-modal';
        
        // Crear el contenido del modal
        const modalContent = document.createElement('div');
        modalContent.className = 'custom-modal-content';
        
        // Mensaje
        const mensajeElement = document.createElement('p');
        mensajeElement.className = 'custom-modal-message';
        mensajeElement.textContent = mensaje;
        
        // Contenedor de botones
        const botonesContainer = document.createElement('div');
        botonesContainer.className = 'custom-modal-buttons';
        
        // Botón Aceptar
        const btnAceptar = document.createElement('button');
        btnAceptar.className = 'btn btn-primary';
        btnAceptar.textContent = 'Aceptar';
        btnAceptar.onclick = function() {
            closeConfirmModal(true);
        };
        
        // Botón Cancelar
        const btnCancelar = document.createElement('button');
        btnCancelar.className = 'btn btn-secondary';
        btnCancelar.textContent = 'Cancelar';
        btnCancelar.onclick = function() {
            closeConfirmModal(false);
        };
        
        // Añadir los elementos al modal
        botonesContainer.appendChild(btnAceptar);
        botonesContainer.appendChild(btnCancelar);
        modalContent.appendChild(mensajeElement);
        modalContent.appendChild(botonesContainer);
        modal.appendChild(modalContent);
        
        // Añadir estilos al modal
        const style = document.createElement('style');
        style.textContent = `
            .custom-modal {
                display: none;
                position: fixed;
                z-index: 9999;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                align-items: center;
                justify-content: center;
            }
            
            .custom-modal-content {
                background-color: #fff;
                border-radius: 5px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
                max-width: 500px;
                width: 90%;
                padding: 20px;
                text-align: center;
                animation: modalFadeIn 0.3s;
            }
            
            .custom-modal-message {
                margin-bottom: 20px;
                font-size: 16px;
            }
            
            .custom-modal-buttons {
                display: flex;
                justify-content: center;
                gap: 15px;
            }
            
            @keyframes modalFadeIn {
                from {
                    opacity: 0;
                    transform: translateY(-20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        `;
        
        // Añadir el estilo y el modal al documento
        document.head.appendChild(style);
        document.body.appendChild(modal);
    } else {
        // Actualizar el mensaje si el modal ya existe
        modal.querySelector('.custom-modal-message').textContent = mensaje;
    }
    
    // Mostrar el modal
    modal.style.display = 'flex';
    
    // Poner el foco en el botón Cancelar por defecto (más seguro)
    setTimeout(function() {
        const cancelButton = modal.querySelector('.btn-secondary');
        if (cancelButton) {
            cancelButton.focus();
        }
    }, 100);
}

// Función para cerrar el modal de confirmación
function closeConfirmModal(result) {
    const modal = document.getElementById('customConfirmModal');
    if (modal) {
        modal.style.display = 'none';
        
        // Ejecutar el callback si existe
        if (typeof confirmCallback === 'function') {
            confirmCallback(result);
            // Limpiar el callback
            confirmCallback = null;
        }
    }
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