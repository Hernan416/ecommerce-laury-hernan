<?php
session_start();

$host = "localhost";
$user = "root"; 
$pass = "";     
$db   = "the_drop_vinyls";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$error = "";
$exito = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $conn->real_escape_string(trim($_POST['nombre']));
    $apellido = $conn->real_escape_string(trim($_POST['apellido']));
    $correo = $conn->real_escape_string(trim($_POST['correo']));
    $contrasena = $conn->real_escape_string($_POST['contrasena']);
    $direccion = $conn->real_escape_string(trim($_POST['direccion']));

    // 1. Verificamos si el correo ya existe en la base de datos
    $check_email = $conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
    $check_email->bind_param("s", $correo);
    $check_email->execute();
    $resultado_email = $check_email->get_result();

    if ($resultado_email->num_rows > 0) {
        $error = "Este correo ya está registrado. Por favor, inicia sesión.";
    } else {
        // 2. Si no existe, insertamos el nuevo usuario
        // Nota: Le asignamos el rol 'cliente' por defecto y la fecha actual
        $sql_insert = "INSERT INTO usuarios (nombre, apellido, correo, contrasena, rol, direccion, fecha_registro) VALUES (?, ?, ?, ?, 'cliente', ?, NOW())";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("sssss", $nombre, $apellido, $correo, $contrasena, $direccion);

        if ($stmt_insert->execute()) {
            $exito = "¡Cuenta creada con éxito! Ya puedes iniciar sesión.";
        } else {
            $error = "Hubo un error al crear la cuenta: " . $conn->error;
        }
        $stmt_insert->close();
    }
    $check_email->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - The Drop Vinyls</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Righteous&display=swap" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 py-5" style="background-color: #FDF8E2; font-family: 'Fredoka', sans-serif; color: #504E76;">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg border-0 rounded-4" style="border: 2px solid #E6D8B8 !important; overflow: hidden;">
                
                <div class="card-header text-center p-4 border-bottom-0" style="background-color: #E6D8B8; border-bottom: 2px solid #C06C38 !important;">
                    <img src="Usuarios/assets/LOGO.png" alt="Logo The Drop Vinyls" class="img-fluid mb-3" style="max-height: 80px;">
                    <h1 class="display-6 m-0" style="font-family: 'Righteous', sans-serif; color: #504E76;">The Drop Vinyls</h1>
                    <p class="mt-2 fw-medium mb-0" style="color: #8D4A23;">Únete a la comunidad del vinilo.</p>
                </div>

                <div class="card-body p-4 p-md-5 bg-white">
                    <h3 class="text-center mb-4" style="font-family: 'Righteous', sans-serif; color: #504E76;">Crear Cuenta</h3>

                    <?php if(!empty($error)): ?>
                        <div class="alert alert-danger text-center rounded-3 shadow-sm" role="alert">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <?php if(!empty($exito)): ?>
                        <div class="alert alert-success text-center rounded-3 shadow-sm" role="alert">
                            <?php echo $exito; ?> <br>
                            <a href="login.php" class="alert-link">Haz clic aquí para iniciar sesión</a>
                        </div>
                    <?php else: ?>

                        <form action="registro.php" method="POST">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="nombre" class="form-label fw-medium" style="color: #504E76;">Nombre</label>
                                    <input type="text" class="form-control border-secondary-subtle" id="nombre" name="nombre" required>
                                </div>
                                <div class="col-md-6 mt-3 mt-md-0">
                                    <label for="apellido" class="form-label fw-medium" style="color: #504E76;">Apellido</label>
                                    <input type="text" class="form-control border-secondary-subtle" id="apellido" name="apellido" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="correo" class="form-label fw-medium" style="color: #504E76;">Correo Electrónico</label>
                                <input type="email" class="form-control border-secondary-subtle" id="correo" name="correo" placeholder="ejemplo@correo.com" required>
                            </div>

                            <div class="mb-3">
                                <label for="direccion" class="form-label fw-medium" style="color: #504E76;">Dirección de Entrega</label>
                                <input type="text" class="form-control border-secondary-subtle" id="direccion" name="direccion" placeholder="Ej. Calle 123, Ciudad" required>
                            </div>

                            <div class="mb-4">
                                <label for="contrasena" class="form-label fw-medium" style="color: #504E76;">Contraseña</label>
                                <input type="password" class="form-control border-secondary-subtle" id="contrasena" name="contrasena" placeholder="********" required>
                                <div class="form-text">Mínimo 8 caracteres para tu seguridad.</div>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-lg text-white shadow-sm" style="background-color: #C06C38; font-family: 'Righteous', sans-serif; letter-spacing: 1px;" onmouseover="this.style.backgroundColor='#8D4A23'" onmouseout="this.style.backgroundColor='#C06C38'">REGISTRARSE</button>
                            </div>
                        </form>

                    <?php endif; ?>

                    <div class="text-center mt-4">
                        <p class="mb-0">¿Ya tienes una cuenta? <a href="login.php" class="text-decoration-none fw-bold" style="color: #C06C38;" onmouseover="this.style.color='#8D4A23'" onmouseout="this.style.color='#C06C38'">Inicia sesión aquí</a></p>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>