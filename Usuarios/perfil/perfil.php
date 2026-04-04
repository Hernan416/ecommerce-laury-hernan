<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header("Location: ../login/login.php"); exit(); }

$host = "localhost"; $user = "root"; $pass = ""; $db = "the_drop_vinyls";
$conn = new mysqli($host, $user, $pass, $db);
$id_usuario = $_SESSION['usuario_id'];
$mensaje = "";

// LÓGICA DE ACTUALIZACIÓN
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['actualizar_datos'])) {
        $nueva_direccion = $conn->real_escape_string($_POST['direccion']);
        $conn->query("UPDATE usuarios SET direccion = '$nueva_direccion' WHERE id = '$id_usuario'");
        $mensaje = "Datos actualizados correctamente.";
    }

    if (isset($_POST['cambiar_pass'])) {
        $p1 = $_POST['n_pass'];
        $p2 = $_POST['c_pass'];
        
        if ($p1 === $p2 && !empty($p1)) {
            $conn->query("UPDATE usuarios SET contrasena = '$p1' WHERE id = '$id_usuario'");
            $mensaje = "Contraseña cambiada con éxito.";
        } else {
            $mensaje = "Error: Las contraseñas no coinciden.";
        }
    }
}

// Obtener datos frescos del usuario
$datos_usuario = $conn->query("SELECT * FROM usuarios WHERE id = '$id_usuario'")->fetch_assoc();

$id_usuario = $_SESSION['usuario_id'];

// 1. Obtener datos del usuario
$stmt_user = $conn->prepare("SELECT nombre, apellido, correo, rol, fecha_registro, direccion FROM usuarios WHERE id = ?");
$stmt_user->bind_param("i", $id_usuario);
$stmt_user->execute();
$datos_usuario = $stmt_user->get_result()->fetch_assoc();
$stmt_user->close();

// 2. Obtener historial de facturas
$stmt_facturas = $conn->prepare("SELECT id, precio_final, fecha_emision FROM facturas WHERE id_usuario = ? ORDER BY fecha_emision DESC");
$stmt_facturas->bind_param("i", $id_usuario);
$stmt_facturas->execute();
$resultado_facturas = $stmt_facturas->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - The Drop Vinyls</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Righteous&display=swap" rel="stylesheet">
</head>
<body style="background-color: #FDF8E2; font-family: 'Fredoka', sans-serif; color: #504E76;">

<nav class="navbar navbar-expand-lg shadow-sm" style="background-color: #504E76; padding: 15px 0;">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.php" style="font-family: 'Righteous', sans-serif; color: #FDF8E2; font-size: 1.8rem; letter-spacing: 1px;">
            <img src="assets/LOGO.png" alt="Logo The Drop Vinyls" style="height: 40px; margin-right: 12px; object-fit: contain;">
            The Drop Vinyls
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContenido" aria-controls="navbarContenido" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarContenido">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                <li class="nav-item">
                    <a class="btn text-white fw-medium shadow-sm px-4" href="../index.php" style="background-color: #C06C38;" onmouseover="this.style.backgroundColor='#8D4A23'" onmouseout="this.style.backgroundColor='#C06C38'">Volver a la Tienda</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container my-5">
    <div class="row">
        
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm border-0 rounded-4" style="border: 2px solid #E6D8B8 !important;">
                <div class="card-header text-center p-4" style="background-color: #E6D8B8; border-bottom: 2px solid #C06C38 !important;">
                    <h3 class="m-0" style="font-family: 'Righteous', sans-serif; color: #504E76;">Mi Perfil</h3>
                </div>
                <div class="card-body p-4 bg-white">
                    <h5 class="fw-bold mb-1" style="color: #504E76; font-family: 'Righteous', sans-serif; font-size: 1.5rem;">
                        <?php echo htmlspecialchars($datos_usuario['nombre'] . ' ' . $datos_usuario['apellido']); ?>
                    </h5>
                    <p class="mb-4" style="color: #C06C38; font-weight: 600;">
                        Usuario <?php echo ucfirst(htmlspecialchars($datos_usuario['rol'])); ?>
                    </p>
                    
                    <div class="mb-3">
                     <span class="d-block fw-bold" style="color: #8D4A23;">Correo Electrónico:</span>
                     <span style="color: #504E76;"><?php echo htmlspecialchars($datos_usuario['correo']); ?></span>
                 </div>

                 <div class="mb-3">
                     <span class="d-block fw-bold" style="color: #8D4A23;">Dirección Registrada:</span>
                     <span style="color: #504E76;"><?php echo htmlspecialchars($datos_usuario['direccion'] ?? 'Sin dirección registrada'); ?></span>
                 </div>
                    
                    <div class="mb-4">
                        <span class="d-block fw-bold" style="color: #8D4A23;">Miembro desde:</span>
                        <span style="color: #504E76;"><?php echo date("d/m/Y", strtotime($datos_usuario['fecha_registro'])); ?></span>
                    </div>

                    <div class="d-grid gap-2">
                        <button class="btn fw-medium shadow-sm" style="background-color: #E6D8B8; color: #504E76;" data-bs-toggle="modal" data-bs-target="#modalDatos">Editar Dirección</button>
                        <button class="btn fw-medium text-white shadow-sm" style="background-color: #C06C38;" data-bs-toggle="modal" data-bs-target="#modalPass">Cambiar Contraseña</button>
                        <a href="../../login.php" class="btn fw-medium text-white shadow-sm" style="background-color: #504E76;">Cerrar Sesión</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <h3 class="mb-4" style="font-family: 'Righteous', sans-serif; color: #504E76;">Historial de Compras</h3>
            
            <?php if ($resultado_facturas->num_rows > 0): ?>
                <div class="accordion shadow-sm" id="accordionCompras" style="border: 2px solid #E6D8B8; border-radius: 10px; overflow: hidden;">
                    
                    <?php while($factura = $resultado_facturas->fetch_assoc()): ?>
                        <div class="accordion-item border-0 border-bottom" style="border-color: #E6D8B8 !important;">
                            
                            <h2 class="accordion-header" id="heading<?php echo $factura['id']; ?>">
                                <button class="accordion-button collapsed fw-medium" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $factura['id']; ?>" aria-expanded="false" aria-controls="collapse<?php echo $factura['id']; ?>" style="background-color: white; color: #504E76; box-shadow: none;">
                                    Factura #00<?php echo $factura['id']; ?> - <span class="mx-2" style="color: #8D4A23;"><?php echo date("d/m/Y", strtotime($factura['fecha_emision'])); ?></span> - <span class="ms-auto fw-bold" style="color: #C06C38;">$<?php echo number_format($factura['precio_final'], 2); ?></span>
                                </button>
                            </h2>
                            
                            <div id="collapse<?php echo $factura['id']; ?>" class="accordion-collapse collapse" aria-labelledby="heading<?php echo $factura['id']; ?>" data-bs-parent="#accordionCompras">
                                <div class="accordion-body" style="background-color: #FDF8E2;">
                                    <ul class="list-group list-group-flush rounded border" style="border-color: #E6D8B8 !important;">
                                        <?php
                                            // Consultar los items específicos de esta factura
                                            $stmt_items = $conn->prepare("SELECT p.nombre_producto, p.artista, fd.cantidad, fd.precio_unitario FROM factura_detalles fd JOIN productos p ON fd.id_producto = p.id WHERE fd.id_factura = ?");
                                            $stmt_items->bind_param("i", $factura['id']);
                                            $stmt_items->execute();
                                            $items = $stmt_items->get_result();
                                            
                                            while($item = $items->fetch_assoc()):
                                        ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: white; color: #504E76;">
                                                <div>
                                                    <span class="fw-bold"><?php echo htmlspecialchars($item['nombre_producto']); ?></span><br>
                                                    <small style="color: #8D4A23;"><?php echo htmlspecialchars($item['artista']); ?></small>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge rounded-pill" style="background-color: #E6D8B8; color: #504E76;">x<?php echo $item['cantidad']; ?></span>
                                                    <span class="ms-2 fw-medium" style="color: #C06C38;">$<?php echo number_format($item['precio_unitario'], 2); ?></span>
                                                </div>
                                            </li>
                                        <?php endwhile; $stmt_items->close(); ?>
                                    </ul>
                                </div>
                            </div>
                            
                        </div>
                    <?php endwhile; ?>
                    
                </div>
            <?php else: ?>
                <div class="card bg-white border-0 shadow-sm p-5 text-center rounded-4" style="border: 2px solid #E6D8B8 !important;">
                    <h5 style="color: #8D4A23; font-family: 'Righteous', sans-serif;">Aún no has comprado ningún vinilo.</h5>
                    <p style="color: #504E76;">Explora nuestro catálogo y empieza tu colección.</p>
                    <a href="index.php" class="btn text-white mt-3 mx-auto px-4 py-2" style="background-color: #C06C38; max-width: 200px;" onmouseover="this.style.backgroundColor='#8D4A23'" onmouseout="this.style.backgroundColor='#C06C38'">Ir a la Tienda</a>
                </div>
            <?php endif; ?>
            
        </div>
    </div>
</div>

<div class="modal fade" id="modalDatos" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="POST">
      <div class="modal-header" style="background-color: #E6D8B8;">
        <h5 class="modal-title" style="font-family: 'Righteous';">Editar Dirección</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <label class="form-label">Nueva Dirección de Entrega</label>
        <input type="text" name="direccion" class="form-control" value="<?php echo htmlspecialchars($datos_usuario['direccion']); ?>" required>
      </div>
      <div class="modal-footer">
        <button type="submit" name="actualizar_datos" class="btn text-white" style="background-color: #504E76;">Guardar Cambios</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="modalPass" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="POST">
      <div class="modal-header" style="background-color: #E6D8B8;">
        <h5 class="modal-title" style="font-family: 'Righteous';">Cambiar Contraseña</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
            <label class="form-label">Nueva Contraseña</label>
            <input type="password" name="n_pass" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Confirmar Nueva Contraseña</label>
            <input type="password" name="c_pass" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="cambiar_pass" class="btn text-white" style="background-color: #C06C38;">Actualizar Contraseña</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php 
$stmt_facturas->close();
$conn->close(); 
?>