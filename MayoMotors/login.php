<?php
require_once 'layout.php';

// Redirigir si ya está logueado
if (estaLogueado()) {
    redirigir('index.php');
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = limpiarInput($_POST['correo']);
    $contrasena = $_POST['contrasena'];
    
    // Validar correo
    if (empty($correo)) {
        mostrarAlerta('Por favor, introduce tu correo electrónico', 'danger');
    } else {
        // Buscar usuario por correo
        $sql = "SELECT * FROM Usuarios WHERE correo = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $usuario = $result->fetch_assoc();
            
            // Verificar contraseña
            if (password_verify($contrasena, $usuario['contrasena'])) {
                // Iniciar sesión
                $_SESSION['usuario_id'] = $usuario['id_usuario'];
                $_SESSION['nombre_usuario'] = $usuario['nombre'];
                $_SESSION['es_admin'] = $usuario['administrador'] == 1;
                
                mostrarAlerta('Has iniciado sesión correctamente');
                redirigir('index.php');
            } else {
                mostrarAlerta('Contraseña incorrecta', 'danger');
            }
        } else {
            mostrarAlerta('No existe ningún usuario con ese correo', 'danger');
        }
    }
}

mostrarHeader('Iniciar Sesión');
?>

<h1 class="title">Formulario de Inicio Sesión</h1>

<div class="form-container">
    <div class="form-border"></div>
    <h2 class="subtitle">Iniciar sesión</h2>
    
    <form action="login.php" method="post">
        <div class="form-group">
            <label for="correo">Correo</label>
            <input type="email" id="correo" name="correo" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="contrasena">Contraseña</label>
            <input type="password" id="contrasena" name="contrasena" class="form-control" required>
        </div>
        
        <div class="btn-container">
        <button type="submit" class="btn btn-primary">Acceso</button>
        </div>
    </form>
    
    <div class="form-link">
        <p>¿No tienes usuario? <a href="registro.php">Registrarse</a></p>
        <p>¿Olvidaste tu contraseña? <a href="recuperar_contrasena.php">Recuperar contraseña</a></p>
    </div>
</div>

<?php
mostrarFooter();
?>