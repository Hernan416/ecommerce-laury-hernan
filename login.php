<?php
// Iniciar la sesión para guardar los datos del usuario si el login es correcto
session_start();

// 1. Conexión a la base de datos
$host = "localhost";
$user = "root"; // Usuario por defecto en XAMPP
$pass = "";     // Contraseña por defecto en XAMPP (vacía)
$db   = "the_drop_vinyls";

$conn = new mysqli($host, $user, $pass, $db);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$error = "";

// 2. Lógica para procesar el formulario cuando el usuario hace clic en "Entrar"
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST['correo'];
    $contrasena_ingresada = $_POST['contrasena'];

    // Preparamos la consulta para evitar inyecciones SQL
    $stmt = $conn->prepare("SELECT id, nombre, apellido, contrasena, rol FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();
        
        // NOTA DE DESARROLLO: 
        // Para este entorno de prueba donde las contraseñas están en texto plano ('123456'), 
        // usamos una comparación directa. 
        // En PRODUCCIÓN, debes cambiar la línea de abajo por: 
        // if (password_verify($contrasena_ingresada, $usuario['contrasena'])) {
        
        if ($contrasena_ingresada === $usuario['contrasena']) {
            // Contraseña correcta: Guardamos datos en variables de sesión
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_rol'] = $usuario['rol'];

            // Redirigimos dependiendo del rol
            if ($usuario['rol'] == 'admin') {
                header("Location: admin_dashboard.php"); // Página del administrador (por crear)
            } else {
                header("Location: index.php"); // Página principal de la tienda (por crear)
            }
            exit();
        } else {
            $error = "Contraseña incorrecta.";
        }
    } else {
        $error = "No existe una cuenta con este correo.";
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - The Drop Vinyls</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Righteous&display=swap" rel="stylesheet">

    <style>
        :root {
            --color-purple: #504E76;
            --color-brown: #8D4A23;
            --color-orange: #C06C38;
            --color-light-cream: #FDF8E2;
            --color-dark-cream: #E6D8B8;
            
            --font-title: 'Righteous', sans-serif;
            --font-body: 'Fredoka', sans-serif;
        }

        body {
            background-color: var(--color-light-cream);
            font-family: var(--font-body);
            color: var(--color-purple);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(80, 78, 118, 0.1);
            overflow: hidden;
            border: 2px solid var(--color-dark-cream);
        }

        .login-header {
            background-color: var(--color-dark-cream);
            padding: 30px;
            text-align: center;
            border-bottom: 2px solid var(--color-orange);
        }

        .brand-title {
            font-family: var(--font-title);
            color: var(--color-purple);
            font-size: 2.5rem;
            margin: 0;
            letter-spacing: 1px;
        }

        .brand-subtitle {
            color: var(--color-brown);
            font-size: 1rem;
            font-weight: 500;
        }

        .btn-custom {
            background-color: var(--color-orange);
            color: white;
            font-family: var(--font-title);
            letter-spacing: 1px;
            border: none;
            padding: 10px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .btn-custom:hover {
            background-color: var(--color-brown);
            color: var(--color-light-cream);
        }

        .form-control:focus {
            border-color: var(--color-orange);
            box-shadow: 0 0 0 0.25rem rgba(192, 108, 56, 0.25);
        }

        .form-label {
            font-weight: 500;
            color: var(--color-purple);
        }

        .text-orange {
            color: var(--color-orange) !important;
            text-decoration: none;
            font-weight: 600;
        }
        
        .text-orange:hover {
            color: var(--color-brown) !important;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="login-card">
                
                <div class="login-header">
                    <h1 class="brand-title">The Drop Vinyls</h1>
                    <p class="brand-subtitle mt-2">La música de hoy, el ritual de siempre.</p>
                </div>

                <div class="p-4 p-md-5">
                    <h3 class="mb-4 text-center" style="font-family: var(--font-title); color: var(--color-purple);">Iniciar Sesión</h3>

                    <?php if(!empty($error)): ?>
                        <div class="alert alert-danger text-center" role="alert">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form action="login.php" method="POST">
                        <div class="mb-4">
                            <label for="correo" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control form-control-lg" id="correo" name="correo" placeholder="ejemplo@correo.com" required>
                        </div>

                        <div class="mb-4">
                            <label for="contrasena" class="form-label">Contraseña</label>
                            <input type="password" class="form-control form-control-lg" id="contrasena" name="contrasena" placeholder="********" required>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-custom btn-lg">ENTRAR</button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <p class="mb-0">¿Aún no tienes cuenta? <a href="#" class="text-orange">Regístrate aquí</a></p>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>