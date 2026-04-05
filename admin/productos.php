<?php
include 'auth_admin.php';
$conn = new mysqli("localhost", "root", "", "the_drop_vinyls");

// --- LÓGICA DE ELIMINAR ---
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $conn->query("DELETE FROM productos WHERE id = $id");
    header("Location: productos.php?msg=eliminado");
}

// --- LÓGICA DE AGREGAR ---
if (isset($_POST['agregar'])) {
    $nombre = $_POST['nombre'];
    $artista = $_POST['artista'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $img = $_POST['imagen']; // URL o ruta
    $id_cat = $_POST['categoria'];

    $sql = "INSERT INTO productos (id_categoria, nombre_producto, artista, precio, stock, imagen_portada) 
            VALUES ('$id_cat', '$nombre', '$artista', '$precio', '$stock', '$img')";
    $conn->query($sql);
}

$productos = $conn->query("SELECT p.*, c.nombre_categoria FROM productos p JOIN categorias c ON p.id_categoria = c.id");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;600&family=Righteous&display=swap" rel="stylesheet">
</head>
<body style="background-color: #FDF8E2; font-family: 'Fredoka', sans-serif;">

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 style="font-family: 'Righteous'; color: #504E76;">Gestión de Productos</h2>
        <button class="btn text-white" style="background-color: #C06C38;" data-bs-toggle="modal" data-bs-target="#modalAgregar">Nuevo Vinilo</button>
    </div>

    <table class="table table-hover shadow-sm bg-white rounded">
        <thead style="background-color: #E6D8B8;">
            <tr>
                <th>Imagen</th>
                <th>Producto</th>
                <th>Artista</th>
                <th>Precio</th>
                <th>Stock</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while($p = $productos->fetch_assoc()): ?>
            <tr class="align-middle">
                <td><img src="<?php echo $p['imagen_portada']; ?>" width="50" class="rounded"></td>
                <td><?php echo $p['nombre_producto']; ?></td>
                <td><?php echo $p['artista']; ?></td>
                <td>$<?php echo $p['precio']; ?></td>
                <td><?php echo $p['stock']; ?></td>
                <td>
                    <a href="?eliminar=<?php echo $p['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este producto?')">Eliminar</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="modalAgregar" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Nuevo Disco</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" name="nombre" placeholder="Nombre del Álbum" class="form-control mb-2" required>
                <input type="text" name="artista" placeholder="Artista" class="form-control mb-2" required>
                <input type="number" step="0.01" name="precio" placeholder="Precio" class="form-control mb-2" required>
                <input type="number" name="stock" placeholder="Stock Inicial" class="form-control mb-2" required>
                <input type="text" name="imagen" placeholder="URL de la Imagen" class="form-control mb-2">
                <select name="categoria" class="form-select mb-2">
                    <option value="1">Pop</option>
                    <option value="2">Rock/Indie</option>
                    <option value="3">R&B/Soul</option>
                    <option value="4">Electrónica</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="submit" name="agregar" class="btn text-white" style="background-color: #C06C38;">Guardar Producto</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>