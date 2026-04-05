<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// 1. PRIMERO NOS CONECTAMOS A LA BASE DE DATOS
$host = "localhost"; $user = "root"; $pass = ""; $db = "the_drop_vinyls";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) { die("Error de conexión: " . $conn->connect_error); }

$id_usuario = $_SESSION['usuario_id'];

// 2. LÓGICA DEL CARRITO DIRECTA A LA BASE DE DATOS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_al_carrito'])) {
    
    // Aseguramos los datos para evitar inyecciones SQL
    $id_producto = $conn->real_escape_string($_POST['id_producto']);
    $titulo_producto = $conn->real_escape_string($_POST['titulo_producto']);

    // Revisamos si este usuario ya tiene este producto exacto en su carrito
    $check_sql = "SELECT id FROM carrito WHERE id_usuario = '$id_usuario' AND id_producto = '$id_producto'";
    $resultado_check = $conn->query($check_sql);

    if ($resultado_check->num_rows > 0) {
        // Si ya existe en la base de datos, avisamos
        $_SESSION['mensaje_alerta'] = "Este vinilo ya está en tu carrito. 🎶";
    } else {
        // Si no existe, lo insertamos en la tabla carrito
        $insert_sql = "INSERT INTO carrito (id_usuario, id_producto, cantidad) VALUES ('$id_usuario', '$id_producto', 1)";
        
        if ($conn->query($insert_sql) === TRUE) {
            $_SESSION['mensaje_alerta'] = "¡'$titulo_producto' agregado al carrito! 🛒";
        } else {
            $_SESSION['mensaje_alerta'] = "Hubo un error al guardar: " . $conn->error;
        }
    }

    // Recargamos la página
    header("Location: index.php");
    exit();
}

// 3. CONSULTAS PARA MOSTRAR LOS PRODUCTOS EN LA TIENDA
$busqueda = "";
$sql = "SELECT p.*, c.nombre_categoria FROM productos p LEFT JOIN categorias c ON p.id_categoria = c.id WHERE p.stock > 0";

if (isset($_GET['buscar']) && !empty(trim($_GET['buscar']))) {
    $busqueda = $conn->real_escape_string(trim($_GET['buscar']));
    $sql .= " AND (p.nombre_producto LIKE '%$busqueda%' OR p.artista LIKE '%$busqueda%')";
}

if (isset($_GET['categoria']) && is_numeric($_GET['categoria'])) {
    $id_cat = $conn->real_escape_string($_GET['categoria']);
    $sql .= " AND p.id_categoria = $id_cat";
}

$resultado = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda - The Drop Vinyls</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Righteous&display=swap" rel="stylesheet">
</head>
<body style="background-color: #FDF8E2; font-family: 'Fredoka', sans-serif; color: #504E76;">

<?php if (isset($_SESSION['mensaje_alerta'])): ?>
    <div class="alert alert-success alert-dismissible fade show position-absolute top-0 start-50 translate-middle-x mt-3 shadow-sm" role="alert" style="z-index: 9999; background-color: #E6D8B8; border-color: #C06C38; color: #504E76; font-weight: 500;">
        <?php 
            echo $_SESSION['mensaje_alerta']; 
            unset($_SESSION['mensaje_alerta']); 
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<nav class="navbar navbar-expand-lg shadow-sm" style="background-color: #504E76; padding: 15px 0;">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.php" style="font-family: 'Righteous', sans-serif; color: #FDF8E2; font-size: 1.8rem; letter-spacing: 1px;">
            <img src="assets/LOGO.png" alt="Logo The Drop Vinyls" style="height: 40px; margin-right: 12px; object-fit: contain;">
            The Drop Vinyls perfil
        </a>
        
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContenido" aria-controls="navbarContenido" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContenido">
            <form class="d-flex mx-auto my-2 my-lg-0 w-100" action="index.php" method="GET" style="max-width: 400px;">
                <input class="form-control me-2 border-0 shadow-sm" type="search" name="buscar" placeholder="Buscar artista o álbum..." value="<?php echo htmlspecialchars($busqueda); ?>" style="font-family: 'Fredoka', sans-serif;">
                <button class="btn text-white fw-medium shadow-sm px-4" type="submit" style="background-color: #C06C38;" onmouseover="this.style.backgroundColor='#8D4A23'" onmouseout="this.style.backgroundColor='#C06C38'">Buscar</button>
            </form>

            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                <li class="nav-item dropdown me-3 mb-2 mb-lg-0">
                    <a class="nav-link dropdown-toggle text-white fw-medium" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Categorías
                    </a>
                    <ul class="dropdown-menu border-0 shadow-sm" style="background-color: #FDF8E2;">
                        <li><a class="dropdown-item fw-medium" href="index.php" style="color: #504E76;" onmouseover="this.style.backgroundColor='#E6D8B8'" onmouseout="this.style.backgroundColor='transparent'">Todas</a></li>
                        <?php
                        $res_categorias = $conn->query("SELECT * FROM categorias");
                        while($cat = $res_categorias->fetch_assoc()):
                        ?>
                        <li><a class="dropdown-item fw-medium" href="index.php?categoria=<?php echo $cat['id']; ?>" style="color: #504E76;" onmouseover="this.style.backgroundColor='#E6D8B8'" onmouseout="this.style.backgroundColor='transparent'"><?php echo htmlspecialchars($cat['nombre_categoria']); ?></a></li>
                        <?php endwhile; ?>
                    </ul>
                </li>

                <li class="nav-item me-3 mb-2 mb-lg-0">
                    <span class="text-white fw-medium">Hola, <?php echo $_SESSION['usuario_nombre']; ?></span>
                </li>
                <li class="nav-item me-2 mb-2 mb-lg-0">
                    <?php 
                        // Consultamos cuántos productos tiene ESTE usuario en su carrito
                        $count_sql = "SELECT COUNT(id) as total FROM carrito WHERE id_usuario = '$id_usuario'";
                        $res_count = $conn->query($count_sql);
                        $row_count = $res_count->fetch_assoc();
                        $total_carrito = $row_count['total'] > 0 ? $row_count['total'] : 0;
                    ?>
                    <a class="btn text-white fw-medium shadow-sm px-3" href="carrito/carrito.php" style="background-color: #C06C38;" onmouseover="this.style.backgroundColor='#8D4A23'" onmouseout="this.style.backgroundColor='#C06C38'">
                        🛒 Carrito <?php echo $total_carrito > 0 ? "<span class='badge bg-light text-dark ms-1'>$total_carrito</span>" : ""; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="btn shadow-sm px-4 fw-medium" href="perfil/perfil.php" style="background-color: #E6D8B8; color: #504E76;" onmouseover="this.style.backgroundColor='#FDF8E2'; this.style.color='#8D4A23';" onmouseout="this.style.backgroundColor='#E6D8B8'; this.style.color='#504E76';">Mi Perfil</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container my-5">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h2 style="font-family: 'Righteous', sans-serif; color: #504E76;">Nuevos Lanzamientos & Clásicos</h2>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
        <?php if ($resultado->num_rows > 0): ?>
            <?php while($row = $resultado->fetch_assoc()): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm border-0 rounded-3" style="border: 2px solid #E6D8B8 !important;">
                        
                        <img src="<?php echo $row['imagen_portada']; ?>" class="card-img-top" alt="Portada de <?php echo $row['nombre_producto']; ?>" style="height: 250px; object-fit: cover; background-color: #E6D8B8; border-bottom: 2px solid #E6D8B8;">
                        
                        <div class="card-body d-flex flex-column p-4 bg-white">
                            <h5 class="card-title mb-1" style="font-family: 'Righteous', sans-serif; color: #504E76; font-size: 1.3rem;">
                                <?php echo htmlspecialchars($row['nombre_producto']); ?>
                            </h5>
                            <p class="card-text fw-medium mb-1" style="color: #8D4A23;">
                                <?php echo htmlspecialchars($row['artista']); ?>
                            </p>
                            <span class="badge mb-3 align-self-start" style="background-color: #E6D8B8; color: #504E76;"><?php echo htmlspecialchars($row['nombre_categoria']); ?></span>
                            
                            <div class="mt-auto">
                                <p class="mb-3 fw-bold fs-4" style="color: #C06C38;">
                                    $<?php echo number_format($row['precio'], 2); ?>
                                </p>
                                
                                <form action="index.php" method="POST" class="m-0 p-0">
                                    <input type="hidden" name="id_producto" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="titulo_producto" value="<?php echo htmlspecialchars($row['nombre_producto']); ?>">
                                    <input type="hidden" name="artista_producto" value="<?php echo htmlspecialchars($row['artista']); ?>">
                                    <input type="hidden" name="precio_producto" value="<?php echo $row['precio']; ?>">
                                    <input type="hidden" name="imagen_producto" value="<?php echo $row['imagen_portada']; ?>">
                                    
                                    <button type="submit" name="agregar_al_carrito" class="btn w-100 fw-medium text-white shadow-sm" style="background-color: #504E76; padding: 10px;" onmouseover="this.style.backgroundColor='#C06C38'" onmouseout="this.style.backgroundColor='#504E76'">
                                        Agregar al Carrito
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <h4 style="color: #8D4A23;">No encontramos vinilos en esta búsqueda/categoría.</h4>
                <a href="index.php" class="btn text-white mt-3 px-4 py-2 fw-medium" style="background-color: #C06C38;">Ver todo el catálogo</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>