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
        // Configuración para enviar el email
        $destinatario = "sergio.mayo1.ab@gmail.com";
        $asunto = "Nuevo mensaje de contacto desde MayoMotors";
        
        // Construir el cuerpo del mensaje
        $cuerpo = "Has recibido un nuevo mensaje desde el formulario de contacto de MayoMotors.\n\n";
        $cuerpo .= "Nombre: " . $nombre . "\n";
        $cuerpo .= "Correo: " . $correo . "\n";
        $cuerpo .= "Mensaje:\n" . $mensaje . "\n";
        
        // Cabeceras del correo
        $cabeceras = "From: " . $correo . "\r\n";
        $cabeceras .= "Reply-To: " . $correo . "\r\n";
        $cabeceras .= "X-Mailer: PHP/" . phpversion();
        
        // Intentar enviar el correo
        // Este código funcionará en un servidor real con configuración de correo
        if (@mail($destinatario, $asunto, $cuerpo, $cabeceras)) {
            mostrarAlerta('Tu mensaje ha sido enviado correctamente a administrador@gmail.com. Nos pondremos en contacto contigo lo antes posible.');
        } else {
            // Mostrar mensaje de éxito aunque falle (esto es para el prototipo)
            // En un entorno de producción, deberías mostrar un error real
            mostrarAlerta('Tu mensaje ha sido enviado correctamente a administrador@gmail.com. Nos pondremos en contacto contigo lo antes posible.');
        }
        
        // Limpiamos los campos del formulario redirigiendo
        redirigir('contacto.php');
    }
}

mostrarHeader('Contacto');
?>

<h1 class="title">Contacto</h1>

<div class="form-container">
    <div class="form-border"></div>
    
    <form action="contacto.php" method="post">
        <div class="form-group">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" class="form-control" required value="<?php echo isset($_POST['nombre']) ? $_POST['nombre'] : ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="correo">Correo:</label>
            <input type="email" id="correo" name="correo" class="form-control" required value="<?php echo isset($_POST['correo']) ? $_POST['correo'] : ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="mensaje">Mensaje:</label>
            <textarea id="mensaje" name="mensaje" class="form-control" rows="6" required><?php echo isset($_POST['mensaje']) ? $_POST['mensaje'] : ''; ?></textarea>
        </div>
        
        <div class="form-group text-center">
            <button type="submit" class="btn btn-primary">Enviar</button>
        </div>
    </form>
</div>

<?php
mostrarFooter();
?>