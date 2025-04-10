<?php
require_once 'config.php';

// Función para mostrar el encabezado
function mostrarHeader($titulo) {
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo; ?> - MayoMotors</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div id="wrapper">
        <header class="header">
            <div>
                <a href="index.php">
                    <img src="css/logo.png" alt="MayoMotors" class="logo">
                </a>
            </div>
            <nav class="nav">
                <a href="index.php">Inicio</a>
                <a href="coches.php">Lista de coches</a>
                <a href="contacto.php">Contacto</a>
            </nav>
            <div class="user-section">
                <?php if (estaLogueado()): ?>
                    <div class="dropdown">
                        <div style="display: flex; align-items: center;">
                            <img src="css/sesion.png" alt="Usuario" class="user-icon" style="margin-right: 8px;">
                            <div class="user-info">
                                <span><?php echo $_SESSION['nombre_usuario']; ?></span>
                                <a href="logout.php">Cerrar Sesión</a>
                            </div>
                        </div>
                        <div class="dropdown-content">
                            <?php if (esAdmin()): ?>
                                <a href="favoritos.php">Favoritos</a>
                                <a href="vender.php">Vender</a>
                                <a href="admin_usuarios.php">Usuarios</a>
                            <?php else: ?>
                                <a href="favoritos.php">Favoritos</a>
                                <a href="vender.php">Vender</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php">Login</a>
                <?php endif; ?>
            </div>
        </header>
        
        <div class="container">
            <main class="main">
                <?php
                // Mostrar alertas si existen
                if (isset($_SESSION['alerta'])) {
                    echo '<div class="alert alert-' . $_SESSION['alerta']['tipo'] . '">';
                    echo $_SESSION['alerta']['mensaje'];
                    echo '</div>';
                    unset($_SESSION['alerta']);
                }
                ?>
<?php
}

// Función para mostrar el pie de página
function mostrarFooter() {
?>
            </main>
        </div>
        
        <footer class="footer">
            2025 © MayoMotors
        </footer>
    </div>
    
    <script src="js/scripts.js"></script>
</body>
</html>
<?php
}
?>