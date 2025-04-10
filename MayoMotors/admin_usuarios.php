<?php
require_once 'layout.php';

// Verificar si el usuario está logueado y es administrador
if (!estaLogueado() || !esAdmin()) {
    mostrarAlerta('No tienes permisos para acceder a esta página', 'danger');
    redirigir('index.php');
}

// Procesar eliminación de usuario
if (isset($_GET['eliminar']) && !empty($_GET['eliminar'])) {
    $usuario_id = $_GET['eliminar'];
    
    // No permitir eliminar al administrador principal
    if ($usuario_id === 'ADMIN001') {
        mostrarAlerta('No se puede eliminar al administrador principal', 'danger');
    } else {
        // Verificar si el usuario existe
        $verificar_sql = "SELECT id_usuario FROM Usuarios WHERE id_usuario = ?";
        $stmt = $conn->prepare($verificar_sql);
        $stmt->bind_param("s", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            mostrarAlerta('El usuario no existe', 'danger');
        } else {
            // Eliminar referencias en la tabla Guardar
            $eliminar_favoritos = "DELETE FROM Guardar WHERE id_usuario = ?";
            $stmt = $conn->prepare($eliminar_favoritos);
            $stmt->bind_param("s", $usuario_id);
            $stmt->execute();
            
            // Verificar si el usuario tiene coches publicados
            $verificar_coches = "SELECT id_coche FROM Coches WHERE id_usuario = ?";
            $stmt = $conn->prepare($verificar_coches);
            $stmt->bind_param("s", $usuario_id);
            $stmt->execute();
            $result_coches = $stmt->get_result();
            
            if ($result_coches->num_rows > 0) {
                // El usuario tiene coches, eliminar imágenes primero
                while ($coche = $result_coches->fetch_assoc()) {
                    $eliminar_imagenes = "DELETE FROM Imagenes WHERE id_coche = ?";
                    $stmt = $conn->prepare($eliminar_imagenes);
                    $stmt->bind_param("s", $coche['id_coche']);
                    $stmt->execute();
                    
                    // Eliminar favoritos de otros usuarios para este coche
                    $eliminar_favoritos_coche = "DELETE FROM Guardar WHERE id_coche = ?";
                    $stmt = $conn->prepare($eliminar_favoritos_coche);
                    $stmt->bind_param("s", $coche['id_coche']);
                    $stmt->execute();
                }
                
                // Ahora eliminar los coches
                $eliminar_coches = "DELETE FROM Coches WHERE id_usuario = ?";
                $stmt = $conn->prepare($eliminar_coches);
                $stmt->bind_param("s", $usuario_id);
                $stmt->execute();
            }
            
            // Finalmente eliminar el usuario
            $eliminar_usuario = "DELETE FROM Usuarios WHERE id_usuario = ?";
            $stmt = $conn->prepare($eliminar_usuario);
            $stmt->bind_param("s", $usuario_id);
            
            if ($stmt->execute()) {
                mostrarAlerta('Usuario eliminado correctamente junto con todos sus datos asociados');
            } else {
                mostrarAlerta('Error al eliminar el usuario: ' . $conn->error, 'danger');
            }
        }
    }
    
    // Redirigir para evitar reenvío del formulario
    redirigir('admin_usuarios.php');
}

// Procesar búsqueda de usuarios
$where = "";
$params = [];
$tipos = "";

if (isset($_GET['buscar']) && !empty($_GET['q'])) {
    $busqueda = '%' . limpiarInput($_GET['q']) . '%';
    $where = " WHERE nombre LIKE ? OR apellidos LIKE ? OR correo LIKE ? OR telefono LIKE ?";
    $params = [$busqueda, $busqueda, $busqueda, $busqueda];
    $tipos = "ssss";
}

// Obtener lista de usuarios
$sql = "SELECT id_usuario, nombre, apellidos, correo, telefono, administrador FROM Usuarios" . $where . " ORDER BY nombre";
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($tipos, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

mostrarHeader('Administrar Usuarios');
?>

<h1 class="title">Usuarios</h1>

<div class="container">
    <!-- Formulario de búsqueda -->
    <form action="admin_usuarios.php" method="get" class="form-search" style="margin-bottom: 20px;">
        <div style="display: flex; max-width: 600px; margin: 0 auto;">
            <input type="text" name="q" class="form-control" placeholder="Buscar por nombre, email o teléfono" value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
            <button type="submit" name="buscar" class="btn btn-primary" style="margin-left: 10px;">Buscar</button>
            <?php if (isset($_GET['buscar'])): ?>
                <a href="admin_usuarios.php" class="btn btn-danger" style="margin-left: 10px;">Limpiar</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Tabla de usuarios -->
    <table class="user-table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Teléfono</th>
                <th>Rol</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($usuario = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <?php echo $usuario['nombre'] . ' ' . $usuario['apellidos']; ?>
                        </td>
                        <td><?php echo $usuario['correo']; ?></td>
                        <td><?php echo $usuario['telefono']; ?></td>
                        <td>
                            <?php if ($usuario['administrador'] == 1): ?>
                                <span style="background-color: #0066cc; color: white; font-size: 0.8rem; padding: 2px 5px; border-radius: 3px;">
                                    Administrador
                                </span>
                            <?php else: ?>
                                <span style="background-color: #28a745; color: white; font-size: 0.8rem; padding: 2px 5px; border-radius: 3px;">
                                    Usuario
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($usuario['id_usuario'] != 'ADMIN001' && esAdmin()): ?>
                                <!-- Solo administrador puede eliminar usuarios -->
                                <a href="admin_usuarios.php?eliminar=<?php echo $usuario['id_usuario']; ?>" 
                                   class="btn btn-danger"
                                   onclick="return confirm('¿Estás seguro de que deseas eliminar este usuario? Esta acción eliminará también todos sus coches publicados y no se puede deshacer.')">
                                    Eliminar
                                </a>
                            <?php elseif ($usuario['id_usuario'] == 'ADMIN001'): ?>
                                <span class="text-muted">Administrador principal</span>
                            <?php else: ?>
                                <span class="text-muted">Sin acciones disponibles</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center;">No se encontraron usuarios</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <!-- Estadísticas -->
    <?php 
    $stats_sql = "SELECT 
                    (SELECT COUNT(*) FROM Usuarios) AS total_usuarios,
                    (SELECT COUNT(*) FROM Usuarios WHERE administrador = 1) AS total_admins,
                    (SELECT COUNT(*) FROM Coches) AS total_coches";
    $stats_result = $conn->query($stats_sql);
    $stats = $stats_result->fetch_assoc();
    ?>
    
    <div style="margin-top: 30px; background-color: #f8f9fa; padding: 15px; border-radius: 5px;">
        <h3 style="margin-bottom: 15px;">Estadísticas generales</h3>
        <div style="display: flex; justify-content: space-around; flex-wrap: wrap;">
            <div style="text-align: center; padding: 10px;">
                <div style="font-size: 2rem; font-weight: bold; color: #0066cc;"><?php echo $stats['total_usuarios']; ?></div>
                <div>Usuarios registrados</div>
            </div>
            <div style="text-align: center; padding: 10px;">
                <div style="font-size: 2rem; font-weight: bold; color: #0066cc;"><?php echo $stats['total_admins']; ?></div>
                <div>Administradores</div>
            </div>
            <div style="text-align: center; padding: 10px;">
                <div style="font-size: 2rem; font-weight: bold; color: #0066cc;"><?php echo $stats['total_coches']; ?></div>
                <div>Coches publicados</div>
            </div>
        </div>
    </div>
</div>

<?php
mostrarFooter();
?>