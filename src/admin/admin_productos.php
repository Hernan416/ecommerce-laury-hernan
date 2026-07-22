<?php
require 'auth_admin.php';

$conn = new mysqli(getenv("DB_HOST") ?: "localhost", "root", "", "the_drop_vinyls");

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Obtener el ID del administrador actual desde la sesión
// (Asegúrate de que la clave de tu sesión sea 'id', o cámbiala por 'usuario_id' si es el caso)
$id_admin_actual = isset($_SESSION['usuario_id']) ? intval($_SESSION['usuario_id']) : 1;

// Función para optimizar y redimensionar imagen
function optimizar_imagen($origen, $destino, $max_width = 600, $max_height = 600) {
    $info = getimagesize($origen);
    if ($info['mime'] == 'image/jpeg') {
        $img = imagecreatefromjpeg($origen);
    } elseif ($info['mime'] == 'image/png') {
        $img = imagecreatefrompng($origen);
    } else {
        return false;
    }
    
    $width = $info[0];
    $height = $info[1];
    $ratio = min($max_width / $width, $max_height / $height);
    $new_width = round($width * $ratio);
    $new_height = round($height * $ratio);
    
    $new_img = imagecreatetruecolor($new_width, $new_height);
    if ($info['mime'] == 'image/png') {
        imagealphablending($new_img, false);
        imagesavealpha($new_img, true);
        $transparent = imagecolorallocatealpha($new_img, 255, 255, 255, 127);
        imagefilledrectangle($new_img, 0, 0, $new_width, $new_height, $transparent);
    }
    
    imagecopyresampled($new_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    
    if ($info['mime'] == 'image/jpeg') {
        imagejpeg($new_img, $destino, 85);
    } elseif ($info['mime'] == 'image/png') {
        imagepng($new_img, $destino, 8);
    }
    imagedestroy($img);
    imagedestroy($new_img);
    return true;
}

// ELIMINAR PRODUCTO
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    
    $res_prod = $conn->query("SELECT nombre_producto FROM productos WHERE id = $id");
    $nombre_prod = ($res_prod && $prod = $res_prod->fetch_assoc()) ? $prod['nombre_producto'] : 'Desconocido';

    if ($conn->query("DELETE FROM productos WHERE id = $id") === TRUE) {
        // Registrar auditoría con el ID real de la sesión
        @$conn->query("INSERT INTO audit_logs (id_usuario, accion) VALUES ($id_admin_actual, 'Eliminó el producto: $nombre_prod')");

        header("Location: admin_productos.php");
        exit();
    } else {
        die("<h3 style='color:red;'>Error al eliminar:</h3> " . $conn->error);
    }
}

// AGREGAR PRODUCTO
if (isset($_POST['agregar'])) {
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $artista = $conn->real_escape_string($_POST['artista']);
    $precio = floatval($_POST['precio']);
    $stock = intval($_POST['stock']);
    $id_cat = intval($_POST['id_categoria']);
    $desc = $conn->real_escape_string($_POST['descripcion']);
    
    if ($precio <= 0 || $stock < 0) {
        die("<h3 style='color:red;'>Error:</h3> Precio debe ser mayor a 0 y stock mayor o igual a 0.");
    }
    
    $imagen_ruta = "";
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $nombre_archivo = time() . '_' . basename($_FILES['imagen']['name']);
        $ruta_destino = __DIR__ . '/../assets/uploads/' . $nombre_archivo;
        
        if (optimizar_imagen($_FILES['imagen']['tmp_name'], $ruta_destino)) {
            $imagen_ruta = '../assets/uploads/' . $nombre_archivo;
        } else {
            die("<h3 style='color:red;'>Error:</h3> Formato de imagen no soportado.");
        }
    } else {
        die("<h3 style='color:red;'>Error:</h3> Debe subir una imagen válida.");
    }
    
    $sql = "INSERT INTO productos (nombre_producto, artista, precio, stock, id_categoria, imagen_portada, descripcion) 
            VALUES ('$nombre', '$artista', '$precio', $stock, $id_cat, '$imagen_ruta', '$desc')";
    
    if ($conn->query($sql) === TRUE) {
        @$conn->query("INSERT INTO audit_logs (id_usuario, accion) VALUES ($id_admin_actual, 'Agregó el producto: $nombre')");
        echo "<script>alert('¡Producto guardado con éxito!'); window.location.href='admin_productos.php';</script>";
        exit();
    } else {
        die("<h3 style='color:red;'>Error de MySQL al insertar producto:</h3> " . $conn->error);
    }
}

// EDITAR PRODUCTO
if (isset($_POST['editar_producto'])) {
    $id = intval($_POST['id']);
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $artista = $conn->real_escape_string($_POST['artista']);
    $precio = floatval($_POST['precio']);
    $stock = intval($_POST['stock']);
    $id_cat = intval($_POST['id_categoria']);
    $desc = $conn->real_escape_string($_POST['descripcion']);
    
    if ($precio <= 0 || $stock < 0) {
        die("<h3 style='color:red;'>Error:</h3> Precio debe ser mayor a 0 y stock mayor o igual a 0.");
    }
    
    $imagen_ruta = $conn->real_escape_string($_POST['imagen_actual']);
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $nombre_archivo = time() . '_' . basename($_FILES['imagen']['name']);
        $ruta_destino = __DIR__ . '/../assets/uploads/' . $nombre_archivo;
        
        if (optimizar_imagen($_FILES['imagen']['tmp_name'], $ruta_destino)) {
            $imagen_ruta = '../assets/uploads/' . $nombre_archivo;
        }
    }
    
    $sql = "UPDATE productos SET 
            nombre_producto = '$nombre', artista = '$artista', precio = '$precio', 
            stock = $stock, id_categoria = $id_cat, imagen_portada = '$imagen_ruta', descripcion = '$desc' 
            WHERE id = $id";
    
    if ($conn->query($sql) === TRUE) {
        @$conn->query("INSERT INTO audit_logs (id_usuario, accion) VALUES ($id_admin_actual, 'Actualizó el producto: $nombre')");
        echo "<script>alert('¡Producto actualizado con éxito!'); window.location.href='admin_productos.php';</script>";
        exit();
    } else {
        die("<h3 style='color:red;'>Error de MySQL al actualizar producto:</h3> " . $conn->error);
    }
}

// CONSULTA PARA LA TABLA
$sql_ver = "SELECT p.*, c.nombre_categoria 
            FROM productos p 
            LEFT JOIN categorias c ON p.id_categoria = c.id";
$result = $conn->query($sql_ver);

// OBTENER CATEGORÍAS PARA LOS SELECTS
$res_cats = $conn->query("SELECT * FROM categorias");
$todas_las_cats = [];
while($cat_row = $res_cats->fetch_assoc()){
    $todas_las_cats[] = $cat_row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php 
    if (file_exists(__DIR__ . '/../assets/head.php')) {
        include(__DIR__ . '/../assets/head.php'); 
    }
    ?>
    <meta charset="UTF-8">
    <title>Inventario - The Drop Vinyls</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Righteous&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { background-color: #FDF8E2; font-family: 'Fredoka', sans-serif; color: #504E76; }
        .tabla-card { background: white; border-radius: 20px; overflow: hidden; border: 2px solid #E6D8B8; }
        thead { background-color: #E6D8B8; }
        th { font-family: 'Righteous'; color: #504E76; border: none !important; padding: 15px !important; }
        td { border-bottom: 1px solid #C06C38 !important; padding: 15px !important; vertical-align: middle; }
        
        .btn-custom { border-radius: 10px; font-weight: 500; padding: 8px 16px; border: none; transition: 0.3s; text-decoration: none; display: inline-block; font-size: 0.85rem; }
        .btn-editar { background-color: #504E76; color: white; }
        .btn-eliminar { background-color: #C06C38; color: white; }
        .btn-agregar { background-color: #C06C38; color: white; font-family: 'Righteous'; }
        
        .badge-genero { background-color: #E6D8B8; color: #504E76; font-weight: 600; padding: 5px 12px; border-radius: 15px; font-size: 0.75rem; }
        .img-mini { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; border: 1px solid #E6D8B8; }
        .modal-content { border-radius: 20px; background-color: #FDF8E2; border: none; }
        .form-control, .form-select { border-radius: 10px; border: 1px solid #E6D8B8; }
    </style>
</head>
<body>

<nav class="navbar shadow-sm" style="background-color:#504E76;">
    <div class="container">
        <span class="navbar-brand text-white" style="font-family:'Righteous';">Gestión de Catálogo</span>
        <a href="admin_dashboard.php" class="btn text-white" style="background-color:#C06C38; border-radius: 10px;">Volver</a>
    </div>
</nav>

<div class="container-fluid px-5 my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 style="font-family:'Righteous'; color: #504E76;">Inventario de Vinilos</h3>
        <button class="btn-custom btn-agregar shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAgregar">
            + Nuevo Vinilo
        </button>
    </div>

    <div class="tabla-card shadow-sm">
        <div class="table-responsive">
            <table class="table text-center">
                <thead>
                    <tr>
                        <th>Portada</th>
                        <th>Álbum</th>
                        <th>Artista</th>
                        <th>Género</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($p = $result->fetch_assoc()): ?>
                    <tr>
                        <td><img src="<?= htmlspecialchars($p['imagen_portada']) ?>" class="img-mini" alt="Vinilo"></td>
                        <td style="font-weight: 600;"><?= htmlspecialchars($p['nombre_producto']) ?></td>
                        <td style="color: #C06C38;"><?= htmlspecialchars($p['artista']) ?></td>
                        <td><span class="badge-genero"><?= htmlspecialchars($p['nombre_categoria'] ?? 'Sin Género') ?></span></td>
                        <td style="font-weight: 700;">$<?= number_format($p['precio'], 2) ?></td>
                        <td><?= $p['stock'] ?></td>
                        <td>
                            <div class="d-flex gap-2 justify-content-center">
                                <button class="btn-custom btn-editar" data-bs-toggle="modal" data-bs-target="#modalEditar<?= $p['id'] ?>">Editar</button>
                                <a href="javascript:void(0);" class="btn-custom btn-eliminar" onclick="confirmarEliminacion(<?= $p['id'] ?>)">Eliminar</a>
                            </div>
                        </td>
                    </tr>

                    <div class="modal fade" id="modalEditar<?= $p['id'] ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content p-4 shadow">
                                <h5 class="modal-title mb-4" style="font-family:'Righteous'; color:#504E76;">Editar Vinilo</h5>
                                <form method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                    <div class="row g-3">
                                        <div class="col-md-6 text-start">
                                            <label class="form-label">Nombre del Álbum</label>
                                            <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($p['nombre_producto']) ?>" required>
                                        </div>
                                        <div class="col-md-6 text-start">
                                            <label class="form-label">Artista</label>
                                            <input type="text" name="artista" class="form-control" value="<?= htmlspecialchars($p['artista']) ?>" required>
                                        </div>
                                        <div class="col-md-4 text-start">
                                            <label class="form-label">Precio ($)</label>
                                            <input type="number" step="0.01" min="0.01" name="precio" class="form-control" value="<?= $p['precio'] ?>" required>
                                        </div>
                                        <div class="col-md-4 text-start">
                                            <label class="form-label">Stock</label>
                                            <input type="number" min="0" name="stock" class="form-control" value="<?= $p['stock'] ?>" required>
                                        </div>
                                        <div class="col-md-4 text-start">
                                            <label class="form-label">Género</label>
                                            <select name="id_categoria" class="form-select" required>
                                                <?php foreach($todas_las_cats as $c): ?>
                                                    <option value="<?= $c['id'] ?>" <?= ($p['id_categoria'] == $c['id']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($c['nombre_categoria']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-12 text-start">
                                            <label class="form-label">Imagen de Portada (JPG/PNG) - Dejar vacío para mantener la actual</label>
                                            <input type="hidden" name="imagen_actual" value="<?= htmlspecialchars($p['imagen_portada']) ?>">
                                            <input type="file" name="imagen" class="form-control" accept="image/jpeg, image/png">
                                        </div>
                                        <div class="col-12 text-start">
                                            <label class="form-label">Descripción</label>
                                            <textarea name="descripcion" class="form-control" rows="3"><?= htmlspecialchars($p['descripcion']) ?></textarea>
                                        </div>
                                    </div>
                                    <button type="submit" name="editar_producto" class="btn-custom btn-editar w-100 mt-4 py-2">Guardar Cambios</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAgregar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content p-4 shadow">
            <h5 class="modal-title mb-4" style="font-family:'Righteous'; color:#504E76;">Nuevo Vinilo al Catálogo</h5>
            <form method="POST" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-6 text-start">
                        <label class="form-label">Nombre del Álbum</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej: Midnights" required>
                    </div>
                    <div class="col-md-6 text-start">
                        <label class="form-label">Artista</label>
                        <input type="text" name="artista" class="form-control" placeholder="Ej: Taylor Swift" required>
                    </div>
                    <div class="col-md-4 text-start">
                        <label class="form-label">Precio ($)</label>
                        <input type="number" step="0.01" min="0.01" name="precio" class="form-control" required>
                    </div>
                    <div class="col-md-4 text-start">
                        <label class="form-label">Stock</label>
                        <input type="number" min="0" name="stock" class="form-control" required>
                    </div>
                    <div class="col-md-4 text-start">
                        <label class="form-label">Género</label>
                        <select name="id_categoria" class="form-select" required>
                            <option value="">Seleccionar...</option>
                            <?php foreach($todas_las_cats as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre_categoria']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 text-start">
                        <label class="form-label">Subir Imagen de Portada (JPG/PNG)</label>
                        <input type="file" name="imagen" class="form-control" accept="image/jpeg, image/png" required>
                    </div>
                    <div class="col-12 text-start">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3" placeholder="Detalles técnicos del vinilo..."></textarea>
                    </div>
                </div>
                <button type="submit" name="agregar" class="btn-custom btn-editar w-100 mt-4 py-2">Registrar Disco</button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function confirmarEliminacion(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¡Este vinilo desaparecerá del inventario!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#C06C38',
        cancelButtonColor: '#504E76',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        background: '#FDF8E2',
        color: '#504E76'
    }).then((result) => { if (result.isConfirmed) { window.location.href = "admin_productos.php?eliminar=" + id; } })
}
</script>
</body>
</html>
