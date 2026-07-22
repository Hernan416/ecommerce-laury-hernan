<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está logueado y si su rol es admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("HTTP/1.1 403 Forbidden");
    echo "<h2>Acceso denegado</h2><p>No tienes permisos de administrador para ver esta sección.</p>";
    echo "<a href='index.php'>Volver al inicio</a>";
    exit();
}
?>