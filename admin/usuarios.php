<?php 
include 'auth_admin.php';
$conn = new mysqli("localhost", "root", "", "the_drop_vinyls");

// Lógica para ASCENDER A ADMIN
if (isset($_GET['promover'])) {
    $id = $_GET['promover'];
    $conn->query("UPDATE usuarios SET rol = 'admin' WHERE id = $id");
    header("Location: usuarios.php");
}

// Lógica para ELIMINAR (Tarea masiva)
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $conn->query("DELETE FROM usuarios WHERE id = $id");
    header("Location: usuarios.php");
}

$usuarios = $conn->query("SELECT * FROM usuarios");
?>

<table class="table">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Correo</th>
            <th>Rol</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php while($u = $usuarios->fetch_assoc()): ?>
        <tr>
            <td><?php echo $u['nombre']; ?></td>
            <td><?php echo $u['correo']; ?></td>
            <td><?php echo $u['rol']; ?></td>
            <td>
                <?php if($u['rol'] == 'cliente'): ?>
                    <a href="?promover=<?php echo $u['id']; ?>" class="btn btn-sm btn-success">Ascender</a>
                <?php endif; ?>
                <a href="?eliminar=<?php echo $u['id']; ?>" class="btn btn-sm btn-danger">Eliminar</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>