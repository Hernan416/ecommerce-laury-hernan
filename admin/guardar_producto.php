<?php
session_start();
require 'auth_admin.php';
require 'conexion.php'; // Asegúrate de que tu conexión se llame así

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Recoges los datos que vienen del formulario (ajusta los nombres según tus inputs)
    $nombre = $_POST['nombre_producto'];
    $artista = $_POST['artista'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $id_categoria = $_POST['id_categoria'];
    $descripcion = $_POST['descripcion'];

    // 2. Haces la consulta para guardar el producto en tu base de datos real
    $sql = "INSERT INTO productos (id_categoria, nombre_producto, artista, precio, stock, descripcion) 
            VALUES ('$id_categoria', '$nombre', '$artista', '$precio', '$stock', '$descripcion')";

    if ($conn->query($sql) === TRUE) {
        
        // 3. AQUÍ SÍ VA TU BLOQUE DE AUDITORÍA (Este ya lo tienes bien puesto)
        if (isset($_SESSION['id_usuario'])) {
            $id_admin = $_SESSION['id_usuario'];
            $accion = "El administrador actualizó o agregó un producto.";
            $conn->query("INSERT INTO audit_logs (id_usuario, accion) VALUES ('$id_admin', '$accion')");
        }

        // 4. Redireccionas de vuelta al panel de productos
        header("Location: admin_productos.php");
        exit();
    } else {
        echo "Error al guardar el producto: " . $conn->error;
    }
}
?>