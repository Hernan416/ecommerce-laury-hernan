<?php
session_start();

// 1. Seguridad: Verificar sesión y que se haya enviado un ID de factura válido
if (!isset($_SESSION['usuario_id'])) { die("Acceso denegado. Por favor inicia sesión."); }
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { die("Factura no válida."); }

$host = getenv("DB_HOST") ?: "localhost"; $user = "root"; $pass = ""; $db = "the_drop_vinyls";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) { die("Error de conexión: " . $conn->connect_error); }

$id_usuario = $_SESSION['usuario_id'];
$id_factura = intval($_GET['id']);

// 2. Obtener datos de la factura cruzados con los del usuario
$stmt_fac = $conn->prepare("SELECT f.id, f.precio_final, f.fecha_emision, u.nombre, u.apellido, u.correo, u.direccion 
                            FROM facturas f 
                            JOIN usuarios u ON f.id_usuario = u.id 
                            WHERE f.id = ? AND f.id_usuario = ?");
$stmt_fac->bind_param("ii", $id_factura, $id_usuario); 
$stmt_fac->execute();
$resultado_fac = $stmt_fac->get_result();

if ($resultado_fac->num_rows === 0) {
    die("Error: La factura no existe o no tienes permisos para verla.");
}
$factura = $resultado_fac->fetch_assoc();
$stmt_fac->close();

// --- CONSULTA A LA API OFICIAL DESDE PHP ---
$tasa_cambio = 40.50; // Valor por defecto por si la API falla
$api_url = "https://ve.dolarapi.com/v1/dolares/oficial";

// Usamos cURL para llamar a la misma API que usas en el carrito
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 3); // Máximo 3 segundos de espera
$respuesta = curl_exec($ch);
curl_close($ch);

if ($respuesta) {
    $datos = json_decode($respuesta, true);
    if (isset($datos['promedio'])) {
        $tasa_cambio = $datos['promedio'];
    }
}

// Calculamos el total en Bs
$total_bs = $factura['precio_final'] * $tasa_cambio;

// 3. Obtener los productos exactos que compró en esa factura
$stmt_det = $conn->prepare("SELECT fd.cantidad, fd.precio_unitario, p.nombre_producto, p.artista 
                            FROM factura_detalles fd 
                            JOIN productos p ON fd.id_producto = p.id 
                            WHERE fd.id_factura = ?");
$stmt_det->bind_param("i", $id_factura);
$stmt_det->execute();
$detalles = $stmt_det->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include(__DIR__ . '/../../assets/head.php'); ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura_00<?php echo $factura['id']; ?>_TheDropVinyls</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Estilos visuales para verla en pantalla */
        body { background-color: #e9ecef; color: #333; font-family: Arial, sans-serif; }
        .hoja-factura { max-width: 800px; margin: 40px auto; background: #fff; padding: 50px; box-shadow: 0 0 15px rgba(0,0,0,0.1); border-radius: 8px; }
        
        .encabezado { border-bottom: 2px solid #504E76; padding-bottom: 20px; margin-bottom: 30px; }
        .logo-text { font-size: 28px; font-weight: bold; color: #504E76; }
        .datos-cliente { background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 30px; border-left: 4px solid #C06C38; }
        
        table th { background-color: #504E76 !important; color: white !important; }
        .total-row { font-size: 1.2rem; font-weight: bold; background-color: #f8f9fa; }
        .tasa-row { font-size: 0.85rem; background-color: #fff; }

        /* Estilos EXCLUSIVOS para cuando se imprime a PDF */
        @media print {
            body { background-color: #fff; margin: 0; }
            .hoja-factura { box-shadow: none; margin: 0; padding: 0; max-width: 100%; border-radius: 0; }
            .btn-imprimir { display: none !important; } 
            table th { background-color: #eee !important; color: #333 !important; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>

<body onload="window.print()">

<div class="hoja-factura">
    
    <div class="text-end mb-3 btn-imprimir">
        <button onclick="window.print()" class="btn btn-secondary">Imprimir / Guardar PDF</button>
    </div>

    <div class="row encabezado align-items-center">
        <div class="col-sm-6">
            <div class="logo-text">The Drop Vinyls</div>
            <p class="mb-0 text-muted">Juangriego, Nueva Esparta, Venezuela</p>
            <p class="mb-0 text-muted">contacto@thedropvinyls.com</p>
        </div>
        <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
            <h2 style="color: #C06C38; font-weight: bold;">FACTURA</h2>
            <p class="mb-0"><strong>N° de Factura:</strong> #00<?php echo $factura['id']; ?></p>
            <p class="mb-0"><strong>Fecha:</strong> <?php echo date("d/m/Y", strtotime($factura['fecha_emision'])); ?></p>
        </div>
    </div>

    <div class="datos-cliente">
        <h5 style="color: #504E76; margin-bottom: 10px;">Facturado a:</h5>
        <p class="mb-1"><strong>Nombre:</strong> <?php echo htmlspecialchars($factura['nombre'] . " " . $factura['apellido']); ?></p>
        <p class="mb-1"><strong>Correo:</strong> <?php echo htmlspecialchars($factura['correo']); ?></p>
        <p class="mb-0"><strong>Dirección de Entrega:</strong> <?php echo htmlspecialchars($factura['direccion'] ?? 'No especificada'); ?></p>
    </div>

    <table class="table table-bordered mb-5">
        <thead>
            <tr>
                <th style="width: 50%;">Artículo / Vinilo</th>
                <th class="text-center">Cantidad</th>
                <th class="text-end">Precio Unit.</th>
                <th class="text-end">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            while($item = $detalles->fetch_assoc()): 
                $subtotal = $item['cantidad'] * $item['precio_unitario'];
            ?>
            <tr>
                <td>
                    <strong><?php echo htmlspecialchars($item['nombre_producto']); ?></strong><br>
                    <small class="text-muted"><?php echo htmlspecialchars($item['artista']); ?></small>
                </td>
                <td class="text-center align-middle"><?php echo $item['cantidad']; ?></td>
                <td class="text-end align-middle">$<?php echo number_format($item['precio_unitario'], 2); ?></td>
                <td class="text-end align-middle">$<?php echo number_format($subtotal, 2); ?></td>
            </tr>
            <?php endwhile; ?>
            
            <tr class="total-row">
                <td colspan="3" class="text-end text-uppercase" style="color: #504E76;">Total Pagado (USD):</td>
                <td class="text-end" style="color: #C06C38;">$<?php echo number_format($factura['precio_final'], 2); ?></td>
            </tr>
            <tr class="total-row">
                <td colspan="3" class="text-end text-uppercase" style="color: #504E76;">Total Pagado (Bs):</td>
                <td class="text-end" style="color: #C06C38;">Bs <?php echo number_format($total_bs, 2, ',', '.'); ?></td>
            </tr>
            <tr class="tasa-row border-white">
                <td colspan="4" class="text-end text-muted pt-1 border-0">
                    <em>Calculado a una tasa oficial BCV de Bs <?php echo number_format($tasa_cambio, 2, ',', '.'); ?></em>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="text-center mt-5 pt-4" style="border-top: 1px solid #ddd;">
        <p class="fw-bold" style="color: #504E76;">¡Gracias por tu compra y por apoyar la cultura del vinilo!</p>
        <p class="text-muted small">Este es un comprobante de compra válido generado electrónicamente.</p>
    </div>

</div>

</body>
</html>
<?php 
$stmt_det->close();
$conn->close(); 
?>
