<?php
// Variable para indicar que estamos en la página principal
$es_pagina_principal = true;

require_once 'layout.php';

mostrarHeader('Inicio');
?>

<h1 class="title">Bienvenido a MayoMotors</h1>

<div class="welcome-text">
    <p>Encuentra el coche de tus sueños al mejor precio</p>
    <br>
    <p class="slogan">¡¡¡ Solo marcas Europeas !!!</p>
</div>

<div class="mayo-logo-container">
    <img src="css/logo.png" alt="MayoMotors Logo" class="mayo-logo">
</div>

<script>
// Script específico para la página de inicio con carga de 5 segundos
document.addEventListener('DOMContentLoaded', function() {
    // Forzar un tiempo de carga exacto de 5 segundos para la página principal
    const loader = document.getElementById('loader-wrapper');
    const counter = document.querySelector('.loader-counter');
    
    if (loader && counter) {
        // Tiempo fijo de 5 segundos (reducido de 6 a 5)
        const totalLoadTime = 5000;
        
        // Evitar que se oculte el loader y asegurar que permanezca visible
        loader.classList.add('extended-loading');
        
        // Asegurar que el contenido principal no sea visible durante la carga
        const wrapper = document.getElementById('wrapper');
        if (wrapper) {
            wrapper.style.visibility = 'hidden';
        }
        
        // Mensajes de carga que se irán mostrando
        const mensajes = [
            'Iniciando motores...',
            'Buscando los mejores coches...',
            'Conectando con la base de datos...',
            'Casi listo para arrancar...'
        ];
        
        // Cambiar mensajes aproximadamente cada 1.5 segundos (ajustado para 5 segundos totales)
        let mensajeActual = 0;
        const cambiarMensaje = setInterval(function() {
            if (mensajeActual < mensajes.length) {
                document.querySelector('.loader-text').textContent = mensajes[mensajeActual];
                mensajeActual++;
            } else {
                // Reiniciar mensajes si se terminan
                mensajeActual = 0;
            }
        }, 1500);
        
        // Controlar el progreso del contador de porcentaje
        let startTime = Date.now();
        
        const updateProgress = function() {
            const elapsedTime = Date.now() - startTime;
            let progress = Math.min((elapsedTime / totalLoadTime) * 100, 100);
            
            // Destacar contador al acercarse al final
            if (progress >= 80) {
                counter.classList.add('almost-done');
            }
            
            // Actualizar texto del contador
            counter.textContent = Math.round(progress) + '%';
            
            // Continuar actualizando si no hemos llegado al 100%
            if (progress < 100) {
                requestAnimationFrame(updateProgress);
            } else {
                // Al llegar al 100% mantener el mensaje final
                document.querySelector('.loader-text').textContent = '¡Bienvenido a MayoMotors!';
                clearInterval(cambiarMensaje);
            }
        };
        
        // Iniciar actualización de progreso
        requestAnimationFrame(updateProgress);
        
        // Ocultar el loader exactamente después de 5 segundos
        setTimeout(function() {
            // Asegurar que el contador muestra 100%
            counter.textContent = '100%';
            document.querySelector('.loader-text').textContent = '¡Bienvenido a MayoMotors!';
            
            // Mostrar el contenido principal
            if (wrapper) {
                wrapper.style.visibility = 'visible';
                wrapper.style.opacity = '1';
            }
            
            // Esperar un momento con el 100% visible
            setTimeout(function() {
                loader.classList.remove('extended-loading');
                loader.classList.add('hide-loader');
                
                // Remover el loader después de la transición
                setTimeout(function() {
                    if (loader.parentNode) {
                        loader.remove();
                    }
                }, 500);
            }, 1000);
        }, totalLoadTime);
    }
});
</script>

<?php
mostrarFooter();
?>