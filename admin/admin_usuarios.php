<?php
require 'auth_admin.php';
$conn = new mysqli("localhost", "root", "", "the_drop_vinyls");

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// 1. LÓGICA PARA AGREGAR USUARIO 
if (isset($_POST['agregar_usuario'])) {
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $correo = $conn->real_escape_string($_POST['correo']);
    $direccion = $conn->real_escape_string($_POST['direccion']); 
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $rol = $conn->real_escape_string($_POST['rol']);

    $sql = "INSERT INTO usuarios (nombre, correo, direccion, contrasena, rol) 
            VALUES ('$nombre', '$correo', '$direccion', '$pass', '$rol')";

    if ($conn->query($sql)) {
        header("Location: admin_usuarios.php?status=success");
        exit();
    } else {
        echo "Error al guardar: " . $conn->error;
    }
}

// 2. ELIMINAR 
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $conn->query("DELETE FROM usuarios WHERE id = $id");
    header("Location: admin_usuarios.php");
}

// 3. CAMBIAR ROL
if (isset($_GET['cambiar_rol'])) {
    $id = intval($_GET['cambiar_rol']);
    $nuevo_rol = $_GET['rol'];
    $conn->query("UPDATE usuarios SET rol = '$nuevo_rol' WHERE id = $id");
    header("Location: admin_usuarios.php");
}

$result = $conn->query("SELECT * FROM usuarios");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Usuarios - The Drop Vinyls</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Righteous&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { background-color: #FDF8E2; font-family: 'Fredoka', sans-serif; color: #504E76; }
        .tabla-card { background: white; border-radius: 20px; overflow: hidden; border: 2px solid #E6D8B8; }
        thead { background-color: #E6D8B8; }
        th { font-family: 'Righteous'; color: #504E76; border: none !important; padding: 15px !important; }
        td { border-bottom: 1px solid #C06C38 !important; padding: 15px !important; vertical-align: middle; }
        
        .btn-custom { border-radius: 10px; font-weight: 500; padding: 8px 16px; border: none; transition: 0.3s; text-decoration: none; display: inline-block; font-size: 0.9rem; }
        .btn-admin { background-color: #504E76; color: white; }
        .btn-quitar { background-color: #E6D8B8; color: #504E76; }
        .btn-eliminar { background-color: #C06C38; color: white; }
        .btn-agregar { background-color: #C06C38; color: white; font-family: 'Righteous'; }
        
        .rol-text { color: #C06C38; font-weight: 600; text-transform: capitalize; }
        
        .modal-content { border-radius: 20px; border: none; background-color: #FDF8E2; }
        .form-control { border-radius: 10px; border: 1px solid #E6D8B8; }
    </style>
</head>
<body>

<nav class="navbar shadow-sm" style="background-color:#504E76;">
    <div class="container">
        <span class="navbar-brand text-white" style="font-family:'Righteous';">Gestión de Usuarios</span>
        <a href="admin_dashboard.php" class="btn text-white" style="background-color:#C06C38; border-radius: 10px;">Volver</a>
    </div>
</nav>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 style="font-family:'Righteous';">Lista de Usuarios</h3>
        <button class="btn-custom btn-agregar shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAgregar">
            + Nuevo Usuario
        </button>
    </div>

    <div class="tabla-card shadow-sm">
        <div class="table-responsive">
            <table class="table text-center">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($u = $result->fetch_assoc()): ?>
                    <tr>
                        <td style="font-weight: 600;"><?= $u['nombre'] ?></td>
                        <td><?= $u['correo'] ?></td>
                        <td><span class="rol-text"><?= $u['rol'] ?></span></td>
                        <td>
                            <div class="d-flex gap-2 justify-content-center">
                                <?php if ($u['rol'] == 'cliente'): ?>
                                    <a href="?cambiar_rol=<?= $u['id'] ?>&rol=admin" class="btn-custom btn-admin">Hacer Admin</a>
                                <?php else: ?>
                                    <a href="?cambiar_rol=<?= $u['id'] ?>&rol=cliente" class="btn-custom btn-quitar">Quitar Admin</a>
                                <?php endif; ?>

                                <a href="javascript:void(0);" class="btn-custom btn-eliminar" onclick="confirmarEliminacion(<?= $u['id'] ?>)">Eliminar</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAgregar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content p-3 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title" style="font-family:'Righteous'; color:#504E76;">Agregar Nuevo Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre Completo</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Correo Electrónico</label>
                        <input type="email" name="correo" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dirección</label>
                        <input type="text" name="direccion" class="form-control" placeholder="Ej: Calle 123, Ciudad" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rol inicial</label>
                        <select name="rol" class="form-select" style="border-radius:10px;">
                            <option value="cliente">Cliente</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" name="agregar_usuario" class="btn-custom btn-admin w-100">Guardar Usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function confirmarEliminacion(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¡El usuario será borrado permanentemente!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#C06C38',
        cancelButtonColor: '#504E76',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        background: '#FDF8E2',
        color: '#504E76'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "admin_usuarios.php?eliminar=" + id;
        }
    })
}
</script>
</body>
</html>
