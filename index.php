<?php
session_start(); // Iniciar la sesión al inicio del archivo

// Configuración de la base de datos
$servername = "localhost";
$username = "root";
$password = "Anahuac57";
$dbname = "proyectofinal";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Verificar si se han enviado los datos de inicio de sesión
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];

    // Consulta para verificar las credenciales
    $sql = "SELECT ID_Usuario, Nombre_usuario, Contraseña, administrador FROM Usuarios WHERE Nombre_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();

        // Verificar la contraseña
        if (password_verify($contrasena, $row['Contraseña'])) {
            // Si la contraseña es correcta, iniciar sesión
            $_SESSION['id_usuario'] = $row['ID_Usuario'];
            $_SESSION['nombre_usuario'] = $row['Nombre_usuario'];
            $_SESSION['administrador'] = $row['administrador'];

            // Mantener al usuario en index.php y no redirigir
            header("Location: index.php");
            exit();
        } else {
            // Redirigir con mensaje de error de contraseña incorrecta
            header("Location: loginReg.html?error=incorrect_password");
            exit();
        }
    } else {
        // Redirigir con mensaje de error de usuario no encontrado
        header("Location: loginReg.html?error=user_not_found");
        exit();
    }

}


// Consulta para obtener los productos disponibles
$sql = "SELECT ID_Producto, Nombre, Precio, Fotos FROM Productos WHERE Cantidad_en_almacen > 0";
$result = $conn->query($sql);

// Cerrar conexión
$conn->close();
?>

<!DOCTYPE HTML>
<html>
<head>
    <title>Dopetrope by HTML5 UP</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <link rel="stylesheet" href="assets/css/main.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="homepage is-preload">
<div id="page-wrapper">
    <!-- Header -->
    <section id="header">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 20px;">
            <!-- Columna 1: Título y subtítulo -->
            <div style="flex: 1; text-align: left;">
                <h1><a href="index.php">J&J</a></h1>
                <h2>Códigos de juegos</h2>
            </div>

            <!-- Columna 2: Menú de navegación centrado -->
            <div style="flex: 1; text-align: center;">
                <nav id="nav">
                    <ul style="display: inline-flex; gap: 15px; list-style: none; padding: 0; margin: 0;">
                        <li class="current"><a href="index.html">Home</a></li>
                        <li><a href="#">Categorías</a>
                            <ul>
                                <li><a href="#">Deportes</a></li>
                                <li><a href="#">Acción</a></li>
                                <li><a href="#">Mobile</a></li>
                                <li><a href="#">?</a></li>
                            </ul>
                        </li>
                        <li><a href="loginReg.html">Cerrar Sesión</a></li>
                    </ul>
                </nav>
            </div>

            <!-- Columna 3: Bienvenida e ícono del carrito alineados a la derecha -->
            <div style="flex: 1; text-align: right;">
                <h3>Bienvenid@: <?php echo $_SESSION['nombre_usuario']; ?></h3>
                <!-- Ícono del carrito -->
                <a href="carrito.php" style="margin-left: 15px;">
                    <img src="assets/icons/carrito.png" alt="Carrito de Compras" style="width: 30px; height: 30px;">
                </a>
            </div>
        </div>
    </section>





    <!-- Main -->
    <section id="main">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <section>
                        <header class="major">
                            <h2>Productos disponibles</h2>
                        </header>
                        <div class="row">
                            <?php while($row = $result->fetch_assoc()): ?>
                                <div class="col-md-3 mb-4">
                                    <div class="card">
                                        <img src="<?= htmlspecialchars($row['Fotos']) ?>" class="card-img-top" alt="<?= htmlspecialchars($row['Nombre']) ?>" style="height: 400px; object-fit: cover;">
                                        <div class="card-body">
                                            <h5 class="card-title"><?= htmlspecialchars($row['Nombre']) ?></h5>
                                            <p class="card-text"><strong>Precio: $<?= htmlspecialchars($row['Precio']) ?> MXN</strong></p>
                                            <form action="detalle_producto.php" method="post">
                                                <input type="hidden" name="id_producto" value="<?= $row['ID_Producto'] ?>">
                                                <button type="submit" class="btn btn-primary">Más información</button>
                                                <button type="button" class="btn btn-secondary">Añadir al carrito</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </section>


</div>

<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/jquery.dropotron.min.js"></script>
<script src="assets/js/browser.min.js"></script>
<script src="assets/js/breakpoints.min.js"></script>
<script src="assets/js/util.js"></script>
<script src="assets/js/main.js"></script>
</body>
</html>
