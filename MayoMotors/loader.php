<?php

// Verificar si estamos en la página principal
$es_pagina_principal = isset($es_pagina_principal) && $es_pagina_principal === true;
?>

<div id="loader-wrapper" class="<?php echo $es_pagina_principal ? 'home-loader' : ''; ?>">
    <div class="loader-content">
        <img src="css/logo.png" alt="MayoMotors" class="loader-logo">
        <div class="loader-bar-container">
            <div class="loader-bar"></div>
        </div>
        <p class="loader-text">Cargando MayoMotors...</p>
        <p class="loader-counter">0%</p>
    </div>
</div>

<style>
#loader-wrapper {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: #38B6FF;
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    transition: opacity 0.5s ease, visibility 0.5s ease;
}

.loader-content {
    text-align: center;
    background-color: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
    max-width: 90%;
    width: 400px;
}

.loader-logo {
    width: 200px;
    height: auto;
    margin-bottom: 20px;
    animation: pulse 2s infinite ease-in-out;
}

.loader-bar-container {
    width: 100%;
    height: 10px;
    background-color: #CFE0EE;
    border-radius: 5px;
    overflow: hidden;
    margin: 20px 0;
}

.loader-bar {
    height: 100%;
    width: 30%;
    background-color: #004ADD;
    border-radius: 5px;
    animation: loading 1.5s infinite ease-in-out;
    position: relative;
}

.loader-text {
    font-size: 18px;
    color: #004ADD;
    font-weight: bold;
    margin: 0 0 10px 0;
}

.loader-counter {
    font-size: 16px;
    color: #38B6FF;
    font-weight: bold;
    margin: 5px 0 0 0;
}

@keyframes loading {
    0% {
        left: -30%;
    }
    50% {
        left: 100%;
    }
    100% {
        left: -30%;
    }
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}

.hide-loader {
    opacity: 0;
    visibility: hidden;
}

/* Clase especial para la página de inicio */
.home-loader.extended-loading {
    opacity: 1 !important;
    visibility: visible !important;
    /* Asegurar que el loader permanezca por encima de todo */
    z-index: 99999 !important;
    /* Asegurar que el loader ocupa toda la pantalla */
    width: 100vw !important;
    height: 100vh !important;
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
}

/* Estilos para la página de inicio */
.home-loader .loader-content {
    animation: glow 3s infinite alternate;
    border: 2px solid #38B6FF;
}

@keyframes glow {
    0% {
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
    }
    100% {
        box-shadow: 0 0 20px rgba(0, 74, 221, 0.6);
    }
}

.home-loader .loader-counter.almost-done {
    font-size: 20px;
    color: #004ADD;
    text-shadow: 0 0 5px rgba(0, 74, 221, 0.3);
}
</style>

<script>
// Función para ocultar el loader después de que la página esté completamente cargada
document.addEventListener('DOMContentLoaded', function() {
    const loader = document.getElementById('loader-wrapper');
    const counter = document.querySelector('.loader-counter');
    
    // Si estamos en la página principal, no hacemos nada aquí
    // ya que el script específico de index.php se encargará de la carga
    if (loader && counter && !loader.classList.contains('home-loader')) {
        let progress = 0;
        const loadingTime = 5000; // 5 segundos para páginas normales
        const interval = 50; // Actualización cada 50ms
        const increment = (interval / loadingTime) * 100;
        
        const progressInterval = setInterval(function() {
            progress += increment;
            
            if (progress >= 100) {
                progress = 100;
                clearInterval(progressInterval);
                counter.textContent = '100%';
                
                // Cuando llegue al 100%, esperamos un poco y luego ocultamos el loader
                setTimeout(function() {
                    loader.classList.add('hide-loader');
                    
                    // Después de que la transición termine, eliminamos el loader del DOM
                    setTimeout(function() {
                        loader.remove();
                    }, 500);
                }, 300); // Pequeña pausa en el 100%
            } else {
                counter.textContent = Math.round(progress) + '%';
            }
        }, interval);
    }
});

// Agregar un evento para precarga de recursos
window.addEventListener('load', function() {
    // Este evento se dispara cuando todos los recursos (imágenes, scripts, etc.) han sido cargados
    const loader = document.getElementById('loader-wrapper');
    
    if (loader && document.readyState === 'complete' && 
        !loader.classList.contains('home-loader') && 
        !loader.classList.contains('hide-loader')) {
        
        setTimeout(function() {
            loader.classList.add('hide-loader');
            setTimeout(function() {
                loader.remove();
            }, 500);
        }, 500);
    }
});
</script>