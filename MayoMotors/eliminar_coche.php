<?php
require_once 'layout.php';

// Verificar si el usuario está logueado y es administrador
if (!estaLogueado() || !esAdmin()) {
    mostrarAlerta('No tienes permisos para realizar esta acción', 'danger');
    redirigir('coches.php');
}

// Verificar si se proporcionó un ID de coche
if (!isset($_GET['id']) || empty($_GET['id'])) {
    mostrarAlerta('No se ha especificado ningún coche', 'danger');
    redirigir('coches.php');
}

$coche_id = $_GET['id'];

// Verificar si el coche existe
$verificar_sql = "SELECT id_coche FROM Coches WHERE id_coche = ?";
$stmt = $conn->prepare($verificar_sql);
$stmt->bind_param("s", $coche_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    mostrarAlerta('El coche especificado no existe', 'danger');
    redirigir('coches.php');
}

try {
    // Iniciar transacción
    $conn->begin_transaction();
    
    // Eliminar imágenes
    $imagenes_sql = "SELECT id_imagen, url FROM Imagenes WHERE id_coche = ?";
    $stmt = $conn->prepare($imagenes_sql);
    $stmt->bind_param("s", $coche_id);
    $stmt->execute();
    $imagenes_result = $stmt->get_result();
    
    // Eliminar archivos de imágenes del servidor si no son la imagen por defecto
    while ($imagen = $imagenes_result->fetch_assoc()) {
        if ($imagen['url'] != 'img/no-image.png' && file_exists($imagen['url'])) {
            unlink($imagen['url']);
        }
        
        // Eliminar referencia en la base de datos
        $eliminar_imagen = "DELETE FROM Imagenes WHERE id_imagen = ?";
        $stmt = $conn->prepare($eliminar_imagen);
        $stmt->bind_param("s", $imagen['id_imagen']);
        $stmt->execute();
    }
    
    // Eliminar entradas en la tabla Guardar (favoritos)
    $eliminar_favoritos = "DELETE FROM Guardar WHERE id_coche = ?";
    $stmt = $conn->prepare($eliminar_favoritos);
    $stmt->bind_param("s", $coche_id);
    $stmt->execute();
    
    // Finalmente, eliminar el coche
    $eliminar_coche = "DELETE FROM Coches WHERE id_coche = ?";
    $stmt = $conn->prepare($eliminar_coche);
    $stmt->bind_param("s", $coche_id);
    
    if ($stmt->execute()) {
        // Confirmar transacción
        $conn->commit();
        mostrarAlerta('El coche ha sido eliminado correctamente junto con sus imágenes y referencias.');
    } else {
        throw new Exception("Error al eliminar el coche: " . $stmt->error);
    }
    
} catch (Exception $e) {
    // Rollback en caso de error
    $conn->rollback();
    mostrarAlerta('Error: ' . $e->getMessage(), 'danger');
}

// Redirigir a la lista de coches
redirigir('coches.php');
?>