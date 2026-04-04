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
$compra_exitosa = false;

// Si el usuario envió el formulario de confirmación de envío
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Obtener la dirección actual del usuario para autocompletar el formulario
    $stmt_user = $conn->prepare("SELECT direccion FROM usuarios WHERE id = ?");
    $stmt_user->bind_param("i", $id_usuario);
    $stmt_user->execute();
    $datos_usuario = $stmt_user->get_result()->fetch_assoc();
    $stmt_user->close();
        
    // 1. Calcular total del carrito actual
    $stmt_total = $conn->prepare("SELECT SUM(p.precio * c.cantidad) AS total FROM carrito c JOIN productos p ON c.id_producto = p.id WHERE c.id_usuario = ?");
    $stmt_total->bind_param("i", $id_usuario);
    $stmt_total->execute();
    $result_total = $stmt_total->get_result()->fetch_assoc();
    $precio_final = $result_total['total'] ? $result_total['total'] : 0;
    $stmt_total->close();

    if ($precio_final > 0) {
        // 2. Crear la factura maestra
        $stmt_factura = $conn->prepare("INSERT INTO facturas (id_usuario, precio_final) VALUES (?, ?)");
        $stmt_factura->bind_param("id", $id_usuario, $precio_final);
        $stmt_factura->execute();
        $id_factura_nueva = $conn->insert_id; // Obtenemos el ID de la factura recién creada
        $stmt_factura->close();

        // 3. Mover items del carrito a factura_detalles
        $sql_detalles = "INSERT INTO factura_detalles (id_factura, id_producto, cantidad, precio_unitario) 
                         SELECT ?, c.id_producto, c.cantidad, p.precio 
                         FROM carrito c JOIN productos p ON c.id_producto = p.id 
                         WHERE c.id_usuario = ?";
        $stmt_detalles = $conn->prepare($sql_detalles);
        $stmt_detalles->bind_param("ii", $id_factura_nueva, $id_usuario);
        $stmt_detalles->execute();
        $stmt_detalles->close();

        // 4. Vaciar el carrito de este usuario
        $stmt_vaciar = $conn->prepare("DELETE FROM carrito WHERE id_usuario = ?");
        $stmt_vaciar->bind_param("i", $id_usuario);
        $stmt_vaciar->execute();
        $stmt_vaciar->close();

        $compra_exitosa = true;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - The Drop Vinyls</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Righteous&display=swap" rel="stylesheet">
    
    <?php if ($compra_exitosa): ?>
        <meta http-equiv="refresh" content="4;url=../index.php">
    <?php endif; ?>
</head>
<body class="d-flex flex-column min-vh-100" style="background-color: #FDF8E2; font-family: 'Fredoka', sans-serif; color: #504E76;">

<nav class="navbar navbar-expand-lg shadow-sm" style="background-color: #504E76; padding: 15px 0;">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="../index.php" style="font-family: 'Righteous', sans-serif; color: #FDF8E2; font-size: 1.8rem; letter-spacing: 1px;">
            <img src="../assets/LOGO.png" alt="Logo" style="height: 40px; margin-right: 12px; object-fit: contain;">
            The Drop Vinyls
        </a>
    </div>
</nav>

<div class="container my-auto py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            
            <?php if ($compra_exitosa): ?>
                <div class="card shadow-lg border-0 rounded-4 text-center p-5 bg-white" style="border: 2px solid #E6D8B8 !important;">
                    <div class="mb-4">
                        <span style="font-size: 5rem; color: #C06C38;">✔️</span>
                    </div>
                    <h1 class="display-5 mb-3" style="font-family: 'Righteous', sans-serif; color: #504E76;">¡Muchas gracias por tu compra!</h1>
                    <p class="fs-5 fw-medium" style="color: #8D4A23;">Tu pedido está siendo procesado.</p>
                    <p class="text-muted">Serás redirigido a la tienda en unos segundos...</p>
                    <div class="mt-4">
                        <div class="spinner-border" style="color: #E6D8B8;" role="status">
                            <span class="visually-hidden">Redirigiendo...</span>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <div class="card shadow-sm border-0 rounded-4" style="border: 2px solid #E6D8B8 !important;">
                    <div class="card-header text-center p-4" style="background-color: #E6D8B8; border-bottom: 2px solid #C06C38 !important;">
                        <h3 class="m-0" style="font-family: 'Righteous', sans-serif; color: #504E76;">Datos de Envío</h3>
                    </div>
                    <div class="card-body p-4 p-md-5 bg-white">
                        <form action="checkout.php" method="POST">
                            
                            <div class="mb-3">
                             <label class="form-label fw-medium" style="color: #504E76;">Dirección de Entrega</label>
                             <input type="text" class="form-control border-secondary-subtle" required placeholder="Ej. Calle Principal 123" value="<?php echo htmlspecialchars($datos_usuario['direccion'] ?? ''); ?>">
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium" style="color: #504E76;">Ciudad</label>
                                    <input type="text" class="form-control border-secondary-subtle" required>
                                </div>
                                <div class="col-md-6 mt-3 mt-md-0">
                                    <label class="form-label fw-medium" style="color: #504E76;">Código Postal</label>
                                    <input type="text" class="form-control border-secondary-subtle" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-medium" style="color: #504E76;">Teléfono de Contacto</label>
                                <input type="tel" class="form-control border-secondary-subtle" required>
                            </div>

                            <div class="alert text-center fw-medium mb-4 rounded-3" style="background-color: #FDF8E2; color: #8D4A23; border: 1px dashed #C06C38;">
                                ℹ️ El pago se realizará contra entrega o a través de transferencia bancaria.
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn text-white fw-bold shadow-sm py-2 fs-5" style="background-color: #C06C38; font-family: 'Righteous', sans-serif; letter-spacing: 1px;" onmouseover="this.style.backgroundColor='#8D4A23'" onmouseout="this.style.backgroundColor='#C06C38'">CONFIRMAR PEDIDO</button>
                                <a href="carrito.php" class="btn fw-medium shadow-sm" style="background-color: #E6D8B8; color: #504E76;" onmouseover="this.style.backgroundColor='#FDF8E2'" onmouseout="this.style.backgroundColor='#E6D8B8'">Volver al Carrito</a>
                            </div>

                        </form>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>