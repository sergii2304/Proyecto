<?php
require_once 'config.php';

// Devolver respuesta en formato JSON
header('Content-Type: application/json');

// Verificar si el usuario está logueado
if (!estaLogueado()) {
    echo json_encode([
        'success' => false,
        'message' => 'Debes iniciar sesión para añadir favoritos'
    ]);
    exit;
}

// Verificar si se recibió el ID del coche
if (!isset($_POST['coche_id']) || empty($_POST['coche_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'No se ha especificado el coche'
    ]);
    exit;
}

$coche_id = $_POST['coche_id'];
$usuario_id = $_SESSION['usuario_id'];

// Verificar si el coche existe
$verificar_coche = "SELECT id_coche FROM Coches WHERE id_coche = ?";
$stmt = $conn->prepare($verificar_coche);
$stmt->bind_param("s", $coche_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'El coche especificado no existe'
    ]);
    exit;
}

// Verificar si ya está en favoritos
$verificar_favorito = "SELECT * FROM Guardar WHERE id_usuario = ? AND id_coche = ?";
$stmt = $conn->prepare($verificar_favorito);
$stmt->bind_param("ss", $usuario_id, $coche_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Ya está en favoritos, eliminar
    $eliminar = "DELETE FROM Guardar WHERE id_usuario = ? AND id_coche = ?";
    $stmt = $conn->prepare($eliminar);
    $stmt->bind_param("ss", $usuario_id, $coche_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'action' => 'removed',
            'message' => 'Coche eliminado de favoritos'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al eliminar de favoritos: ' . $conn->error
        ]);
    }
} else {
    // No está en favoritos, añadir
    $añadir = "INSERT INTO Guardar (id_usuario, id_coche) VALUES (?, ?)";
    $stmt = $conn->prepare($añadir);
    $stmt->bind_param("ss", $usuario_id, $coche_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'action' => 'added',
            'message' => 'Coche añadido a favoritos'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al añadir a favoritos: ' . $conn->error
        ]);
    }
}
?>