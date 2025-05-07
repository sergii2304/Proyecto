<?php
require_once 'layout.php';

// Redirigir si ya está logueado
if (estaLogueado()) {
    redirigir('index.php');
}

// Verificar si se está enviando un token para restablecer la contraseña
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];
    
    // Procesar el formulario de restablecimiento de contraseña
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nueva_contrasena'])) {
        $nueva_contrasena = $_POST['nueva_contrasena'];
        $confirmar_contrasena = $_POST['confirmar_contrasena'];
        
        // Validar la contraseña
        if (empty($nueva_contrasena) || strlen($nueva_contrasena) < 6) {
            mostrarAlerta('La contraseña debe tener al menos 6 caracteres', 'danger');
        } elseif ($nueva_contrasena !== $confirmar_contrasena) {
            mostrarAlerta('Las contraseñas no coinciden', 'danger');
        } else {
            // Verificar el token
            $sql = "SELECT id_usuario FROM ResetPassword WHERE token = ? AND expira > NOW() AND usado = 0";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $row = $result->fetch_assoc();
                $usuario_id = $row['id_usuario'];
                
                // Actualizar la contraseña
                $contrasena_hash = password_hash($nueva_contrasena, PASSWORD_DEFAULT);
                $update_sql = "UPDATE Usuarios SET contrasena = ? WHERE id_usuario = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("ss", $contrasena_hash, $usuario_id);
                
                if ($update_stmt->execute()) {
                    // Marcar el token como usado
                    $usado_sql = "UPDATE ResetPassword SET usado = 1 WHERE token = ?";
                    $usado_stmt = $conn->prepare($usado_sql);
                    $usado_stmt->bind_param("s", $token);
                    $usado_stmt->execute();
                    
                    mostrarAlerta('Tu contraseña ha sido actualizada correctamente');
                    redirigir('login.php');
                } else {
                    mostrarAlerta('Error al actualizar la contraseña', 'danger');
                }
            } else {
                mostrarAlerta('El enlace de restablecimiento no es válido o ha expirado', 'danger');
            }
        }
    }
    
    // Mostrar el formulario para establecer la nueva contraseña
    mostrarHeader('Establecer nueva contraseña');
    ?>

    <h1 class="title">Establecer nueva contraseña</h1>

    <div class="form-container">
        <div class="form-border"></div>
        
        <form action="recuperar_contrasena.php?token=<?php echo htmlspecialchars($token); ?>" method="post">
            <div class="form-group">
                <label for="nueva_contrasena">Nueva contraseña</label>
                <input type="password" id="nueva_contrasena" name="nueva_contrasena" class="form-control" required>
                <small class="text-muted">La contraseña debe tener al menos 6 caracteres</small>
            </div>
            
            <div class="form-group">
                <label for="confirmar_contrasena">Confirmar contraseña</label>
                <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" class="form-control" required>
            </div>
            
            <div class="btn-container">
                <button type="submit" class="btn btn-primary">Cambiar contraseña</button>
            </div>
        </form>
    </div>

    <?php
    mostrarFooter();
    exit;
}

// Procesar el formulario de solicitud de recuperación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = limpiarInput($_POST['correo']);
    
    // Validar el correo
    if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        mostrarAlerta('Por favor, introduce un correo electrónico válido', 'danger');
    } else {
        // Verificar si el correo existe en la base de datos
        $sql = "SELECT id_usuario, nombre FROM Usuarios WHERE correo = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $usuario = $result->fetch_assoc();
            $usuario_id = $usuario['id_usuario'];
            
            // Generar un token único
            $token = bin2hex(random_bytes(32));
            
            // Establecer la fecha de expiración (24 horas)
            $expira = date('Y-m-d H:i:s', strtotime('+24 hours'));
            
            // Verificar si la tabla ResetPassword existe, si no, crearla
            $check_table = $conn->query("SHOW TABLES LIKE 'ResetPassword'");
            if ($check_table->num_rows == 0) {
                $create_table = "CREATE TABLE ResetPassword (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    id_usuario VARCHAR(50) NOT NULL,
                    token VARCHAR(100) NOT NULL,
                    expira DATETIME NOT NULL,
                    usado TINYINT(1) NOT NULL DEFAULT 0,
                    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
                $conn->query($create_table);
            }
            
            // Guardar el token en la base de datos
            $insert_sql = "INSERT INTO ResetPassword (id_usuario, token, expira) VALUES (?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("sss", $usuario_id, $token, $expira);
            $insert_stmt->execute();
            
            // Crear el enlace de restablecimiento
            $reset_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/recuperar_contrasena.php?token=" . $token;
            
            // Mostrar el enlace directo al usuario
            mostrarHeader('Recuperar contraseña');
            ?>

            <h1 class="title">Instrucciones para recuperar tu contraseña</h1>

            <div class="form-container">
                <div class="form-border"></div>
                
                <div style="text-align: center; margin-bottom: 20px;">
                    <p>Hemos generado un enlace para que puedas restablecer tu contraseña.</p>
                    <p><strong>Por favor, haz clic en el siguiente enlace o cópialo en tu navegador:</strong></p>
                    
                    <div style="margin: 20px 0; padding: 15px; background-color: #f8f9fa; border-radius: 5px; word-break: break-all;">
                        <a href="<?php echo $reset_url; ?>" target="_blank"><?php echo $reset_url; ?></a>
                    </div>
                    
                    <p>Este enlace expirará en 24 horas.</p>
                    <p>Si no solicitaste cambiar tu contraseña, puedes ignorar este mensaje.</p>
                </div>
                
                <div style="text-align: center;">
                    <a href="login.php" class="btn btn-primary">Volver al inicio de sesión</a>
                </div>
            </div>

            <?php
            mostrarFooter();
            exit;
        } else {
            mostrarAlerta('No existe ningún usuario con ese correo', 'danger');
        }
    }
}

mostrarHeader('Recuperar contraseña');
?>

<h1 class="title">Recuperar contraseña</h1>

<div class="form-container">
    <div class="form-border"></div>
    
    <p style="margin-bottom: 20px; text-align: center;">
        Introduce tu dirección de correo electrónico y generaremos un enlace para que puedas restablecer tu contraseña.
    </p>
    
    <form action="recuperar_contrasena.php" method="post">
        <div class="form-group">
            <label for="correo">Correo electrónico</label>
            <input type="email" id="correo" name="correo" class="form-control" required>
        </div>
        
        <div class="btn-container">
        <button type="submit" class="btn btn-primary">Enviar instrucciones</button>
        </div>
    </form>
    
    <div class="form-link">
        <p><a href="login.php">Volver al inicio de sesión</a></p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validación del formulario
    const formRecuperar = document.querySelector('form');
    
    if (formRecuperar) {
        formRecuperar.addEventListener('submit', function(e) {
            let isValid = true;
            
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
            
            // Si estamos en el formulario de nueva contraseña
            const nuevaContrasena = document.getElementById('nueva_contrasena');
            const confirmarContrasena = document.getElementById('confirmar_contrasena');
            
            if (nuevaContrasena && confirmarContrasena) {
                if (!nuevaContrasena.value.trim()) {
                    mostrarError(nuevaContrasena, 'La contraseña es obligatoria');
                    isValid = false;
                } else if (nuevaContrasena.value.length < 6) {
                    mostrarError(nuevaContrasena, 'La contraseña debe tener al menos 6 caracteres');
                    isValid = false;
                } else {
                    limpiarError(nuevaContrasena);
                }
                
                if (!confirmarContrasena.value.trim()) {
                    mostrarError(confirmarContrasena, 'Debes confirmar la contraseña');
                    isValid = false;
                } else if (confirmarContrasena.value !== nuevaContrasena.value) {
                    mostrarError(confirmarContrasena, 'Las contraseñas no coinciden');
                    isValid = false;
                } else {
                    limpiarError(confirmarContrasena);
                }
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

<?php
mostrarFooter();
?>