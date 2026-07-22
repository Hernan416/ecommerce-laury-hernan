<?php
session_start();

if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Solución automática: si el ID no está en la sesión, lo buscamos en la base de datos
if (!isset($_SESSION['id'])) {
    $conn_temp = new mysqli(getenv("DB_HOST") ?: "localhost", "root", "", "the_drop_vinyls");
    if (!$conn_temp->connect_error && isset($_SESSION['usuario'])) {
        $user_session = $conn_temp->real_escape_string($_SESSION['usuario']);
        // Busca al usuario por su nombre o correo en tu tabla
        $res = $conn_temp->query("SELECT id FROM usuarios WHERE nombre = '$user_session' OR email = '$user_session' LIMIT 1");
        if ($res && $row = $res->fetch_assoc()) {
            $_SESSION['id'] = $row['id']; // Asigna el ID correcto a la sesión
        }
        $conn_temp->close();
    }
}
?>
