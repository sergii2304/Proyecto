<?php
require_once 'layout.php';

// Procesar formulario de contacto
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = limpiarInput($_POST['nombre']);
    $correo = limpiarInput($_POST['correo']);
    $mensaje = limpiarInput($_POST['mensaje']);
    
    // Validación básica
    if (empty($nombre) || empty($correo) || empty($mensaje)) {
        mostrarAlerta('Todos los campos son obligatorios', 'danger');
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        mostrarAlerta('El correo electrónico no es válido', 'danger');
    } else {
        // Guardar el mensaje en la base de datos
        try {
            
            // Insertar mensaje en la base de datos
            $id_contacto = generarID('Contactos');
            $fecha = date('Y-m-d H:i:s');
            
            $sql = "INSERT INTO Contactos (id_contacto, nombre, correo, mensaje, fecha) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $id_contacto, $nombre, $correo, $mensaje, $fecha);
            
            if ($stmt->execute()) {
                // Guardar también en un archivo de texto como respaldo
                $archivo_mensajes = 'mensajes_contacto.txt';
                $contenido = "-----------------------------------\n";
                $contenido .= "Fecha: " . $fecha . "\n";
                $contenido .= "ID: " . $id_contacto . "\n";
                $contenido .= "Nombre: " . $nombre . "\n";
                $contenido .= "Correo: " . $correo . "\n";
                $contenido .= "Mensaje:\n" . $mensaje . "\n";
                $contenido .= "-----------------------------------\n\n";
                
                file_put_contents($archivo_mensajes, $contenido, FILE_APPEND);
                
                mostrarAlerta('Tu mensaje ha sido enviado correctamente. Nos pondremos en contacto contigo lo antes posible.');
                
                // Si estamos en un servidor con mail() configurado, intentar enviar notificación
                $destinatario = "sergio.mayo1.ab@gmail.com";
                $asunto = "Nuevo mensaje de contacto en MayoMotors";
                $cuerpo = "Tienes un nuevo mensaje de contacto en MayoMotors:\n\n";
                $cuerpo .= "De: " . $nombre . " (" . $correo . ")\n";
                $cuerpo .= "Fecha: " . $fecha . "\n\n";
                $cuerpo .= "Mensaje:\n" . $mensaje . "\n\n";
                $cuerpo .= "Puedes ver todos los mensajes ingresando al panel de administración.";
                
                $cabeceras = "From: webmaster@mayomotors.com\r\n";
                $cabeceras .= "Reply-To: " . $correo . "\r\n";
                
                // Intentar enviar pero no hacer depender el éxito de esto
                @mail($destinatario, $asunto, $cuerpo, $cabeceras);
                
                // Redirigir para evitar reenvío del formulario
                redirigir('contacto.php');
            } else {
                mostrarAlerta('Ha ocurrido un error al enviar el mensaje. Por favor, intenta nuevamente.', 'danger');
            }
        } catch (Exception $e) {
            // En caso de error, guardar en un archivo de respaldo
            $error_log = 'error_contacto.txt';
            $error_content = date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n";
            $error_content .= "Mensaje de: " . $nombre . " (" . $correo . ")\n";
            $error_content .= "Contenido: " . $mensaje . "\n\n";
            
            file_put_contents($error_log, $error_content, FILE_APPEND);
            
            mostrarAlerta('Tu mensaje ha sido recibido. Nos pondremos en contacto contigo lo antes posible.');
            redirigir('contacto.php');
        }
    }
}

// Código para el panel de administración (visible solo para administradores)
$mostrar_mensajes = false;
if (estaLogueado() && esAdmin() && isset($_GET['ver_mensajes'])) {
    $mostrar_mensajes = true;
    
    // Marcar como leído si se solicita
    if (isset($_GET['marcar_leido']) && !empty($_GET['marcar_leido'])) {
        $id_contacto = $_GET['marcar_leido'];
        $sql = "UPDATE Contactos SET leido = 1 WHERE id_contacto = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $id_contacto);
        $stmt->execute();
    }
    
    // Obtener mensajes de contacto
    $sql = "SELECT * FROM Contactos ORDER BY fecha DESC";
    $result = $conn->query($sql);
}

mostrarHeader('Contacto');
?>

<?php if ($mostrar_mensajes && estaLogueado() && esAdmin()): ?>
    <h1 class="title">Mensajes de Contacto</h1>
    
    <div style="margin-bottom: 20px;">
        <a href="contacto.php" class="btn btn-primary">Volver al formulario</a>
    </div>
    
    <?php if ($result && $result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Mensaje</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($mensaje = $result->fetch_assoc()): ?>
                        <tr <?php echo $mensaje['leido'] ? '' : 'style="font-weight: bold; background-color: #f0f8ff;"'; ?>>
                            <td><?php echo date('d/m/Y H:i', strtotime($mensaje['fecha'])); ?></td>
                            <td><?php echo htmlspecialchars($mensaje['nombre']); ?></td>
                            <td>
                                <a href="mailto:<?php echo htmlspecialchars($mensaje['correo']); ?>">
                                    <?php echo htmlspecialchars($mensaje['correo']); ?>
                                </a>
                            </td>
                            <td>
                                <?php 
                                // Mostrar una vista previa del mensaje
                                $preview = htmlspecialchars(substr($mensaje['mensaje'], 0, 100));
                                echo $preview . (strlen($mensaje['mensaje']) > 100 ? '...' : '');
                                ?>
                                <button class="btn btn-sm btn-info ver-mensaje" 
                                        data-mensaje="<?php echo htmlspecialchars($mensaje['mensaje']); ?>"
                                        data-nombre="<?php echo htmlspecialchars($mensaje['nombre']); ?>"
                                        data-correo="<?php echo htmlspecialchars($mensaje['correo']); ?>"
                                        data-fecha="<?php echo date('d/m/Y H:i', strtotime($mensaje['fecha'])); ?>">
                                    Ver completo
                                </button>
                            </td>
                            <td>
                                <?php if ($mensaje['leido']): ?>
                                    <span class="badge bg-success">Leído</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">No leído</span>
                                <?php endif; ?>
                            </td>
                            <td class="action-buttons">
                                <div class="btn-action-container">
                                    <?php if (!$mensaje['leido']): ?>
                                        <a href="contacto.php?ver_mensajes=1&marcar_leido=<?php echo $mensaje['id_contacto']; ?>" 
                                           class="btn btn-sm btn-primary action-btn">
                                            Leído
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="mailto:<?php echo htmlspecialchars($mensaje['correo']); ?>?subject=Re: Consulta en MayoMotors" 
                                       class="btn btn-sm btn-success action-btn">
                                        Responder
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Modal para ver mensaje completo -->
        <div id="mensajeModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h3 id="modal-titulo">Mensaje de <span id="modal-nombre"></span></h3>
                <p><strong>Fecha:</strong> <span id="modal-fecha"></span></p>
                <p><strong>Email:</strong> <span id="modal-correo"></span></p>
                <div class="mensaje-contenido">
                    <strong>Mensaje:</strong>
                    <p id="modal-mensaje-texto"></p>
                </div>
                <div class="modal-buttons">
                    <button id="modal-responder" class="btn btn-primary">Responder</button>
                    <button class="btn btn-secondary cerrar-modal">Cerrar</button>
                </div>
            </div>
        </div>
        
        <style>
            .modal {
                display: none;
                position: fixed;
                z-index: 1000;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0,0,0,0.4);
            }
            
            .modal-content {
                background-color: #fefefe;
                margin: 10% auto;
                padding: 20px;
                border: 1px solid #888;
                width: 80%;
                max-width: 600px;
                border-radius: 5px;
                box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            }

            .close {
                color: #aaa;
                float: right;
                font-size: 28px;
                font-weight: bold;
                cursor: pointer;
            }
            
            .close:hover {
                color: black;
            }
            
            .mensaje-contenido {
                margin: 15px 0;
                padding: 10px;
                background-color: #f9f9f9;
                border: 1px solid #ddd;
                border-radius: 5px;
                max-height: 300px;
                overflow-y: auto;
            }
            
            .modal-buttons {
                display: flex;
                justify-content: flex-end;
                gap: 10px;
                margin-top: 15px;
            }
            
            .badge {
                padding: 5px 10px;
                border-radius: 4px;
                color: white;
            }

            .ver-mensaje {
                background-color: #38B6FF;
            }
            .ver-mensaje:hover {
                background-color: #004ADD;
                color: white;
            }

            .bg-success {
                background-color: #28a745;
            }

            .bg-warning {
                background-color: #ffc107;
                color: #212529;
            }
            
            /* Estilos para los botones de acción */
            .btn-action-container {
                display: flex;
                flex-direction: column;
                gap: 5px;
                justify-content: center;
            }
            
            .action-btn {
                width: 110px;
                text-align: center;
                display: inline-block;
            }
        </style>
        
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const modal = document.getElementById('mensajeModal');
                const modalNombre = document.getElementById('modal-nombre');
                const modalCorreo = document.getElementById('modal-correo');
                const modalFecha = document.getElementById('modal-fecha');
                const modalMensajeTexto = document.getElementById('modal-mensaje-texto');
                const modalResponder = document.getElementById('modal-responder');
                
                // Abrir modal al hacer clic en "Ver completo"
                document.querySelectorAll('.ver-mensaje').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        const mensaje = this.getAttribute('data-mensaje');
                        const nombre = this.getAttribute('data-nombre');
                        const correo = this.getAttribute('data-correo');
                        const fecha = this.getAttribute('data-fecha');
                        
                        modalNombre.textContent = nombre;
                        modalCorreo.textContent = correo;
                        modalFecha.textContent = fecha;
                        modalMensajeTexto.textContent = mensaje;
                        
                        modalResponder.onclick = function() {
                            window.location.href = 'mailto:' + correo + '?subject=Re: Consulta en MayoMotors';
                        };
                        
                        modal.style.display = 'block';
                    });
                });
                
                // Cerrar modal con la X
                document.querySelector('.close').addEventListener('click', function() {
                    modal.style.display = 'none';
                });
                
                // Cerrar modal con el botón Cerrar
                document.querySelector('.cerrar-modal').addEventListener('click', function() {
                    modal.style.display = 'none';
                });
                
                // Cerrar modal haciendo clic fuera del contenido
                window.addEventListener('click', function(event) {
                    if (event.target === modal) {
                        modal.style.display = 'none';
                    }
                });
            });
        </script>
    <?php else: ?>
        <div class="alert alert-info">No hay mensajes de contacto.</div>
    <?php endif; ?>

<?php else: ?>
    <!-- Formulario de contacto normal -->
    <h1 class="title">Contacto</h1>
    
    <?php if (estaLogueado() && esAdmin()): ?>
        <div style="text-align: center; margin-bottom: 20px;">
            <a href="contacto.php?ver_mensajes=1" class="btn" style="background-color: #38B6FF; color: white;">Ver todos los mensajes</a>
        </div>
    <?php endif; ?>

    <div class="form-container">
        <div class="form-border"></div>
        
        <form action="contacto.php" method="post" id="form-contacto">
            <div class="form-group">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" class="form-control" required value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="correo">Correo:</label>
                <input type="email" id="correo" name="correo" class="form-control" required value="<?php echo isset($_POST['correo']) ? htmlspecialchars($_POST['correo']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="mensaje">Mensaje:</label>
                <textarea id="mensaje" name="mensaje" class="form-control" rows="6" required><?php echo isset($_POST['mensaje']) ? htmlspecialchars($_POST['mensaje']) : ''; ?></textarea>
            </div>
            
            <div class="btn-container">
                <button type="submit" class="btn btn-primary">Enviar</button>
            </div>
        </form>
        
        <div class="contacto-info" style="margin-top: 20px; text-align: center;">
            <p>También puedes contactarnos directamente:</p>
            <p><strong>Teléfono:</strong> 685 10 18 44</p>
            <p><strong>Email:</strong> <a href="mailto:sergio.mayo1.ab@gmail.com">sergio.mayo1.ab@gmail.com</a></p>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Validación del formulario
        const formContacto = document.getElementById('form-contacto');
        
        if (formContacto) {
            formContacto.addEventListener('submit', function(e) {
                let isValid = true;
                
                // Validar nombre
                const nombreInput = document.getElementById('nombre');
                if (!nombreInput.value.trim()) {
                    mostrarError(nombreInput, 'El nombre es obligatorio');
                    isValid = false;
                } else {
                    limpiarError(nombreInput);
                }
                
                // Validar correo
                const correoInput = document.getElementById('correo');
                if (!correoInput.value.trim()) {
                    mostrarError(correoInput, 'El correo es obligatorio');
                    isValid = false;
                } else if (!validarEmail(correoInput.value)) {
                    mostrarError(correoInput, 'Por favor, introduce un email válido');
                    isValid = false;
                } else {
                    limpiarError(correoInput);
                }
                
                // Validar mensaje
                const mensajeInput = document.getElementById('mensaje');
                if (!mensajeInput.value.trim()) {
                    mostrarError(mensajeInput, 'El mensaje es obligatorio');
                    isValid = false;
                } else if (mensajeInput.value.length < 10) {
                    mostrarError(mensajeInput, 'El mensaje debe tener al menos 10 caracteres');
                    isValid = false;
                } else {
                    limpiarError(mensajeInput);
                }
                
                if (!isValid) {
                    e.preventDefault();
                }
            });
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
        
        // Validar el email
        function validarEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }
    });
    </script>
<?php endif; ?>

<?php
mostrarFooter();
?>