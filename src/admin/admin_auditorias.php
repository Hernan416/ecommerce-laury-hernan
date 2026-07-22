<?php
require 'auth_admin.php';
// Asegurando la correcta conexión (ajusta si tu ruta usa $conn directamente o requiere conexion.php)
if (!isset($conn)) {
    @include '../conexion.php';
}
if (!isset($conn)) {
    $conn = new mysqli(getenv("DB_HOST") ?: "localhost", "root", "", "the_drop_vinyls");
}

// Consultar los logs seleccionando explícitamente el ID del log para que no choque con el ID del usuario
$sql_logs = "SELECT a.id AS id_log, a.accion, a.fecha, u.nombre, u.correo 
             FROM audit_logs a 
             JOIN usuarios u ON a.id_usuario = u.id 
             ORDER BY a.fecha DESC";
$resultado_logs = $conn->query($sql_logs);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auditoría del Sistema - The Drop Vinyls</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Righteous&display=swap"
        rel="stylesheet">
</head>

<body style="background-color: #FDF8E2; font-family: 'Fredoka', sans-serif; color: #504E76;">

    <!-- Navbar -->
    <nav class="navbar shadow-sm" style="background-color: #504E76;">
        <div class="container">
            <span class="navbar-brand text-white" style="font-family: 'Righteous';">
                Gestion de Auditorías
            </span>
            <a href="admin_dashboard.php" class="btn text-white" style="background-color: #C06C38;">Volver al Panel</a>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <div class="container my-5">
        <h2 class="mb-4 text-center" style="font-family: 'Righteous'; color: #504E76;">Registro de Actividad</h2>

        <!-- Tabla de Auditorías -->
        <div class="card shadow-sm border-0 rounded-4 p-4" style="background-color: #ffffff; border: 2px solid #E6D8B8;">
            <h4 class="mb-4" style="font-family: 'Righteous'; color: #504E76;">Historial de Acciones de Administradores</h4>
            
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead style="background-color: #504E76; color: white;">
                        <tr>
                            <th>ID</th>
                            <th>Administrador</th>
                            <th>Correo</th>
                            <th>Acción Realizada</th>
                            <th>Fecha y Hora</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($resultado_logs && $resultado_logs->num_rows > 0): ?>
                            <?php while ($log = $resultado_logs->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $log['id_log']; ?></td>
                                    <td class="fw-bold"><?php echo htmlspecialchars($log['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($log['correo']); ?></td>
                                    <td><?php echo htmlspecialchars($log['accion']); ?></td>
                                    <td><small class="text-muted"><?php echo $log['fecha']; ?></small></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No hay registros de auditoría todavía.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</body>

</html>
