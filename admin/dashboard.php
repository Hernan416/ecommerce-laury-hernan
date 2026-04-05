<?php include 'auth_admin.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Panel Administrativo - The Drop Vinyls</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background-color: #FDF8E2; font-family: 'Fredoka', sans-serif;">
    <div class="container py-5">
        <h1 style="color: #504E76;">Bienvenido, Administrador</h1>
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card p-4 shadow-sm">
                    <h3>Gestión de Inventario</h3>
                    <p>Agrega nuevos vinilos o edita el stock actual.</p>
                    <a href="productos.php" class="btn text-white" style="background-color: #C06C38;">Gestionar Productos</a>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card p-4 shadow-sm">
                    <h3>Control de Usuarios</h3>
                    <p>Administra cuentas y otorga permisos de admin.</p>
                    <a href="usuarios.php" class="btn text-white" style="background-color: #8D4A23;">Gestionar Usuarios</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>