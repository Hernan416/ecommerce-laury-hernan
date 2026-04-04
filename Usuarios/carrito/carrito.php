<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login/login.php");
    exit();
}

$host = "localhost";
$user = "root";
$pass = "";
$db   = "the_drop_vinyls";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) { die("Error de conexión: " . $conn->connect_error); }

$id_usuario = $_SESSION['usuario_id'];

// Lógica para eliminar un producto del carrito
if (isset($_GET['eliminar'])) {
    $id_carrito_eliminar = intval($_GET['eliminar']);
    $stmt_del = $conn->prepare("DELETE FROM carrito WHERE id = ? AND id_usuario = ?");
    $stmt_del->bind_param("ii", $id_carrito_eliminar, $id_usuario);
    $stmt_del->execute();
    $stmt_del->close();
    header("Location: carrito.php"); // Recargar la página para actualizar el total
    exit();
}

// Consultar los productos en el carrito
$sql = "SELECT c.id AS id_carrito, p.nombre_producto, p.artista, p.precio, p.imagen_portada, c.cantidad 
        FROM carrito c 
        JOIN productos p ON c.id_producto = p.id 
        WHERE c.id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();

$total = 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Carrito - The Drop Vinyls</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Righteous&display=swap" rel="stylesheet">
</head>
<body style="background-color: #FDF8E2; font-family: 'Fredoka', sans-serif; color: #504E76;">

<nav class="navbar navbar-expand-lg shadow-sm" style="background-color: #504E76; padding: 15px 0;">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="../index.php" style="font-family: 'Righteous', sans-serif; color: #FDF8E2; font-size: 1.8rem; letter-spacing: 1px;">
            <img src="../assets/LOGO.png" alt="Logo The Drop Vinyls" style="height: 40px; margin-right: 12px; object-fit: contain;">
            The Drop Vinyls
        </a>
        <div class="d-flex ms-auto">
            <a class="btn shadow-sm px-4 fw-medium" href="../perfil/perfil.php" style="background-color: #E6D8B8; color: #504E76;" onmouseover="this.style.backgroundColor='#FDF8E2'; this.style.color='#8D4A23';" onmouseout="this.style.backgroundColor='#E6D8B8'; this.style.color='#504E76';">Mi Perfil</a>
        </div>
    </div>
</nav>

<div class="container my-5">
    <h2 class="mb-4" style="font-family: 'Righteous', sans-serif; color: #504E76;">Tu Carrito de Compras</h2>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <?php if ($resultado->num_rows > 0): ?>
                <?php while($row = $resultado->fetch_assoc()): 
                    $subtotal = $row['precio'] * $row['cantidad'];
                    $total += $subtotal;
                ?>
                    <div class="card shadow-sm border-0 rounded-4 mb-3" style="border: 2px solid #E6D8B8 !important;">
                        <div class="row g-0 align-items-center p-3 bg-white rounded-4">
                            <div class="col-md-2 text-center">
                                <img src="../img/<?php echo $row['imagen_portada']; ?>" class="img-fluid rounded" alt="Portada" style="max-height: 100px; object-fit: cover;" onerror="this.src='https://via.placeholder.com/100x100/E6D8B8/504E76?text=Vinilo'">
                            </div>
                            <div class="col-md-6">
                                <div class="card-body py-2">
                                    <h5 class="card-title m-0" style="font-family: 'Righteous', sans-serif; color: #504E76;"><?php echo htmlspecialchars($row['nombre_producto']); ?></h5>
                                    <p class="card-text fw-medium m-0" style="color: #8D4A23;"><small><?php echo htmlspecialchars($row['artista']); ?></small></p>
                                    <p class="card-text mt-2 mb-0 fw-bold" style="color: #C06C38;">$<?php echo number_format($row['precio'], 2); ?> <span class="fw-normal text-muted" style="font-size: 0.9rem;">x <?php echo $row['cantidad']; ?></span></p>
                                </div>
                            </div>
                            <div class="col-md-4 text-center text-md-end px-3">
                                <a href="carrito.php?eliminar=<?php echo $row['id_carrito']; ?>" class="btn btn-sm text-white fw-medium shadow-sm" style="background-color: #504E76;" onmouseover="this.style.backgroundColor='#8D4A23'" onmouseout="this.style.backgroundColor='#504E76'">🗑️ Eliminar</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="alert text-center fw-medium shadow-sm rounded-4" style="background-color: white; border: 2px solid #E6D8B8; color: #8D4A23;">
                    Tu carrito está vacío. ¡Es hora de agregar buena música!
                </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 rounded-4" style="border: 2px solid #E6D8B8 !important;">
                <div class="card-header text-center p-3" style="background-color: #E6D8B8; border-bottom: 2px solid #C06C38 !important;">
                    <h4 class="m-0" style="font-family: 'Righteous', sans-serif; color: #504E76;">Resumen</h4>
                </div>
                <div class="card-body p-4 bg-white">
                    <div class="d-flex justify-content-between mb-3 fw-bold fs-5" style="color: #C06C38;">
                        <span>Total:</span>
                        <span>$<?php echo number_format($total, 2); ?></span>
                    </div>
                    <?php if ($total > 0): ?>
                        <div class="d-grid">
                            <a href="checkout.php" class="btn text-white fw-bold shadow-sm p-2 fs-5" style="background-color: #C06C38; font-family: 'Righteous', sans-serif; letter-spacing: 1px;" onmouseover="this.style.backgroundColor='#8D4A23'" onmouseout="this.style.backgroundColor='#C06C38'">COMPRAR</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php 
$stmt->close();
$conn->close(); 
?>