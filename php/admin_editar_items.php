<?php
session_start(); // Iniciar la sesión

// Conexión a la base de datos
$conn = new mysqli("localhost", "root", "Anahuac57", "proyectofinal");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    // Redirigir al index.html si no está iniciada la sesión
    header("Location: index.html?error=user_not_found");
    exit();
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
            header("Location: index.html?error=incorrect_password");
            exit();
        }
    } else {
        header("Location: index.html?error=user_not_found");
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

// Verificar si se ha enviado el formulario para eliminar un producto
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['eliminar_producto'])) {
    $id_producto = $_POST['producto_id'];

    // Consulta para eliminar el producto
    $sql = "DELETE FROM productos WHERE ID_Producto = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_producto);

    if ($stmt->execute()) {
        $message = "Producto eliminado exitosamente.";
        $message_type = "success";
    } else {
        $message = "Error al eliminar el producto: " . $stmt->error;
        $message_type = "danger";
    }
} else if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_producto = isset($_POST['producto_id']) ? $_POST['producto_id'] : '';
    $nombre = isset($_POST['nombre']) ? $_POST['nombre'] : '';
    $descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : '';
    $precio = isset($_POST['precio']) ? $_POST['precio'] : '';
    $fabricante = isset($_POST['fabricante']) ? $_POST['fabricante'] : '';
    $cantidad_en_almacen = isset($_POST['cantidad_en_almacen']) ? $_POST['cantidad_en_almacen'] : '';
    $origen = isset($_POST['origen']) ? $_POST['origen'] : '';
    $categoria = isset($_POST['categoria']) ? $_POST['categoria'] : '';
    $imagen = isset($_FILES['imagen']) ? $_FILES['imagen'] : null;

    // Verificar que todos los campos obligatorios están completos
    if ($nombre == '' || $precio == '' || $cantidad_en_almacen == '' || $categoria == '') {
        $message = "Por favor, complete todos los campos obligatorios.";
        $message_type = "warning";
    } else {
        // Manejar la carga de la imagen
        $images_dir = "../images";

        $ruta_imagen = '';
        if ($imagen && $imagen['error'] == 0) {
            $nombre_imagen = basename($imagen['name']);
            $ruta_imagen = $images_dir . "/" . $nombre_imagen;
            if (move_uploaded_file($imagen['tmp_name'], $ruta_imagen)) {
                // Store the path as /images/ in the database
                $ruta_imagen = "/images/" . $nombre_imagen;
            } else {
                echo "Error al subir la imagen.";
            }
        }

        // Si producto_id está vacío, significa que es un nuevo producto (INSERT)
        if (empty($id_producto)) {
            $sql = "INSERT INTO productos (Nombre, Descripcion, Precio, Fabricante, Cantidad_en_almacen, Origen, Categoria, Fotos)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssisss", $nombre, $descripcion, $precio, $fabricante, $cantidad_en_almacen, $origen, $categoria, $ruta_imagen);

            if ($stmt->execute()) {
                $message = "Nuevo producto agregado exitosamente.";
                $message_type = "success";
            } else {
                $message = "Error al agregar el producto: " . $stmt->error;
                $message_type = "danger";
            }
        } else {
            // Si producto_id no está vacío, actualizar el producto
            if ($ruta_imagen) {
                $sql = "UPDATE productos SET Nombre = ?, Descripcion = ?, Precio = ?, Fabricante = ?, Cantidad_en_almacen = ?, Origen = ?, Categoria = ?, Fotos = ? WHERE ID_Producto = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssisssi", $nombre, $descripcion, $precio, $fabricante, $cantidad_en_almacen, $origen, $categoria, $ruta_imagen, $id_producto);
            } else {
                $sql = "UPDATE productos SET Nombre = ?, Descripcion = ?, Precio = ?, Fabricante = ?, Cantidad_en_almacen = ?, Origen = ?, Categoria = ? WHERE ID_Producto = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssissi", $nombre, $descripcion, $precio, $fabricante, $cantidad_en_almacen, $origen, $categoria, $id_producto);
            }

            if ($stmt->execute()) {
                $message = "Producto actualizado exitosamente.";
                $message_type = "success";
            } else {
                $message = "Error al actualizar el producto: " . $stmt->error;
                $message_type = "danger";
            }
        }
    }
}

// Obtener los productos para llenar el combobox
$result = $conn->query("SELECT ID_Producto, Nombre FROM productos");

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <title>Editar/Agregar Producto</title>
</head>
<body>
<div class="container mt-5">
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
                </nav>            </div>

            <div style="flex: 1; text-align: right;">
                <h3>Bienvenid@: <?php echo $_SESSION['nombre_usuario'] ?? 'Invitado'; ?></h3>

            </div>
        </div>
    </section>

    <h2>Editar/Agregar Producto</h2>

    <!-- Mostrar mensajes de alerta -->
    <?php if (isset($message)): ?>
        <div class="alert alert-<?= $message_type ?>"><?= $message ?></div>
    <?php endif; ?>

    <!-- Formulario para seleccionar un producto y editarlo -->
    <form action="" method="POST">
        <label for="producto_id">Seleccione un producto:</label>
        <select name="producto_id" id="producto_id" onchange="this.form.submit()">
            <option value="">Inserte un producto nuevo o Seleccione</option>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <option value="<?= $row['ID_Producto'] ?>" <?php echo (isset($_POST['producto_id']) && $_POST['producto_id'] == $row['ID_Producto']) ? 'selected' : ''; ?>>
                    <?= $row['Nombre'] ?>
                </option>
            <?php } ?>
        </select>
    </form>

    <?php
    // Si se ha seleccionado un producto para editar, mostrar los detalles
    if (isset($_POST['producto_id']) && $_POST['producto_id'] != "") {
        $producto_id = $_POST['producto_id'];

        // Obtener los detalles del producto seleccionado
        $sql = "SELECT * FROM productos WHERE ID_Producto = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $producto_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $producto = $result->fetch_assoc();

        if ($producto) {
            ?>
            <div class="mb-3">
            <!-- Formulario para editar el producto seleccionado -->
                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="hidden" class="form-control" name="producto_id" value="<?= $producto['ID_Producto'] ?>">

                    <label for="nombre">Nombre:</label>
                    <input type="text" class="form-control" name="nombre" value="<?= htmlspecialchars($producto['Nombre']) ?>" required><br>

                    <label for="descripcion">Descripción:</label>
                    <textarea name="descripcion" class="form-control"><?= htmlspecialchars($producto['Descripcion']) ?></textarea><br>

                    <label for="precio">Precio:</label>
                    <input type="number" class="form-control" step="0.01" name="precio" value="<?= htmlspecialchars($producto['Precio']) ?>" required><br>

                    <label for="fabricante">Fabricante:</label>
                    <input type="text" class="form-control" name="fabricante" value="<?= htmlspecialchars($producto['Fabricante']) ?>"><br>

                    <label for="cantidad_en_almacen">Cantidad en Almacén:</label>
                    <input type="number" class="form-control" name="cantidad_en_almacen" value="<?= htmlspecialchars($producto['Cantidad_en_almacen']) ?>" required><br>

                    <label for="origen">Origen:</label>
                    <input type="text" class="form-control" name="origen" value="<?= htmlspecialchars($producto['Origen']) ?>"><br>

                    <label for="categoria">Categoría:</label>
                    <input type="text" class="form-control" name="categoria" value="<?= htmlspecialchars($producto['Categoria']) ?>" required><br>

                    <label for="imagen">Imagen:</label>
                    <input type="file" class="form-control" name="imagen"><br>
                    <?php if (!empty($producto['Fotos'])): ?>
                        <img src="../<?= htmlspecialchars($producto['Fotos']) ?>" alt="Imagen del producto" style="max-width: 200px; max-height: 200px;"><br>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-primary">Actualizar Producto</button>

                    <button type="submit" name="eliminar_producto" class="btn btn-danger">Eliminar Producto</button>
                </form>
            </div>
            <?php
        } else {
            echo "Producto no encontrado.";
        }
    } else {
        // Mostrar formulario para agregar un nuevo producto
        ?>

        <!-- Formulario para agregar un nuevo producto -->
        <form action="" method="POST" enctype="multipart/form-data">
            <label for="nombre">Nombre:</label>
            <input type="text" class="form-control" name="nombre" required><br>

            <label for="descripcion">Descripción:</label>
            <textarea name="descripcion" class="form-control"></textarea><br>

            <label for="precio">Precio:</label>
            <input type="number" class="form-control" step="0.01" name="precio" required><br>

            <label for="fabricante">Fabricante:</label>
            <input type="text" class="form-control" name="fabricante"><br>

            <label for="cantidad_en_almacen">Cantidad en Almacén:</label>
            <input type="number" class="form-control" name="cantidad_en_almacen" required><br>

            <label for="origen">Origen:</label>
            <input type="text" class="form-control" name="origen"><br>

            <label for="categoria">Categoría:</label>
            <input type="text" class="form-control" name="categoria" required><br>

            <label for="imagen">Imagen:</label>
            <input type="file" class="form-control" name="imagen"><br>
            <?php if (!empty($ruta_imagen)): ?>
                <img src="<?= htmlspecialchars($ruta_imagen) ?>" alt="Imagen del producto" style="max-width: 200px; max-height: 200px;"><br>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary">Agregar Producto</button>
        </form>

        <?php
    }
    ?>

</div>
</body>
</html>

<?php
$conn->close();
?>