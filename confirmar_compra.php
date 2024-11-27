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

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    die("Por favor, inicia sesión para procesar tu compra.");
}

$id_usuario = $_SESSION['id_usuario'];


// Manejar inicio de sesión
if (isset($_POST['usuario']) && isset($_POST['contrasena'])) {
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
            $_SESSION['id_usuario'] = $row['ID_Usuario'];
            $_SESSION['nombre_usuario'] = $row['Nombre_usuario'];
            $_SESSION['administrador'] = $row['administrador'];

            header("Location: index.php");
            exit();
        } else {
            header("Location: loginReg.html?error=incorrect_password");
            exit();
        }
    } else {
        header("Location: loginReg.html?error=user_not_found");
        exit();
    }
}
// Obtener las categorías distintas de la base de datos
$sql_categorias = "SELECT DISTINCT Categoria FROM Productos";
$result_categorias = $conn->query($sql_categorias);
// Obtener la categoría seleccionada desde la URL (si existe)
$categoria = isset($_GET['categoria']) ? $_GET['categoria'] : '';

// Consulta para obtener productos filtrados por categoría
if ($categoria) {
    $sql_productos = "SELECT * FROM Productos WHERE Categoria = ?";
    $stmt_productos = $conn->prepare($sql_productos);
    $stmt_productos->bind_param("s", $categoria);
} else {
    // Si no se seleccionó categoría, mostrar todos los productos
    $sql_productos = "SELECT * FROM Productos";
    $stmt_productos = $conn->prepare($sql_productos);
}

$stmt_productos->execute();
$result_productos = $stmt_productos->get_result();

$is_admin = $_SESSION['administrador'] ?? false;

// Consultar los productos en el carrito
$sql_carrito = "
    SELECT 
        c.ID_Producto, 
        c.Cantidad, 
        p.Cantidad_en_almacen
    FROM Carrito_Compras c
    INNER JOIN Productos p ON c.ID_Producto = p.ID_Producto
    WHERE c.ID_Usuario = ?";
$stmt_carrito = $conn->prepare($sql_carrito);
$stmt_carrito->bind_param("i", $id_usuario);
$stmt_carrito->execute();
$result_carrito = $stmt_carrito->get_result();

// Verificar si el carrito está vacío
if ($result_carrito->num_rows === 0) {
    $_SESSION['mensaje'] = "Tu carrito está vacío. No puedes procesar la compra.";
    header("Location: index.php");
    exit();
}

// Procesar la compra
$conn->begin_transaction(); // Iniciar transacción
try {
    while ($item = $result_carrito->fetch_assoc()) {
        $id_producto = $item['ID_Producto'];
        $cantidad = $item['Cantidad'];
        $cantidad_en_almacen = $item['Cantidad_en_almacen'];

        // Verificar si hay suficiente stock
        if ($cantidad > $cantidad_en_almacen) {
            throw new Exception("No hay suficiente stock para el producto con ID: $id_producto.");
        }

        // Disminuir la cantidad en la tabla Productos
        $sql_update_producto = "UPDATE Productos SET Cantidad_en_almacen = Cantidad_en_almacen - ? WHERE ID_Producto = ?";
        $stmt_update_producto = $conn->prepare($sql_update_producto);
        $stmt_update_producto->bind_param("ii", $cantidad, $id_producto);
        $stmt_update_producto->execute();

        // Insertar en el historial de compras
        $sql_historial = "INSERT INTO Historial_Compras (ID_Usuario, ID_Producto, Fecha_compra) VALUES (?, ?, NOW())";
        $stmt_historial = $conn->prepare($sql_historial);
        $stmt_historial->bind_param("ii", $id_usuario, $id_producto);
        $stmt_historial->execute();
    }

    // Vaciar el carrito
    $sql_vaciar_carrito = "DELETE FROM Carrito_Compras WHERE ID_Usuario = ?";
    $stmt_vaciar_carrito = $conn->prepare($sql_vaciar_carrito);
    $stmt_vaciar_carrito->bind_param("i", $id_usuario);
    $stmt_vaciar_carrito->execute();

    $conn->commit(); // Confirmar transacción
    $_SESSION['mensaje'] = "Compra procesada con éxito.";
} catch (Exception $e) {
    $conn->rollback(); // Revertir transacción en caso de error
    $_SESSION['mensaje'] = "Error al procesar la compra: " . $e->getMessage();
}

// Redirigir al historial de compras
header("Location: historial_compras.php");
exit();

$conn->close();
?>



<!DOCTYPE HTML>
<html>
<head>
    <title>Confirmar Compra</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">

    <link rel="stylesheet" href="assets/css/main.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<div class="container mt-5">

    <!-- Header -->
    <section id="header">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 20px;">
            <div style="flex: 1; text-align: left;">
                <h1><a href="index.php">Pay to win games</a></h1>
                <h2>Códigos de juegos</h2>
            </div>

            <!-- Columna 2: Menú de navegación centrado -->
            <div style="flex: 1; text-align: center;">
                <nav class="navbar navbar-expand-lg navbar-light bg-light">
                    <div class="container-fluid">
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarNav">
                            <ul class="navbar-nav mx-auto">
                                <li class="nav-item">
                                    <a class="nav-link active" aria-current="page" href="index.php">Home</a>
                                </li>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="categoriesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Categorías
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="categoriesDropdown">
                                        <?php while ($categoria = $result_categorias->fetch_assoc()): ?>
                                            <li><a class="dropdown-item" href="index.php?categoria=<?= urlencode($categoria['Categoria']) ?>"><?= htmlspecialchars($categoria['Categoria']) ?></a></li>
                                        <?php endwhile; ?>
                                    </ul>
                                </li>
                                <?php if (isset($_SESSION['id_usuario'])): ?>
                                    <!-- Mostrar opción para ver historial de compras si el usuario está autenticado -->
                                    <li class="nav-item">
                                        <a class="nav-link" href="historial_compras.php">Historial de Compras</a>
                                    </li>
                                <?php endif; ?>
                                <?php if ($is_admin): ?>
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            Admin
                                        </a>
                                        <ul class="dropdown-menu" aria-labelledby="adminDropdown">
                                            <li><a class="dropdown-item" href="admin_editar_items.php">Editar Items</a></li>
                                            <li><a class="dropdown-item" href="admin_historial_compras.php">Historial de Compras</a></li>
                                        </ul>
                                    </li>
                                <?php endif; ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="loginReg.html">Cerrar Sesión</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>            </div>

            <div style="flex: 1; text-align: right;">
                <h3>Bienvenid@: <?php echo $_SESSION['nombre_usuario'] ?? 'Invitado'; ?></h3>
            </div>
        </div>
    </section>
    <h1>Confirmar Compra</h1>

    <!-- Mostrar mensaje -->
    <?php if (!empty($mensaje)): ?>
        <div class="alert <?= strpos($mensaje, 'éxito') !== false ? 'alert-success' : 'alert-danger' ?>">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <!-- Mostrar resumen de compra -->
    <h3>Resumen de Compra</h3>
    <?php if (count($carrito) > 0): ?>
        <table class="table table-bordered table-striped">
            <thead>
            <tr>
                <th>Imagen</th>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Total</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($carrito as $item): ?>
                <tr>
                    <td>
                        <img src="<?= htmlspecialchars($item['Fotos']) ?>"
                             alt="<?= htmlspecialchars($item['Nombre']) ?>"
                             class="img-thumbnail"
                             style="width: 80px; height: auto;">
                    </td>
                    <td><?= htmlspecialchars($item['Nombre']) ?></td>
                    <td><?= htmlspecialchars($item['Cantidad']) ?></td>
                    <td>$<?= number_format($item['Precio'], 2) ?> MXN</td>
                    <td>$<?= number_format($item['Total'], 2) ?> MXN</td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="4" class="text-end"><strong>Total:</strong></td>
                <td><strong>$<?= number_format($total_compra, 2) ?> MXN</strong></td>
            </tr>
            </tfoot>
        </table>
    <?php else: ?>
        <p class="text-center">Tu carrito está vacío.</p>
    <?php endif; ?>

    <!-- Confirmar compra -->
    <form method="post">
        <button type="submit" class="btn btn-success">Confirmar Compra</button>
        <a href="index.php" class="btn btn-secondary">Regresar al Catálogo</a>
    </form>
</div>
</body>
</html>
