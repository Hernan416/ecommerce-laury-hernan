<?php
require 'auth_admin.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - The Drop Vinyls</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Righteous&display=swap" rel="stylesheet">
</head>

<body style="background-color: #FDF8E2; font-family: 'Fredoka', sans-serif; color: #504E76;">

<nav class="navbar shadow-sm" style="background-color: #504E76;">
    <div class="container">
        <span class="navbar-brand text-white" style="font-family: 'Righteous';">
            Panel Administrador
        </span>
        <a href="../index.php" class="btn text-white" style="background-color: #C06C38;">Volver</a>
    </div>
</nav>

<div class="container my-5">
    <h2 class="mb-5 text-center" style="font-family: 'Righteous'; color: #504E76;">Gestión General</h2>

    <div class="row g-4 justify-content-center">

        <div class="col-12 col-sm-10 col-md-8 col-lg-5">
            <div class="card shadow-sm border-0 rounded-4 p-4 text-center" style="border: 2px solid #E6D8B8; background-color: #ffffff;">
                <h4 style="font-family: 'Righteous'; color: #504E76;">Usuarios</h4>
                <p style="color: #C06C38;">Gestiona los usuarios del sistema</p>
                <a href="admin_usuarios.php" class="btn text-white px-5" style="background-color: #C06C38; border-radius: 10px;">Ir</a>
            </div>
        </div>

        <div class="col-12 col-sm-10 col-md-8 col-lg-5">
            <div class="card shadow-sm border-0 rounded-4 p-4 text-center" style="border: 2px solid #E6D8B8; background-color: #ffffff;">
                <h4 style="font-family: 'Righteous'; color: #504E76;">Productos</h4>
                <p style="color: #C06C38;">Gestiona los productos de la tienda</p>
                <a href="admin_productos.php" class="btn text-white px-5" style="background-color: #504E76; border-radius: 10px;">Ir</a>
            </div>
        </div>

    </div>
</div>

</body>
</html>