<?php
require 'auth_admin.php';
require '../conexion.php';
// Asegúrate de incluir tu conexión a la base de datos (ajusta el nombre si tu archivo se llama diferente, ej. conexion.php o db.php)

// 1. Consulta para métricas generales de facturas
$sql_totales = "SELECT COUNT(*) as total_facturas, SUM(precio_final) as ingresos_totales FROM facturas";
$res_totales = $conn->query($sql_totales)->fetch_assoc();

// 2. Consulta para productos más vendidos usando tus relaciones de tablas
$sql_mas_vendidos = "SELECT p.nombre_producto, p.artista, SUM(fd.cantidad) as total_vendidos 
                     FROM factura_detalles fd 
                     JOIN productos p ON fd.id_producto = p.id 
                     GROUP BY fd.id_producto 
                     ORDER BY total_vendidos DESC 
                     LIMIT 5";
$res_vendidos = $conn->query($sql_mas_vendidos);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - The Drop Vinyls</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Righteous&display=swap"
        rel="stylesheet">
</head>

<body style="background-color: #FDF8E2; font-family: 'Fredoka', sans-serif; color: #504E76;">

    <!-- Navbar -->
    <nav class="navbar shadow-sm" style="background-color: #504E76;">
        <div class="container">
            <span class="navbar-brand text-white" style="font-family: 'Righteous';">
                Gestion de Reportes
            </span>
            <a href="admin_dashboard.php" class="btn text-white" style="background-color: #C06C38;">Volver al Panel</a>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <div class="container my-5">
        <h2 class="mb-4 text-center" style="font-family: 'Righteous'; color: #504E76;">Métricas de Negocio</h2>

        <!-- Tarjetas de Resumen General -->
        <div class="row g-4 mb-5 justify-content-center">
            <div class="col-12 col-md-5">
                <div class="card shadow-sm border-0 rounded-4 p-4 text-center" style="border: 2px solid #E6D8B8; background-color: #ffffff;">
                    <h5 style="color: #C06C38; font-weight: bold;">Total de Ventas Realizadas</h5>
                    <p class="display-6 fw-bold mt-2" style="color: #504E76;"><?php echo $res_totales['total_facturas'] ?? 0; ?></p>
                </div>
            </div>
            <div class="col-12 col-md-5">
                <div class="card shadow-sm border-0 rounded-4 p-4 text-center" style="border: 2px solid #E6D8B8; background-color: #ffffff;">
                    <h5 style="color: #C06C38; font-weight: bold;">Ingresos Totales</h5>
                    <p class="display-6 fw-bold mt-2" style="color: #504E76;">$<?php echo number_format($res_totales['ingresos_totales'] ?? 0, 2); ?></p>
                </div>
            </div>
        </div>

        <!-- Tabla de Productos Más Vendidos -->
        <div class="card shadow-sm border-0 rounded-4 p-4" style="background-color: #ffffff; border: 2px solid #E6D8B8;">
            <h4 class="mb-4" style="font-family: 'Righteous'; color: #504E76;">Top 5 Productos Más Vendidos</h4>
            
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead style="background-color: #504E76; color: white;">
                        <tr>
                            <th>Producto</th>
                            <th>Artista</th>
                            <th class="text-center">Unidades Vendidas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($res_vendidos && $res_vendidos->num_rows > 0): ?>
                            <?php while ($row = $res_vendidos->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-bold"><?php echo htmlspecialchars($row['nombre_producto']); ?></td>
                                    <td><?php echo htmlspecialchars($row['artista']); ?></td>
                                    <td class="text-center"><span class="badge rounded-pill px-3 py-2" style="background-color: #C06C38; font-size: 0.9rem;"><?php echo $row['total_vendidos']; ?></span></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">No hay registros de ventas todavía.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</body>

</html>