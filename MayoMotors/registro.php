<?php
require_once 'layout.php';

// Redirigir si ya está logueado
if (estaLogueado()) {
    redirigir('index.php');
}

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = limpiarInput($_POST['nombre']);
    $apellidos = limpiarInput($_POST['apellidos']);
    $telefono = limpiarInput($_POST['telefono']);
    $correo = limpiarInput($_POST['correo']);
    $contrasena = $_POST['contrasena'];
    $repetir_contrasena = $_POST['repetir_contrasena'];
    
    // Validaciones
    $errores = [];
    
    // Validar el nombre - solo permitir letras y espacios
    if (empty($nombre)) {
        $errores[] = 'El nombre es obligatorio';
    } elseif (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $nombre)) {
        $errores[] = 'El nombre solo debe contener letras y espacios';
    }
    
    // Validar los apellidos - solo permitir letras y espacios
    if (!empty($apellidos) && !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $apellidos)) {
        $errores[] = 'Los apellidos solo deben contener letras y espacios';
    }
    
    // Validar el teléfono
    if (empty($telefono) || !preg_match('/^[0-9]{9}$/', $telefono)) {
        $errores[] = 'El teléfono debe tener 9 dígitos';
    }
    
    // Validar el correo
    if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El correo no es válido';
    } else {
        // Verificar que el correo no exista
        $sql = "SELECT id_usuario FROM Usuarios WHERE correo = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errores[] = 'Este correo ya está registrado';
        }
    }
    
    // Validar la contraseña
    if (empty($contrasena) || strlen($contrasena) < 6) {
        $errores[] = 'La contraseña debe tener al menos 6 caracteres';
    }
    
    // Validar coincidencia de contraseñas
    if ($contrasena !== $repetir_contrasena) {
        $errores[] = 'Las contraseñas no coinciden';
    }
    
    // Si no hay errores, registrar el usuario
    if (empty($errores)) {
        $id = generarID('Usuarios');
        $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO Usuarios (id_usuario, nombre, apellidos, telefono, contrasena, correo, administrador) 
                VALUES (?, ?, ?, ?, ?, ?, FALSE)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $id, $nombre, $apellidos, $telefono, $contrasena_hash, $correo);
        
        if ($stmt->execute()) {
            mostrarAlerta('Te has registrado correctamente. Ya puedes iniciar sesión.');
            redirigir('login.php');
        } else {
            mostrarAlerta('Error al registrarse: ' . $conn->error, 'danger');
        }
    } else {
        // Mostrar los errores
        $mensaje_error = implode('<br>', $errores);
        mostrarAlerta($mensaje_error, 'danger');
    }
}

mostrarHeader('Registro');
?>

<h1 class="title">Formulario de Registro</h1>

<div class="form-container">
    <div class="form-border"></div>
    <h2 class="subtitle">Registro</h2>
    
    <form action="registro.php" method="post" id="form-registro">
        <div class="form-group">
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" class="form-control" required pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+" title="Solo se permiten letras y espacios">
        </div>
        
        <div class="form-group">
            <label for="apellidos">Apellidos</label>
            <input type="text" id="apellidos" name="apellidos" class="form-control" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+" title="Solo se permiten letras y espacios">
        </div>
        
        <div class="form-group">
            <label for="telefono">Teléfono</label>
            <input type="tel" id="telefono" name="telefono" class="form-control" pattern="[0-9]{9}" maxlength="9" inputmode="numeric" required title="Debe contener exactamente 9 dígitos numéricos">
            <small class="text-muted">Formato: 9 dígitos numéricos</small>
        </div>
        
        <div class="form-group">
            <label for="correo">Correo</label>
            <input type="email" id="correo" name="correo" class="form-control" required pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$" title="Introduce un email válido (ejemplo: usuario@dominio.com)">
            <small class="text-muted">Formato: usuario@dominio.com</small>
        </div>
        
        <div class="form-group">
            <label for="contrasena">Contraseña</label>
            <input type="password" id="contrasena" name="contrasena" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="repetir_contrasena">Repetir contraseña</label>
            <input type="password" id="repetir_contrasena" name="repetir_contrasena" class="form-control" required>
        </div>
        
        <div class="btn-container">
            <button type="submit" class="btn btn-primary">Registro</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Formulario de registro
    const formRegistro = document.getElementById('form-registro');
    const nombreInput = document.getElementById('nombre');
    const apellidosInput = document.getElementById('apellidos');
    
    // Referencias a todos los campos
    const telefonoInput = document.getElementById('telefono');
    const correoInput = document.getElementById('correo');
    const contrasenaInput = document.getElementById('contrasena');
    const repetirContrasenaInput = document.getElementById('repetir_contrasena');
    
    // Validación adicional del lado del cliente
    formRegistro.addEventListener('submit', function(event) {
        let hasErrors = false;
        
        // Validar nombre (solo letras y espacios)
        const nombrePattern = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
        if (!nombrePattern.test(nombreInput.value)) {
            mostrarError(nombreInput, 'El nombre solo debe contener letras y espacios');
            hasErrors = true;
        } else {
            limpiarError(nombreInput);
        }
        
        // Validar apellidos si no está vacío (solo letras y espacios)
        if (apellidosInput.value.trim() !== '') {
            const apellidosPattern = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
            if (!apellidosPattern.test(apellidosInput.value)) {
                mostrarError(apellidosInput, 'Los apellidos solo deben contener letras y espacios');
                hasErrors = true;
            } else {
                limpiarError(apellidosInput);
            }
        }
        
        // Validar teléfono
        const telefonoPattern = /^[0-9]{9}$/;
        if (!telefonoPattern.test(telefonoInput.value)) {
            mostrarError(telefonoInput, 'El teléfono debe contener exactamente 9 dígitos numéricos');
            hasErrors = true;
        } else {
            limpiarError(telefonoInput);
        }
        
        // Validar correo
        const correoPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (!correoPattern.test(correoInput.value)) {
            mostrarError(correoInput, 'Introduce un email válido (ejemplo: usuario@dominio.com)');
            hasErrors = true;
        } else {
            limpiarError(correoInput);
        }
        
        // Validar que las contraseñas coincidan
        if (contrasenaInput.value !== repetirContrasenaInput.value) {
            mostrarError(repetirContrasenaInput, 'Las contraseñas no coinciden');
            hasErrors = true;
        } else {
            limpiarError(repetirContrasenaInput);
        }
        
        // Validar longitud mínima de contraseña
        if (contrasenaInput.value.length < 6) {
            mostrarError(contrasenaInput, 'La contraseña debe tener al menos 6 caracteres');
            hasErrors = true;
        } else {
            limpiarError(contrasenaInput);
        }
        
        if (hasErrors) {
            event.preventDefault();
        }
    });
    
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
    
    // Validación en tiempo real para nombre
    nombreInput.addEventListener('input', function() {
        const nombrePattern = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
        if (this.value.trim() !== '' && !nombrePattern.test(this.value)) {
            mostrarError(this, 'El nombre solo debe contener letras y espacios');
        } else {
            limpiarError(this);
        }
    });
    
    // Validación en tiempo real para apellidos
    apellidosInput.addEventListener('input', function() {
        const apellidosPattern = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
        if (this.value.trim() !== '' && !apellidosPattern.test(this.value)) {
            mostrarError(this, 'Los apellidos solo deben contener letras y espacios');
        } else {
            limpiarError(this);
        }
    });
    
    // Validación en tiempo real para teléfono
    telefonoInput.addEventListener('input', function() {
        // Eliminar cualquier carácter que no sea un número
        this.value = this.value.replace(/[^0-9]/g, '');
        
        const telefonoPattern = /^[0-9]{9}$/;
        if (this.value.trim() !== '' && !telefonoPattern.test(this.value)) {
            if (this.value.length !== 9) {
                mostrarError(this, 'El teléfono debe tener exactamente 9 dígitos');
            } else {
                mostrarError(this, 'El teléfono solo debe contener números');
            }
        } else {
            limpiarError(this);
        }
    });
    
    // Validación en tiempo real para correo
    correoInput.addEventListener('input', function() {
        const correoPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (this.value.trim() !== '' && !correoPattern.test(this.value)) {
            mostrarError(this, 'Introduce un email válido (ejemplo: usuario@dominio.com)');
        } else {
            limpiarError(this);
        }
    });
    
    // Validación en tiempo real para contraseña
    contrasenaInput.addEventListener('input', function() {
        if (this.value.trim() !== '' && this.value.length < 6) {
            mostrarError(this, 'La contraseña debe tener al menos 6 caracteres');
        } else {
            limpiarError(this);
            
            // Si ya hay un valor en repetir contraseña, validar que coincidan
            if (repetirContrasenaInput.value.trim() !== '') {
                if (this.value !== repetirContrasenaInput.value) {
                    mostrarError(repetirContrasenaInput, 'Las contraseñas no coinciden');
                } else {
                    limpiarError(repetirContrasenaInput);
                }
            }
        }
    });
    
    // Validación en tiempo real para repetir contraseña
    repetirContrasenaInput.addEventListener('input', function() {
        if (this.value.trim() !== '' && this.value !== contrasenaInput.value) {
            mostrarError(this, 'Las contraseñas no coinciden');
        } else {
            limpiarError(this);
        }
    });
});
</script>

<?php
mostrarFooter();
?>