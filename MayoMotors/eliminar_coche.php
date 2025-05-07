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
    // Iniciar la transacción
    $conn->begin_transaction();
    
    // Obtener las imágenes asociadas al coche
    $imagenes_sql = "SELECT id_imagen, url FROM Imagenes WHERE id_coche = ?";
    $stmt = $conn->prepare($imagenes_sql);
    $stmt->bind_param("s", $coche_id);
    $stmt->execute();
    $imagenes_result = $stmt->get_result();
    
    // Array para registrar errores en la eliminación de archivos
    $errores_eliminacion = [];
    
    // Eliminar los archivos de imágenes del servidor
    while ($imagen = $imagenes_result->fetch_assoc()) {
        $ruta_imagen = $imagen['url'];
        
        // Solo intentar eliminar si no es la imagen por defecto
        if ($ruta_imagen != 'css/no-image.png') {
            // Verificar si el archivo existe usando la ruta completa del servidor
            $ruta_completa = $_SERVER['DOCUMENT_ROOT'] . '/' . $ruta_imagen;
            $ruta_relativa = $ruta_imagen;
            
            // Intentar con la ruta tal como está en la base de datos
            if (file_exists($ruta_imagen)) {
                if (!unlink($ruta_imagen)) {
                    $errores_eliminacion[] = "No se pudo eliminar el archivo: $ruta_imagen";
                }
            } 
            // Intentar con la ruta relativa (sin el DOCUMENT_ROOT)
            else if (file_exists($ruta_relativa)) {
                if (!unlink($ruta_relativa)) {
                    $errores_eliminacion[] = "No se pudo eliminar el archivo: $ruta_relativa";
                }
            }
            // Intentar con la ruta completa
            else if (file_exists($ruta_completa)) {
                if (!unlink($ruta_completa)) {
                    $errores_eliminacion[] = "No se pudo eliminar el archivo: $ruta_completa";
                }
            } else {
                // Registrar que no se encontró el archivo
                $errores_eliminacion[] = "No se encontró el archivo: $ruta_imagen";
            }
        }
        
        // Eliminar la referencia en la base de datos
        $eliminar_imagen = "DELETE FROM Imagenes WHERE id_imagen = ?";
        $stmt = $conn->prepare($eliminar_imagen);
        $stmt->bind_param("s", $imagen['id_imagen']);
        $stmt->execute();
    }
    
    // Eliminar las entradas en la tabla Guardar
    $eliminar_favoritos = "DELETE FROM Guardar WHERE id_coche = ?";
    $stmt = $conn->prepare($eliminar_favoritos);
    $stmt->bind_param("s", $coche_id);
    $stmt->execute();
    
    // Eliminar el coche
    $eliminar_coche = "DELETE FROM Coches WHERE id_coche = ?";
    $stmt = $conn->prepare($eliminar_coche);
    $stmt->bind_param("s", $coche_id);
    
    if ($stmt->execute()) {
        // Confirmar la transacción
        $conn->commit();
        
        // Mostrar mensaje de éxito
        if (empty($errores_eliminacion)) {
            mostrarAlerta('El coche ha sido eliminado correctamente junto con sus imágenes y referencias.');
        } else {
            // Si hubo errores al eliminar archivos, mostrar alerta con advertencia
            mostrarAlerta('El coche ha sido eliminado de la base de datos, pero hubo problemas al eliminar algunos archivos de imágenes.', 'warning');
            
            // Registrar los errores en un archivo de log para revisión posterior
            error_log("Errores al eliminar imágenes: " . implode(", ", $errores_eliminacion));
        }
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