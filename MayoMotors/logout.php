<?php
require_once 'config.php';

// Cerrar la sesión
session_destroy();

// Redirigir al inicio
header("Location: index.php");
exit();
?>