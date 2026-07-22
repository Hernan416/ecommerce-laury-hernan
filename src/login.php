<?php
session_start();

$host = getenv("DB_HOST") ?: "localhost";
$user = "root";
$pass = "";
$db = "the_drop_vinyls";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST['correo'];
    $contrasena_ingresada = $_POST['contrasena'];

    $stmt = $conn->prepare("SELECT id, nombre, apellido, contrasena, rol, estado_cuenta, intentos_fallidos, bloqueado_hasta FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();

        if ($usuario['estado_cuenta'] === 'suspendido') {
            $error = "Error de autenticación o cuenta suspendida.";
        } else {
            // Verificar si está bloqueado temporalmente
            $bloqueado = false;
            if ($usuario['bloqueado_hasta'] !== null) {
                $bloqueado_hasta = new DateTime($usuario['bloqueado_hasta']);
                $ahora = new DateTime();
                if ($ahora < $bloqueado_hasta) {
                    $bloqueado = true;
                    $error = "Demasiados intentos fallidos. Intente nuevamente más tarde.";
                } else {
                    // El tiempo de bloqueo ya pasó, resetear intentos
                    $reset_stmt = $conn->prepare("UPDATE usuarios SET intentos_fallidos = 0, bloqueado_hasta = NULL WHERE id = ?");
                    $reset_stmt->bind_param("i", $usuario['id']);
                    $reset_stmt->execute();
                    $usuario['intentos_fallidos'] = 0;
                }
            }

            if (!$bloqueado) {
                if ($contrasena_ingresada === $usuario['contrasena']) {
                    // Éxito: resetear intentos fallidos
                    $reset_stmt = $conn->prepare("UPDATE usuarios SET intentos_fallidos = 0, bloqueado_hasta = NULL WHERE id = ?");
                    $reset_stmt->bind_param("i", $usuario['id']);
                    $reset_stmt->execute();

                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['usuario_nombre'] = $usuario['nombre'];
                    $_SESSION['usuario_rol'] = $usuario['rol'];

                    if ($usuario['rol'] == 'admin') {
                        header("Location: ./admin/admin_dashboard.php");
                    } else {
                        header("Location: ./Usuarios/index.php"); // Redirige a Usuarios/index.php
                    }
                    exit();
                } else {
                    // Fallo: Incrementar intentos fallidos
                    $intentos = $usuario['intentos_fallidos'] + 1;
                    $bloqueado_hasta = null;
                    if ($intentos >= 5) {
                        $bloqueado_hasta = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                    }
                    
                    $update_stmt = $conn->prepare("UPDATE usuarios SET intentos_fallidos = ?, bloqueado_hasta = ? WHERE id = ?");
                    $update_stmt->bind_param("isi", $intentos, $bloqueado_hasta, $usuario['id']);
                    $update_stmt->execute();

                    $error = "Error de autenticación. Credenciales inválidas.";
                }
            }
        }
    } else {
        $error = "Error de autenticación. Credenciales inválidas.";
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <?php include(__DIR__ . '/assets/head.php'); ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - The Drop Vinyls</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Righteous&display=swap"
        rel="stylesheet">
</head>

<body class="d-flex align-items-center justify-content-center vh-100"
    style="background-color: #FDF8E2; font-family: 'Fredoka', sans-serif; color: #504E76;">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg border-0 rounded-4"
                    style="border: 2px solid #E6D8B8 !important; overflow: hidden;">

                    <div class="card-header text-center p-4 border-bottom-0"
                        style="background-color: #E6D8B8; border-bottom: 2px solid #C06C38 !important;">
                        <img src="assets/LOGO.png" alt="Logo The Drop Vinyls"
                            style="height: 40px; margin-right: 12px; object-fit: contain;">
                        <h1 class="display-6 m-0" style="font-family: 'Righteous', sans-serif; color: #504E76;">The Drop
                            Vinyls</h1>
                        <p class="mt-2 fw-medium mb-0" style="color: #8D4A23;">La música de hoy, el ritual de siempre.
                        </p>
                    </div>

                    <div class="card-body p-4 p-md-5 bg-white">
                        <h3 class="text-center mb-4" style="font-family: 'Righteous', sans-serif; color: #504E76;">
                            Iniciar Sesión</h3>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger text-center rounded-3 shadow-sm" role="alert">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <form action="login.php" method="POST">
                            <div class="mb-4">
                                <label for="correo" class="form-label fw-medium" style="color: #504E76;">Correo
                                    Electrónico</label>
                                <input type="email" class="form-control form-control-lg border-secondary-subtle"
                                    id="correo" name="correo" placeholder="ejemplo@correo.com" required
                                    style="font-family: 'Fredoka', sans-serif;">
                            </div>

                            <div class="mb-4">
                                <label for="contrasena" class="form-label fw-medium"
                                    style="color: #504E76;">Contraseña</label>
                                <input type="password" class="form-control form-control-lg border-secondary-subtle"
                                    id="contrasena" name="contrasena" placeholder="********" required
                                    style="font-family: 'Fredoka', sans-serif;">
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-lg text-white shadow-sm"
                                    style="background-color: #C06C38; font-family: 'Righteous', sans-serif; letter-spacing: 1px;"
                                    onmouseover="this.style.backgroundColor='#8D4A23'"
                                    onmouseout="this.style.backgroundColor='#C06C38'">ENTRAR</button>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <p class="mb-0">¿Aún no tienes cuenta? <a href="registro.php"
                                    class="text-decoration-none fw-bold" style="color: #C06C38;"
                                    onmouseover="this.style.color='#8D4A23'"
                                    onmouseout="this.style.color='#C06C38'">Regístrate aquí</a></p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
