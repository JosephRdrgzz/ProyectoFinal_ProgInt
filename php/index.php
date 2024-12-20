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
// Manejar inicio de sesión
if (isset($_POST['correo']) && isset($_POST['contrasena'])) { // Cambiar 'usuario' a 'correo'
    $correo = $_POST['correo'];  // Ahora 'correo' en lugar de 'usuario'
    $contrasena = $_POST['contrasena'];

    // Consulta para verificar las credenciales (cambiar 'Nombre_usuario' por 'Correo')
    $sql = "SELECT ID_Usuario, Nombre_usuario, Correo_electronico, Contraseña, administrador FROM Usuarios WHERE Correo_electronico = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $correo);  // Cambiar 'usuario' por 'correo'
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();

        // Verificar la contraseña
        if (password_verify($contrasena, $row['Contraseña'])) {
            $_SESSION['id_usuario'] = $row['ID_Usuario'];
            $_SESSION['nombre_usuario'] = $row['Nombre_usuario'];
            $_SESSION['correo_usuario'] = $row['Correo'];
            $_SESSION['administrador'] = $row['administrador'];

            header("Location: index.php");
            exit();
        } else {
            header("Location: index.html?error=incorrect_password");
            exit();
        }
    } else {
        header("Location: ../index.html?error=user_not_found");
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

// Manejar la acción de "Agregar al carrito"
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_producto'])) {
    if (isset($_SESSION['id_usuario'])) {
        $id_producto = intval($_POST['id_producto']);
        $id_usuario = $_SESSION['id_usuario'];

        // Verificar si el producto ya está en el carrito
        $sql_check = "SELECT Cantidad FROM Carrito_Compras WHERE ID_Usuario = ? AND ID_Producto = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("ii", $id_usuario, $id_producto);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            // Si ya existe, aumentar la cantidad
            $sql_update = "UPDATE Carrito_Compras SET Cantidad = Cantidad + 1 WHERE ID_Usuario = ? AND ID_Producto = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ii", $id_usuario, $id_producto);
            $stmt_update->execute();
        } else {
            // Si no existe, agregarlo al carrito
            $sql_insert = "INSERT INTO Carrito_Compras (ID_Usuario, ID_Producto, Cantidad) VALUES (?, ?, 1)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("ii", $id_usuario, $id_producto);
            $stmt_insert->execute();
        }

        $stmt_check->close();

        // Mensaje de éxito
        $_SESSION['mensaje'] = "Producto agregado al carrito con éxito.";
    } else {
        // Mensaje de error si el usuario no está autenticado
        $_SESSION['mensaje'] = "Por favor, inicia sesión para agregar productos al carrito.";
    }

    // Redirigir a la misma página para evitar reenvío del formulario
    header("Location: index.php");
    exit();
}

// Consulta para obtener los productos disponibles
$sql = "SELECT ID_Producto, Nombre, Precio, Fotos FROM Productos WHERE Cantidad_en_almacen > 0";
$result_productos = $conn->query($sql);


// Mostrar mensaje si existe
$mensaje = "";
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    unset($_SESSION['mensaje']);
}











// Consultar los productos en el carrito
$carrito = [];
if (isset($_SESSION['id_usuario'])) {
    $id_usuario = $_SESSION['id_usuario'];
    $sql_carrito = "
    SELECT 
        c.ID_Producto_Carrito, -- Incluye el identificador único de cada fila del carrito
        p.Fotos, 
        p.Nombre, 
        c.Cantidad, 
        p.Precio, 
        (c.Cantidad * p.Precio) AS Total
    FROM Carrito_Compras c
    INNER JOIN Productos p ON c.ID_Producto = p.ID_Producto
    WHERE c.ID_Usuario = ?";

    $stmt_carrito = $conn->prepare($sql_carrito);
    $stmt_carrito->bind_param("i", $id_usuario);
    $stmt_carrito->execute();
    $result_carrito = $stmt_carrito->get_result();
    while ($row = $result_carrito->fetch_assoc()) {
        $carrito[] = $row;
    }
}

// Manejar las acciones de aumentar o disminuir cantidad en el carrito
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion'])) {
    if (isset($_SESSION['id_usuario'])) {
        $id_producto_carrito = intval($_POST['id_producto_carrito']); // Identificar la fila específica del carrito
        $id_usuario = $_SESSION['id_usuario'];
        $accion = $_POST['accion']; // Acción: 'aumentar', 'disminuir', 'eliminar'

        if ($accion === 'aumentar') {
            // Aumentar la cantidad en el carrito
            $sql_update = "UPDATE Carrito_Compras SET Cantidad = Cantidad + 1 WHERE ID_Producto_Carrito = ? AND ID_Usuario = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ii", $id_producto_carrito, $id_usuario);
            $stmt_update->execute();
        } elseif ($accion === 'disminuir') {
            // Verificar la cantidad actual antes de disminuir
            $sql_check = "SELECT Cantidad FROM Carrito_Compras WHERE ID_Producto_Carrito = ? AND ID_Usuario = ?";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param("ii", $id_producto_carrito, $id_usuario);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            $row = $result_check->fetch_assoc();

            if ($row && $row['Cantidad'] > 1) {
                // Disminuir la cantidad si es mayor a 1
                $sql_update = "UPDATE Carrito_Compras SET Cantidad = Cantidad - 1 WHERE ID_Producto_Carrito = ? AND ID_Usuario = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("ii", $id_producto_carrito, $id_usuario);
                $stmt_update->execute();
            } elseif ($row && $row['Cantidad'] == 1) {
                // Si la cantidad es 1, eliminar la fila
                $sql_delete = "DELETE FROM Carrito_Compras WHERE ID_Producto_Carrito = ? AND ID_Usuario = ?";
                $stmt_delete = $conn->prepare($sql_delete);
                $stmt_delete->bind_param("ii", $id_producto_carrito, $id_usuario);
                $stmt_delete->execute();
            }
        } elseif ($accion === 'eliminar') {
            // Eliminar la fila completa
            $sql_delete = "DELETE FROM Carrito_Compras WHERE ID_Producto_Carrito = ? AND ID_Usuario = ?";
            $stmt_delete = $conn->prepare($sql_delete);
            $stmt_delete->bind_param("ii", $id_producto_carrito, $id_usuario);
            $stmt_delete->execute();
        }

        // Redirigir para evitar reenvío del formulario
        header("Location: index.php");
        exit();
    }
}


$conn->close();


?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Catálogo</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<div class="container mt-5">
    <!-- Header -->
    <section id="header">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 20px;">
            <!-- Columna 1: Logo y subtítulo -->
            <div style="flex: 1; text-align: left;">
                <h1 style="font-size: 1.5rem; margin: 0;"><a href="index.php">Pay to win games</a></h1>  <!-- Reducido el tamaño del h1 -->
                <h2 style="font-size: 1rem; margin: 0;">Códigos de juegos</h2>  <!-- Reducido el tamaño del h2 -->
            </div>

            <!-- Columna 2: Menú de navegación centrado -->
            <div style="flex: 2; text-align: center;">
                <nav class="navbar navbar-expand-lg navbar-light" style="max-width: 80%; margin: 0 auto;">  <!-- Ajustado el ancho máximo del nav -->
                    <div class="container-fluid">
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarNav">
                            <ul class="navbar-nav mx-auto">
                                <li class="nav-item">
                                    <a class="nav-link active" aria-current="page" href="index.php">Home</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="index.php#informacion-contacto">About Us</a>
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
                                    <a class="nav-link" href="../index.html">Cerrar Sesión</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
            </div>

            <!-- Columna 3: Bienvenida y botón -->
            <div style="flex: 1; text-align: right;">
                <h3 style="font-size: 1rem; margin: 0;">Bienvenid@: <?php echo $_SESSION['nombre_usuario'] ?? 'Invitado'; ?></h3>
                <!-- Botón para abrir el modal -->
                <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#carritoModal">
                    <i class="fas fa-shopping-cart"></i> Ver Carrito
                </button>
            </div>
        </div>
    </section>

    <!-- Modal -->
    <div class="modal fade" id="carritoModal" tabindex="-1" aria-labelledby="carritoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="carritoModalLabel">Carrito de Compras</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if (count($carrito) > 0): ?>
                        <table class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th>Imagen</th>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Precio Unitario</th>
                                <th>Total</th>
                                <th>Acciones</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $total = 0; ?>
                            <?php foreach ($carrito as $item): ?>
                                <tr>
                                    <td>
                                        <img src="../<?= htmlspecialchars($item['Fotos'] ?? 'ruta_default.png') ?>"
                                             alt="<?= htmlspecialchars($item['Nombre']) ?>"
                                             class="img-thumbnail"
                                             style="width: 80px; height: auto;">
                                    </td>
                                    <td><?= htmlspecialchars($item['Nombre']) ?></td>
                                    <td><?= htmlspecialchars($item['Cantidad']) ?></td>
                                    <td>$<?= number_format($item['Precio'], 2) ?> MXN</td>
                                    <td>$<?= number_format($item['Total'], 2) ?> MXN</td>
                                    <td>
                                        <!-- Botón para aumentar -->
                                        <form action="index.php" method="post" style="display: inline-block;">
                                            <input type="hidden" name="id_producto_carrito" value="<?= htmlspecialchars($item['ID_Producto_Carrito']) ?>">
                                            <input type="hidden" name="accion" value="aumentar">
                                            <button type="submit" class="btn btn-sm btn-success">+</button>
                                        </form>

                                        <form action="index.php" method="post" style="display: inline-block;">
                                            <input type="hidden" name="id_producto_carrito" value="<?= htmlspecialchars($item['ID_Producto_Carrito']) ?>">
                                            <input type="hidden" name="accion" value="disminuir">
                                            <button type="submit" class="btn btn-sm btn-warning">-</button>
                                        </form>

                                        <form action="index.php" method="post" style="display: inline-block;">
                                            <input type="hidden" name="id_producto_carrito" value="<?= htmlspecialchars($item['ID_Producto_Carrito']) ?>">
                                            <input type="hidden" name="accion" value="eliminar">
                                            <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                        </form>


                                    </td>
                                </tr>
                                <?php $total += $item['Total']; ?>
                            <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                            <tr>
                                <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                <td><strong>$<?= number_format($total, 2) ?> MXN</strong></td>
                                <td></td>
                            </tr>
                            </tfoot>
                        </table>
                    <?php else: ?>
                        <p class="text-center">Tu carrito está vacío.</p>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <a href="procesar_compra.php" class="btn btn-success">Procesar Compra</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Mostrar mensaje de éxito -->
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="margin: 20px;">
            <?= htmlspecialchars($mensaje); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Main -->
    <section id="main">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <section>
                        <header class="major">
                            <h2>Productos disponibles</h2>
                        </header>


                        <?php
                                $conn = new mysqli($servername, $username, $password, $dbname);
                                if ($conn->connect_error) {
                            die("Conexión fallida: " . $conn->connect_error);
                            }

                            // Obtener las categorías disponibles
                            $sql_categorias = "SELECT DISTINCT Categoria FROM Productos";
                            $result_categorias = $conn->query($sql_categorias);

                            // Verificar si se ha enviado una categoría
                            $categoria = isset($_GET['categoria']) ? $_GET['categoria'] : '';

                            // Consultar productos según la categoría seleccionada
                            if ($categoria) {
                            $sql_productos = "SELECT * FROM Productos WHERE Categoria = '$categoria'";
                            } else {
                            $sql_productos = "SELECT * FROM Productos"; // Mostrar todos los productos si no hay categoría seleccionada
                            }

                            $result_productos = $conn->query($sql_productos);
                            ?>

                        <!-- Formulario de selección de categoría -->
                        <h2>Selecciona una categoría</h2>
                        <form action="" method="get">
                            <select name="categoria" class="form-control" onchange="this.form.submit()">
                                <option value="">Todas las categorías</option>
                                <?php while ($row_categoria = $result_categorias->fetch_assoc()): ?>
                                    <option value="<?= htmlspecialchars($row_categoria['Categoria']) ?>" <?= ($row_categoria['Categoria'] == $categoria) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($row_categoria['Categoria']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </form>

                        <hr>

                        <!-- Mostrar productos -->
                        <h2>Productos</h2>
                        <div class="row">
                            <!-- Mostrar productos filtrados por categoría -->
                            <?php
                            // Obtener la categoría seleccionada desde la URL
                            $categoriaSeleccionada = isset($_GET['categoria']) ? $_GET['categoria'] : '';

                            // Consulta de productos, filtrando por categoría si es necesario
                            $sql_productos = "SELECT * FROM Productos";
                            if ($categoriaSeleccionada) {
                                $sql_productos .= " WHERE Categoria = '" . $conn->real_escape_string($categoriaSeleccionada) . "'";
                            }
                            $result_productos = $conn->query($sql_productos);

                            if ($result_productos->num_rows > 0):
                                while($row_producto = $result_productos->fetch_assoc()):
                                    ?>
                                    <div class="col-md-3 mb-4">
                                        <div class="card">
                                            <!-- Mostrar imagen del producto -->
                                            <img src="../<?= htmlspecialchars($row_producto['Fotos']) ?>" class="card-img-top" alt="<?= htmlspecialchars($row_producto['Nombre']) ?>" style="height: 200px; object-fit: cover;">

                                            <div class="card-body">
                                                <!-- Mostrar el nombre del producto -->
                                                <h5 class="card-title"><?= htmlspecialchars($row_producto['Nombre']) ?></h5>
                                                <!-- Mostrar precio -->
                                                <p class="card-text"><strong>Precio: $<?= number_format($row_producto['Precio'], 2) ?> MXN</strong></p>
                                                <br>

                                                <!-- Formulario para agregar al carrito -->
                                                <form action="index.php" method="post">
                                                    <input type="hidden" name="id_producto" value="<?= $row_producto['ID_Producto'] ?>">
                                                    <button type="submit" class="btn btn-secondary">Añadir al carrito</button>
                                                </form>
                                                <br>

                                                <!-- Formulario para ver detalles del producto -->
                                                <form action="detalle_producto.php" method="post">
                                                    <input type="hidden" name="id_producto" value="<?= $row_producto['ID_Producto'] ?>">
                                                    <button type="submit" class="btn btn-primary">Detalles del Producto</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="text-center">No hay productos disponibles en esta categoría.</p>
                            <?php endif; ?>
                        </div>



                    </section>
                </div>
            </div>
        </div>
    </section>



    <!-- Información de contacto e información adicional -->
    <div id="informacion-contacto" class="container-fluid mt-5">
        <div class="row justify-content-center">
            <!-- Información de contacto -->
            <div class="col-md-6 mb-4">
                <div class="info-box shadow p-4 bg-white text-dark border border-dark rounded">
                    <h3 class="text-dark">Información de Contacto</h3>
                    <p><strong>Teléfono:</strong> 5548589636</p>
                    <p><strong>Email:</strong> joseph.rodriguez@anahuac.mx</p>
                    <p><strong>Dirección:</strong> Universidad Anahuac Norte</p>
                </div>
            </div>

            <!-- Información adicional -->
            <div class="col-md-6 mb-4">
                <div class="info-box shadow p-4 bg-white text-dark border border-dark rounded">
                    <h3 class="text-dark">Información Adicional</h3>
                    <p>
                        Somos una tienda especializada en la venta de códigos de juegos digitales,
                        específicamente los que son pay to win porque vender esos códigos es lo que
                        de verdad deja dinero. (:

                    </p>
                </div>
            </div>
        </div>
    </div>

</div>









<script src="../assets/js/jquery.min.js"></script>
<script src="../assets/js/main.js"></script>
</body>
</html>
