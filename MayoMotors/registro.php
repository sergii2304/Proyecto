<?php
require_once 'layout.php';

// Redirigir si ya está logueado
if (estaLogueado()) {
    redirigir('index.php');
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = limpiarInput($_POST['nombre']);
    $apellidos = limpiarInput($_POST['apellidos']);
    $telefono = limpiarInput($_POST['telefono']);
    $correo = limpiarInput($_POST['correo']);
    $contrasena = $_POST['contrasena'];
    $repetir_contrasena = $_POST['repetir_contrasena'];
    
    // Validaciones
    $errores = [];
    
    // Validar nombre
    if (empty($nombre)) {
        $errores[] = 'El nombre es obligatorio';
    }
    
    // Validar teléfono
    if (empty($telefono) || !preg_match('/^[0-9]{9}$/', $telefono)) {
        $errores[] = 'El teléfono debe tener 9 dígitos';
    }
    
    // Validar correo
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
    
    // Validar contraseña
    if (empty($contrasena) || strlen($contrasena) < 6) {
        $errores[] = 'La contraseña debe tener al menos 6 caracteres';
    }
    
    // Validar coincidencia de contraseñas
    if ($contrasena !== $repetir_contrasena) {
        $errores[] = 'Las contraseñas no coinciden';
    }
    
    // Si no hay errores, registrar usuario
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
        // Mostrar errores
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
    
    <form action="registro.php" method="post">
        <div class="form-group">
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="apellidos">Apellidos</label>
            <input type="text" id="apellidos" name="apellidos" class="form-control">
        </div>
        
        <div class="form-group">
            <label for="telefono">Teléfono</label>
            <input type="tel" id="telefono" name="telefono" class="form-control" pattern="[0-9]{9}" required>
        </div>
        
        <div class="form-group">
            <label for="correo">Correo</label>
            <input type="email" id="correo" name="correo" class="form-control" required>
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

<?php
mostrarFooter();
?>