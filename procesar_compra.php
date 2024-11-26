<?php
session_start(); // Iniciar la sesión

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
$mensaje = "";


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

$is_admin = $_SESSION['administrador'] ?? false;

// Obtener el método de pago actual del usuario
$sql_metodo_pago = "SELECT Numero_tarjeta_bancaria FROM Usuarios WHERE ID_Usuario = ?";
$stmt_metodo_pago = $conn->prepare($sql_metodo_pago);
$stmt_metodo_pago->bind_param("i", $id_usuario);
$stmt_metodo_pago->execute();
$result_metodo_pago = $stmt_metodo_pago->get_result();
$row_metodo_pago = $result_metodo_pago->fetch_assoc();
$metodo_pago_actual = $row_metodo_pago['Numero_tarjeta_bancaria'] ?? null;

// Actualizar el método de pago si se envía el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['nuevo_metodo_pago'])) {
    $nuevo_metodo_pago = $_POST['nuevo_metodo_pago'];

    // Actualizar el método de pago en la base de datos
    $sql_actualizar_metodo = "UPDATE Usuarios SET Numero_tarjeta_bancaria = ? WHERE ID_Usuario = ?";
    $stmt_actualizar_metodo = $conn->prepare($sql_actualizar_metodo);
    $stmt_actualizar_metodo->bind_param("si", $nuevo_metodo_pago, $id_usuario);

    if ($stmt_actualizar_metodo->execute()) {
        $mensaje = "Método de pago actualizado correctamente.";
        $metodo_pago_actual = $nuevo_metodo_pago; // Actualizar localmente
    } else {
        $mensaje = "Error al actualizar el método de pago.";
    }
}

// Consultar los productos en el carrito y verificar el inventario
$sql_carrito = "
    SELECT 
        c.ID_Producto_Carrito,
        c.ID_Producto,
        p.Nombre,
        c.Cantidad,
        p.Precio,
        p.Cantidad_en_almacen,
        (c.Cantidad * p.Precio) AS Total,
        p.Fotos
    FROM Carrito_Compras c
    INNER JOIN Productos p ON c.ID_Producto = p.ID_Producto
    WHERE c.ID_Usuario = ?";
$stmt_carrito = $conn->prepare($sql_carrito);
$stmt_carrito->bind_param("i", $id_usuario);
$stmt_carrito->execute();
$result_carrito = $stmt_carrito->get_result();

$carrito = [];
$total_compra = 0;
$errores = [];

if ($result_carrito->num_rows > 0) {
    while ($row = $result_carrito->fetch_assoc()) {
        if ($row['Cantidad'] > $row['Cantidad_en_almacen']) {
            $errores[] = "El producto " . htmlspecialchars($row['Nombre']) . " no tiene suficiente stock disponible.";
        }
        $carrito[] = $row;
        $total_compra += $row['Total'];
    }
} else {
    $mensaje = "Tu carrito está vacío.";
}

$conn->close();
?>

<!DOCTYPE HTML>
<html>
<head>
    <title>Procesar Compra</title>
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
                                        <li><a class="dropdown-item" href="#">Deportes</a></li>
                                        <li><a class="dropdown-item" href="#">Acción</a></li>
                                        <li><a class="dropdown-item" href="#">Mobile</a></li>
                                        <li><a class="dropdown-item" href="#">?</a></li>
                                    </ul>
                                </li>
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
    <h1>Procesar Compra</h1>


    <!-- Mostrar mensaje -->
    <?php if (!empty($mensaje)): ?>
        <div class="alert <?= strpos($mensaje, 'correctamente') !== false ? 'alert-success' : 'alert-danger' ?>">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <!-- Mostrar errores de inventario -->
    <?php if (!empty($errores)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Mostrar método de pago actual -->
    <div class="mb-3">
        <h3>Método de Pago</h3>
        <p><strong>Método Actual:</strong> <?= $metodo_pago_actual ? htmlspecialchars($metodo_pago_actual) : "No registrado" ?></p>
        <form method="post">
            <div class="mb-3">
                <label for="nuevo_metodo_pago" class="form-label">Nuevo Método de Pago</label>
                <input type="text" class="form-control" id="nuevo_metodo_pago" name="nuevo_metodo_pago" placeholder="Ingresa tu nuevo método de pago" required>
            </div>
            <button type="submit" class="btn btn-primary">Actualizar Método de Pago</button>
        </form>
    </div>

    <!-- Mostrar los productos del carrito -->
    <div>
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
    </div>

    <!-- Confirmar compra -->
    <form method="post" action="confirmar_compra.php">
        <button type="submit" class="btn btn-success" <?= !empty($errores) ? 'disabled' : '' ?>>Confirmar Compra</button>
        <a href="index.php" class="btn btn-secondary">Regresar al Catálogo</a>
    </form>
</div>
</body>
</html>
