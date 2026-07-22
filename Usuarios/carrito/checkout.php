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

// Obtener la dirección actual del usuario para autocompletar el formulario o usarla directamente
$stmt_user = $conn->prepare("SELECT direccion, ciudad, estado_provincia, codigo_postal, telefono FROM usuarios WHERE id = ?");
$stmt_user->bind_param("i", $id_usuario);
$stmt_user->execute();
$datos_usuario = $stmt_user->get_result()->fetch_assoc();
$stmt_user->close();

$tiene_direccion = !empty($datos_usuario['direccion']) && !empty($datos_usuario['ciudad']);

// --- INICIO PATRÓN STATE ---
interface OrderState {
    public function process($factura_id, $conn);
}

class CreatedState implements OrderState {
    public function process($factura_id, $conn) {
        $conn->query("UPDATE facturas SET estado = 'creado' WHERE id = $factura_id");
    }
}

class PaidState implements OrderState {
    public function process($factura_id, $conn) {
        $conn->query("UPDATE facturas SET estado = 'pagado' WHERE id = $factura_id");
    }
}

class OrderContext {
    private $state;
    public function setState(OrderState $state) {
        $this->state = $state;
    }
    public function processState($factura_id, $conn) {
        $this->state->process($factura_id, $conn);
    }
}
// --- FIN PATRÓN STATE ---

// --- INICIO PATRÓN FACADE ---
class PaymentFacade {
    public function processPayment($amount, $user_id) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        // Construimos la URL al mock de la API
        $base_dir = dirname(dirname(dirname($_SERVER['REQUEST_URI']))); 
        $url = "$protocol://" . $_SERVER['HTTP_HOST'] . rtrim($base_dir, '/') . "/api/v1/pedidos.php";
        
        $data = json_encode(["monto" => $amount, "id_usuario" => $user_id]);
        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => $data,
                'timeout' => 5
            ]
        ];
        $context  = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);
        
        if ($result === FALSE) {
            // Fallback si no se puede resolver la petición HTTP
            file_put_contents(__DIR__ . '/../../payment_log.txt', "[" . date('Y-m-d H:i:s') . "] API Fallback (Sin Red): Pago aprobado para usuario ID $user_id por monto $$amount\n", FILE_APPEND);
            sleep(1);
            return true;
        }
        
        $response = json_decode($result, true);
        return isset($response['status']) && $response['status'] == 'success';
    }
}
// --- FIN PATRÓN FACADE ---

// Si el usuario envió el formulario de confirmación de envío
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validar el inventario antes de llamar a la pasarela
    $inventario_valido = true;
    $stmt_check = $conn->prepare("SELECT p.stock, c.cantidad, p.nombre_producto FROM carrito c JOIN productos p ON c.id_producto = p.id WHERE c.id_usuario = ?");
    $stmt_check->bind_param("i", $id_usuario);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();
    while ($row_check = $res_check->fetch_assoc()) {
        if ($row_check['stock'] < $row_check['cantidad']) {
            $inventario_valido = false;
            $error_inventario = "Lo sentimos, el producto '" . $row_check['nombre_producto'] . "' se agotó mientras realizabas la compra.";
            break;
        }
    }
    $stmt_check->close();

    if ($inventario_valido) {
        // Calcular total del carrito actual
        $stmt_total = $conn->prepare("SELECT SUM(p.precio * c.cantidad) AS total FROM carrito c JOIN productos p ON c.id_producto = p.id WHERE c.id_usuario = ?");
        $stmt_total->bind_param("i", $id_usuario);
        $stmt_total->execute();
        $result_total = $stmt_total->get_result()->fetch_assoc();
        $subtotal = $result_total['total'] ? $result_total['total'] : 0;
        $stmt_total->close();

        if ($subtotal > 0) {
            // Demostración de Patrón Strategy y Composición (SOLID)
            require_once __DIR__ . '/../../Classes/DiscountStrategy.php';
            require_once __DIR__ . '/../../Classes/CheckoutCalculator.php';
            
            // Usamos la estrategia de descuento por volumen (10% si es mayor a 100)
            $strategy = new \Classes\BulkDiscountStrategy();
            $calculator = new \Classes\CheckoutCalculator($strategy);
            $precio_final = $calculator->calculateTotal($subtotal);

            // Reservar temporalmente el stock (bloqueo por 10 min simulado al restar inmediatamente)
            $sql_stock = "UPDATE productos p JOIN carrito c ON p.id = c.id_producto SET p.stock = p.stock - c.cantidad WHERE c.id_usuario = ?";
            $stmt_stock = $conn->prepare($sql_stock);
            $stmt_stock->bind_param("i", $id_usuario);
            $stmt_stock->execute();
            $stmt_stock->close();

            // Usar la Facade para la pasarela de pagos
            $paymentFacade = new PaymentFacade();
            $pago_exitoso = $paymentFacade->processPayment($precio_final, $id_usuario);

            if ($pago_exitoso) {
                // Crear la factura
                $stmt_factura = $conn->prepare("INSERT INTO facturas (id_usuario, precio_final) VALUES (?, ?)");
                $stmt_factura->bind_param("id", $id_usuario, $precio_final);
                $stmt_factura->execute();
                $id_factura_nueva = $conn->insert_id; 
                $stmt_factura->close();

                // Usar Patrón State para actualizar a Creado y luego a Pagado
                $orderContext = new OrderContext();
                $orderContext->setState(new CreatedState());
                $orderContext->processState($id_factura_nueva, $conn);
                
                $orderContext->setState(new PaidState());
                $orderContext->processState($id_factura_nueva, $conn);

                // Mover items del carrito a factura_detalles
                $sql_detalles = "INSERT INTO factura_detalles (id_factura, id_producto, cantidad, precio_unitario) 
                                 SELECT ?, c.id_producto, c.cantidad, p.precio 
                                 FROM carrito c JOIN productos p ON c.id_producto = p.id 
                                 WHERE c.id_usuario = ?";
                $stmt_detalles = $conn->prepare($sql_detalles);
                $stmt_detalles->bind_param("ii", $id_factura_nueva, $id_usuario);
                $stmt_detalles->execute();
                $stmt_detalles->close();

                // Vaciar el carrito de este usuario
                $stmt_vaciar = $conn->prepare("DELETE FROM carrito WHERE id_usuario = ?");
                $stmt_vaciar->bind_param("i", $id_usuario);
                $stmt_vaciar->execute();
                $stmt_vaciar->close();

                $compra_exitosa = true;
            } else {
                // Si falla el pago, revertir el stock (bloqueo levantado)
                $sql_revert = "UPDATE productos p JOIN carrito c ON p.id = c.id_producto SET p.stock = p.stock + c.cantidad WHERE c.id_usuario = ?";
                $stmt_revert = $conn->prepare($sql_revert);
                $stmt_revert->bind_param("i", $id_usuario);
                $stmt_revert->execute();
                $stmt_revert->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include(__DIR__ . '/../../assets/head.php'); ?>
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
            <img src="../../assets/LOGO.png" alt="Logo The Drop Vinyls" style="height: 40px; margin-right: 12px; object-fit: contain;">
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
                        <?php if (isset($error_inventario)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error_inventario); ?></div>
                        <?php endif; ?>
                        <form action="checkout.php" method="POST">
                            
                            <?php if ($tiene_direccion): ?>
                                <div class="alert mb-4" style="background-color: #FDF8E2; border: 1px solid #E6D8B8; color: #504E76;">
                                    <h5 class="fw-bold" style="color: #8D4A23;">Dirección Confirmada</h5>
                                    <p class="mb-1">Utilizaremos tu dirección registrada en el perfil para el envío:</p>
                                    <p class="mb-0 fw-medium">
                                        <?= htmlspecialchars($datos_usuario['direccion']) ?><br>
                                        <?= htmlspecialchars($datos_usuario['ciudad']) ?>, <?= htmlspecialchars($datos_usuario['estado_provincia'] ?? '') ?> - CP: <?= htmlspecialchars($datos_usuario['codigo_postal'] ?? '') ?><br>
                                        Teléfono: <?= htmlspecialchars($datos_usuario['telefono'] ?? '') ?>
                                    </p>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning mb-4" style="color: #8D4A23; border: 1px dashed #C06C38; background-color: transparent;">
                                    ⚠️ Para proceder, por favor ingresa una dirección de envío o configúrala en tu perfil.
                                </div>

                                <div class="mb-3">
                                 <label class="form-label fw-medium" style="color: #504E76;">Dirección de Entrega</label>
                                 <input type="text" name="direccion" class="form-control border-secondary-subtle" required placeholder="Ej. Calle Principal 123" value="<?= htmlspecialchars($datos_usuario['direccion'] ?? '') ?>">
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-medium" style="color: #504E76;">Ciudad</label>
                                        <input type="text" name="ciudad" class="form-control border-secondary-subtle" required value="<?= htmlspecialchars($datos_usuario['ciudad'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6 mt-3 mt-md-0">
                                        <label class="form-label fw-medium" style="color: #504E76;">Código Postal</label>
                                        <input type="text" name="codigo_postal" class="form-control border-secondary-subtle" required value="<?= htmlspecialchars($datos_usuario['codigo_postal'] ?? '') ?>">
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-medium" style="color: #504E76;">Teléfono de Contacto</label>
                                    <input type="tel" name="telefono" class="form-control border-secondary-subtle" required value="<?= htmlspecialchars($datos_usuario['telefono'] ?? '') ?>">
                                </div>
                            <?php endif; ?>

                            <div class="alert text-center fw-medium mb-4 rounded-3" style="background-color: #FDF8E2; color: #8D4A23; border: 1px dashed #C06C38;">
                                ℹ️ El pago se procesará inmediatamente. Revisa tus artículos antes de confirmar.
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