<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "the_drop_vinyls"; // El nombre de la base de datos que está en tu archivo .sql

$conn = new mysqli($host, $user, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>