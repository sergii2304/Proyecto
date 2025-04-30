<?php
// Configuración de la aplicación(Sesión iniciada)
session_start();

// Archivo de conexión
require_once 'conexion.php';

function generarID($tabla) {
    switch ($tabla) {
        case 'Usuarios':
            // Formato U seguido de 4 dígitos aleatorios
            return 'USU' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        case 'Coches':
            // Formato C seguido de 4 dígitos aleatorios
            return 'C' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        case 'Imagenes':
            // Formato IMG seguido de 3 dígitos aleatorios
            return 'IMG' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        default:
            // Formato genérico con 4 dígitos aleatorios
            return 'ID' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }
}

// Función para limpiar los inputs
function limpiarInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Función para verificar si el usuario está logueado
function estaLogueado() {
    return isset($_SESSION['usuario_id']);
}

// Función para verificar si el usuario es administrador
function esAdmin() {
    return isset($_SESSION['es_admin']) && $_SESSION['es_admin'] === true;
}

// Función para redirigir
function redirigir($url) {
    header("Location: $url");
    exit();
}

// Función para mostrar mensajes de alerta
function mostrarAlerta($mensaje, $tipo = 'success') {
    $_SESSION['alerta'] = [
        'mensaje' => $mensaje,
        'tipo' => $tipo
    ];
}