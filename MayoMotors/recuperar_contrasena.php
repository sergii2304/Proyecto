<?php
require_once 'layout.php';

// Redirigir si ya está logueado
if (estaLogueado()) {
    redirigir('index.php');
}

// Procesar el formulario
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
            
            mostrarAlerta('Se ha enviado un correo con instrucciones para restablecer tu contraseña. Por favor, revisa tu bandeja de entrada.');
            
            // Redirigir al login después de mostrar el mensaje
            redirigir('login.php');
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
        Introduce tu dirección de correo electrónico y te enviaremos instrucciones para restablecer tu contraseña.
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

<?php
mostrarFooter();
?>