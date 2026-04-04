<?php
session_start();

// Redirigir a la subcarpeta login si no hay sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login/login.php");
    exit();
}

$host = "localhost";
$user = "root";
$pass = "";
$db   = "the_drop_vinyls";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$busqueda = "";
$sql = "SELECT * FROM productos WHERE stock > 0";

if (isset($_GET['buscar']) && !empty(trim($_GET['buscar']))) {
    $busqueda = $conn->real_escape_string(trim($_GET['buscar']));
    $sql .= " AND (nombre_producto LIKE '%$busqueda%' OR artista LIKE '%$busqueda%')";
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
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Righteous&display=swap" rel="stylesheet">
</head>
<body style="background-color: #FDF8E2; font-family: 'Fredoka', sans-serif; color: #504E76;">

<nav class="navbar navbar-expand-lg shadow-sm" style="background-color: #504E76; padding: 15px 0;">
    <div class="container">
        
        <a class="navbar-brand d-flex align-items-center" href="index.php" style="font-family: 'Righteous', sans-serif; color: #FDF8E2; font-size: 1.8rem; letter-spacing: 1px;">
            <img src="assets/LOGO.png" alt="Logo The Drop Vinyls" style="height: 40px; margin-right: 12px; object-fit: contain;">
            The Drop Vinyls
        </a>
        
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContenido" aria-controls="navbarContenido" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContenido">
            
            <form class="d-flex mx-auto my-2 my-lg-0 w-100" action="index.php" method="GET" style="max-width: 400px;">
                <input class="form-control me-2 border-0 shadow-sm" type="search" name="buscar" placeholder="Buscar artista o álbum..." aria-label="Search" value="<?php echo htmlspecialchars($busqueda); ?>" style="font-family: 'Fredoka', sans-serif;">
                <button class="btn text-white fw-medium shadow-sm px-4" type="submit" style="background-color: #C06C38; border: 2px solid #C06C38;" onmouseover="this.style.backgroundColor='#8D4A23'; this.style.borderColor='#8D4A23';" onmouseout="this.style.backgroundColor='#C06C38'; this.style.borderColor='#C06C38';">Buscar</button>
            </form>

            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                <li class="nav-item me-3 mb-2 mb-lg-0">
                    <span class="text-white fw-medium">Hola, <?php echo $_SESSION['usuario_nombre']; ?></span>
                </li>
                <li class="nav-item me-2 mb-2 mb-lg-0">
                    <a class="btn text-white fw-medium shadow-sm px-3" href="carrito.php" style="background-color: #C06C38; border: none;" onmouseover="this.style.backgroundColor='#8D4A23'" onmouseout="this.style.backgroundColor='#C06C38'">
                        🛒 Carrito
                    </a>
                </li>
                <li class="nav-item">
                    <a class="btn shadow-sm px-4 fw-medium" href="./perfil/perfil.php" style="background-color: #E6D8B8; color: #504E76; border: none;" onmouseover="this.style.backgroundColor='#FDF8E2'; this.style.color='#8D4A23';" onmouseout="this.style.backgroundColor='#E6D8B8'; this.style.color='#504E76';">Mi Perfil</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container my-5">
    
    <div class="row align-items-center mb-4">
        <div class="col">
            <h2 style="font-family: 'Righteous', sans-serif; color: #504E76;">
                <?php 
                    if (!empty($busqueda)) {
                        echo "Resultados para: '" . htmlspecialchars($busqueda) . "'";
                    } else {
                        echo "Nuevos Lanzamientos & Clásicos";
                    }
                ?>
            </h2>
        </div>
        <div class="col-auto">
            <p class="mb-0 fw-medium" style="color: #8D4A23;">
                <?php echo $resultado->num_rows; ?> vinilos encontrados
            </p>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
        
        <?php if ($resultado->num_rows > 0): ?>
            <?php while($row = $resultado->fetch_assoc()): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm border-0 rounded-3" style="border: 2px solid #E6D8B8 !important;">
                        
                        <img src="img/<?php echo $row['imagen_portada']; ?>" class="card-img-top" alt="Portada de <?php echo $row['nombre_producto']; ?>" style="height: 250px; object-fit: cover; background-color: #E6D8B8; border-bottom: 2px solid #E6D8B8;" onerror="this.src='https://via.placeholder.com/250x250/E6D8B8/504E76?text=Vinilo'">
                        
                        <div class="card-body d-flex flex-column p-4 bg-white">
                            <h5 class="card-title mb-1" style="font-family: 'Righteous', sans-serif; color: #504E76; font-size: 1.3rem;">
                                <?php echo htmlspecialchars($row['nombre_producto']); ?>
                            </h5>
                            <p class="card-text fw-medium mb-3" style="color: #8D4A23;">
                                <?php echo htmlspecialchars($row['artista']); ?>
                            </p>
                            
                            <div class="mt-auto">
                                <p class="mb-3 fw-bold fs-4" style="color: #C06C38;">
                                    $<?php echo number_format($row['precio'], 2); ?>
                                </p>
                                <button class="btn w-100 fw-medium text-white shadow-sm" style="background-color: #504E76; padding: 10px;" onmouseover="this.style.backgroundColor='#C06C38'" onmouseout="this.style.backgroundColor='#504E76'">
                                    Agregar al Carrito
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <h4 style="color: #8D4A23;">No encontramos vinilos que coincidan con tu búsqueda.</h4>
                <a href="index.php" class="btn text-white mt-3 px-4 py-2 fw-medium shadow-sm" style="background-color: #C06C38;" onmouseover="this.style.backgroundColor='#8D4A23'" onmouseout="this.style.backgroundColor='#C06C38'">Ver todo el catálogo</a>
            </div>
        <?php endif; ?>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php 
$conn->close(); 
?>