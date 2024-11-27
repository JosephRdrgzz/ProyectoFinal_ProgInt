<?php
session_start();

// Verificar si el usuario es administrador
if (!isset($_SESSION['id_usuario']) || !$_SESSION['administrador']) {
    header("Location: index.php");
    exit();
}

// Configuración de la base de datos
$servername = "localhost";
$username = "root";
$password = "Anahuac57";
$dbname = "proyectofinal";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

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

$mensaje = "";

// Manejar el formulario de edición, creación o eliminación de productos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['editar_producto'])) {
        // Obtener los datos del formulario
        $id_producto = intval($_POST['id_producto']);
        $nombre = $_POST['nombre'] ?? null;
        $descripcion = $_POST['descripcion'] ?? null;
        $precio = floatval($_POST['precio'] ?? 0);
        $cantidad = intval($_POST['cantidad'] ?? 0);
        $fabricante = $_POST['fabricante'] ?? null;
        $origen = $_POST['origen'] ?? null;
        $categoria = $_POST['categoria'] ?? null;

        // Obtener la imagen actual del producto
        $sql_imagen_actual = "SELECT Fotos FROM Productos WHERE ID_Producto = ?";
        $stmt_imagen_actual = $conn->prepare($sql_imagen_actual);
        $stmt_imagen_actual->bind_param("i", $id_producto);
        $stmt_imagen_actual->execute();
        $result_imagen_actual = $stmt_imagen_actual->get_result();
        $row_imagen_actual = $result_imagen_actual->fetch_assoc();
        $ruta_imagen = $row_imagen_actual['Fotos'];

        // Manejar la carga de una nueva imagen
        if (!empty($_FILES['foto']['name'])) {
            $directorio = 'images/';
            $nombre_archivo = basename($_FILES['foto']['name']);
            $ruta_imagen_nueva = $directorio . $nombre_archivo;

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_imagen_nueva)) {
                $ruta_imagen = $ruta_imagen_nueva; // Actualizar la ruta de la imagen si se sube una nueva
            } else {
                $mensaje = "Error al subir la imagen.";
            }
        }

        // Actualizar el producto
        $sql = "UPDATE Productos SET Nombre = ?, Descripcion = ?, Precio = ?, Cantidad_en_almacen = ?, Fabricante = ?, Origen = ?, Categoria = ?, Fotos = ? WHERE ID_Producto = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdiisssi", $nombre, $descripcion, $precio, $cantidad, $fabricante, $origen, $categoria, $ruta_imagen, $id_producto);
        if ($stmt->execute()) {
            $mensaje = "Producto actualizado correctamente.";
        } else {
            $mensaje = "Error al actualizar el producto: " . $conn->error;
        }
    }

    if (isset($_POST['crear_producto'])) {
        // Crear un nuevo producto
        $nombre = $_POST['nombre'] ?? null;
        $descripcion = $_POST['descripcion'] ?? null;
        $precio = floatval($_POST['precio'] ?? 0);
        $cantidad = intval($_POST['cantidad'] ?? 0);
        $fabricante = $_POST['fabricante'] ?? null;
        $origen = $_POST['origen'] ?? null;
        $categoria = $_POST['categoria'] ?? null;

        $ruta_imagen = null;
        if (!empty($_FILES['foto']['name'])) {
            $directorio = 'images/';
            $nombre_archivo = basename($_FILES['foto']['name']);
            $ruta_imagen = $directorio . $nombre_archivo;
            if (!move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_imagen)) {
                $mensaje = "Error al subir la imagen.";
            }
        }

        $sql = "INSERT INTO Productos (Nombre, Descripcion, Precio, Cantidad_en_almacen, Fabricante, Origen, Categoria, Fotos) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdiisss", $nombre, $descripcion, $precio, $cantidad, $fabricante, $origen, $categoria, $ruta_imagen);
        if ($stmt->execute()) {
            $mensaje = "Producto creado correctamente.";
        } else {
            $mensaje = "Error al crear el producto: " . $conn->error;
        }
    }

    if (isset($_POST['eliminar_producto'])) {
        // Eliminar producto
        $id_producto = intval($_POST['id_producto']);

        // Eliminar producto de la tabla Productos
        $sql = "DELETE FROM Productos WHERE ID_Producto = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_producto);

        if ($stmt->execute()) {
            $mensaje = "Producto eliminado correctamente.";
        } else {
            $mensaje = "Error al eliminar el producto: " . $conn->error;
        }

        $stmt->close();
    }
}

// Obtener los productos existentes
$sql_productos = "SELECT * FROM Productos";
$result_productos = $conn->query($sql_productos);

$conn->close();
?>



<!DOCTYPE html>
<!DOCTYPE html>
<html>
<head>
    <title>Editar Items</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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

    <h1>Administrar Productos</h1>



    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-info"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>


    <!-- Formulario para crear un nuevo producto -->
    <h2>Agregar Nuevo Producto</h2>
    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" name="nombre" id="nombre" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea name="descripcion" id="descripcion" class="form-control" required></textarea>
        </div>
        <div class="mb-3">
            <label for="precio" class="form-label">Precio</label>
            <input type="number" name="precio" id="precio" step="0.01" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="cantidad" class="form-label">Cantidad</label>
            <input type="number" name="cantidad" id="cantidad" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="fabricante" class="form-label">Fabricante</label>
            <input type="text" name="fabricante" id="fabricante" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="origen" class="form-label">Origen</label>
            <input type="text" name="origen" id="origen" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="categoria" class="form-label">Categoría</label>
            <input type="text" name="categoria" id="categoria" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="foto" class="form-label">Imagen</label>
            <input type="file" name="foto" id="foto" class="form-control" required>
        </div>
        <button type="submit" name="crear_producto" class="btn btn-success">Crear Producto</button>
    </form>

    <!-- Mostrar los productos existentes -->
    <h2>Productos Existentes</h2>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Precio</th>
            <th>Cantidad</th>
            <th>Fabricante</th>
            <th>Origen</th>
            <th>Categoría</th>
            <th>Imagen</th>
            <th>Acciones</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($producto = $result_productos->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($producto['ID_Producto']) ?></td>
                <td><?= htmlspecialchars($producto['Nombre']) ?></td>
                <td><?= htmlspecialchars($producto['Descripcion']) ?></td>
                <td>$<?= number_format($producto['Precio'], 2) ?></td>
                <td><?= htmlspecialchars($producto['Cantidad_en_almacen']) ?></td>
                <td><?= htmlspecialchars($producto['Fabricante']) ?></td>
                <td><?= htmlspecialchars($producto['Origen']) ?></td>
                <td><?= htmlspecialchars($producto['Categoria']) ?></td>
                <td><img src="<?= htmlspecialchars($producto['Fotos']) ?>" alt="Imagen" style="width: 100px; height: auto;"></td>
                <td>
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="id_producto" value="<?= $producto['ID_Producto'] ?>">

                        <label for="nombre-<?= $producto['ID_Producto'] ?>">Nombre</label>
                        <input type="text" name="nombre" id="nombre-<?= $producto['ID_Producto'] ?>" value="<?= $producto['Nombre'] ?>" class="form-control mb-1">

                        <label for="descripcion-<?= $producto['ID_Producto'] ?>">Descripción</label>
                        <input type="text" name="descripcion" id="descripcion-<?= $producto['ID_Producto'] ?>" value="<?= $producto['Descripcion'] ?>" class="form-control mb-1">

                        <label for="precio-<?= $producto['ID_Producto'] ?>">Precio (MXN)</label>
                        <input type="number" name="precio" id="precio-<?= $producto['ID_Producto'] ?>" value="<?= $producto['Precio'] ?>" step="0.01" class="form-control mb-1">

                        <label for="cantidad-<?= $producto['ID_Producto'] ?>">Cantidad en Almacén</label>
                        <input type="number" name="cantidad" id="cantidad-<?= $producto['ID_Producto'] ?>" value="<?= $producto['Cantidad_en_almacen'] ?>" class="form-control mb-1">

                        <label for="fabricante-<?= $producto['ID_Producto'] ?>">Fabricante</label>
                        <input type="text" name="fabricante" id="fabricante-<?= $producto['ID_Producto'] ?>" value="<?= $producto['Fabricante'] ?>" class="form-control mb-1">

                        <label for="origen-<?= $producto['ID_Producto'] ?>">Origen</label>
                        <input type="text" name="origen" id="origen-<?= $producto['ID_Producto'] ?>" value="<?= $producto['Origen'] ?>" class="form-control mb-1">

                        <label for="categoria-<?= $producto['ID_Producto'] ?>">Categoría</label>
                        <input type="text" name="categoria" id="categoria-<?= $producto['ID_Producto'] ?>" value="<?= $producto['Categoria'] ?>" class="form-control mb-1">

                        <label for="foto-<?= $producto['ID_Producto'] ?>">Actualizar Imagen</label>
                        <input type="file" name="foto" id="foto-<?= $producto['ID_Producto'] ?>" class="form-control mb-1">

                        <button type="submit" name="editar_producto" class="btn btn-warning">Guardar Cambios</button>
                        <button type="submit" name="eliminar_producto" class="btn btn-danger">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>


</div>
</body>

<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/main.js"></script>
</html>
