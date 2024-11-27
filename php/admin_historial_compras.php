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


// Obtener lista de usuarios
$sql_usuarios = "SELECT ID_Usuario, Nombre_usuario FROM Usuarios";
$result_usuarios = $conn->query($sql_usuarios);

// Procesar la selección de usuario
$historial_compras = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_usuario'])) {
    $id_usuario = intval($_POST['id_usuario']);
    $sql_historial = "
        SELECT 
            IFNULL(p.Nombre, 'Producto no disponible') AS Producto,
            hc.Fecha_compra AS Fecha,
            IFNULL(p.Precio, 0) AS Precio
        FROM Historial_Compras hc
        LEFT JOIN Productos p ON hc.ID_Producto = p.ID_Producto
        WHERE hc.ID_Usuario = ?
        ORDER BY hc.Fecha_compra DESC";
    $stmt_historial = $conn->prepare($sql_historial);
    $stmt_historial->bind_param("i", $id_usuario);
    $stmt_historial->execute();
    $result_historial = $stmt_historial->get_result();

    while ($row = $result_historial->fetch_assoc()) {
        $historial_compras[] = $row;
    }
    $stmt_historial->close();
}


$conn->close();
?>

<!DOCTYPE HTML>
<html>
<head>
    <title>Historial de Compras (Admin)</title>
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
    <h1>Historial de Compras</h1>


    <p>Selecciona un usuario para consultar su historial de compras.</p>

    <!-- Formulario para seleccionar usuario -->
    <form method="post" class="mb-4">
        <div class="mb-3">
            <label for="id_usuario" class="form-label">Seleccionar Usuario</label>
            <select class="form-select" id="id_usuario" name="id_usuario" required>
                <option value="">Seleccione un usuario...</option>
                <?php while ($usuario = $result_usuarios->fetch_assoc()): ?>
                    <option value="<?= $usuario['ID_Usuario'] ?>" <?= isset($id_usuario) && $id_usuario == $usuario['ID_Usuario'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($usuario['Nombre_usuario']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Consultar Historial</button>
    </form>

    <!-- Mostrar historial de compras -->
    <?php if (!empty($historial_compras)): ?>
        <h2>Historial de Compras del Usuario</h2>
        <table class="table table-bordered table-striped">
            <thead>
            <tr>
                <th>Producto</th>
                <th>Fecha de Compra</th>
                <th>Precio</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($historial_compras as $compra): ?>
                <tr>
                    <td><?= htmlspecialchars($compra['Producto']) ?></td>
                    <td><?= htmlspecialchars($compra['Fecha']) ?></td>
                    <td>$<?= number_format($compra['Precio'], 2) ?> MXN</td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <div class="alert alert-warning">No se encontraron compras para el usuario seleccionado.</div>
    <?php endif; ?>
</div>
</body>
</html>
