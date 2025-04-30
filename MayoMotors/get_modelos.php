<?php
require_once 'config.php';

// Devolver la respuesta en formato JSON
header('Content-Type: application/json');

// Verificar si se proporcionó un ID de marca
if (!isset($_GET['marca_id']) || empty($_GET['marca_id'])) {
    echo json_encode([]);
    exit;
}

$marca_id = $_GET['marca_id'];

// Obtener modelos de la marca seleccionada
$sql = "SELECT id_modelo, nombre FROM Modelos WHERE id_marca = ? ORDER BY nombre";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $marca_id);
$stmt->execute();
$result = $stmt->get_result();

$modelos = [];
while ($modelo = $result->fetch_assoc()) {
    $modelos[] = $modelo;
}

echo json_encode($modelos);
?>