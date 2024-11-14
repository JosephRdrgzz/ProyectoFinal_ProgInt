<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "Anahuac57";
$dbname = "proyectofinal";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

if (isset($_POST['id_producto'])) {
    $id_producto = $_POST['id_producto'];

    $sql = "SELECT * FROM Productos WHERE ID_Producto = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_producto);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $producto = $result->fetch_assoc();
    } else {
        echo "<p>Producto no encontrado.</p>";
        exit();
    }
    $stmt->close();
} else {
    header("Location: index.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE HTML>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <link rel="stylesheet" href="assets/css/main.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Detalles del Producto</title>
</head>
<body>


<section id="main">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="container mt-5">
                    <div class="row">
                        <div class="col-md-6">
                            <img src="<?= htmlspecialchars($producto['Fotos']) ?>" class="img-fluid" alt="<?= htmlspecialchars($producto['Nombre']) ?>">
                        </div>
                        <div class="col-md-6">
                            <h1><?= htmlspecialchars($producto['Nombre']) ?></h1>
                            <p><strong>Descripción:</strong> <?= htmlspecialchars($producto['Descripcion']) ?></p>
                            <p><strong>Precio:</strong> $<?= htmlspecialchars($producto['Precio']) ?> MXN</p>
                            <p><strong>Cantidad en Almacén:</strong> <?= htmlspecialchars($producto['Cantidad_en_almacen']) ?></p>
                            <p><strong>Fabricante:</strong> <?= htmlspecialchars($producto['Fabricante']) ?></p>
                            <p><strong>Origen:</strong> <?= htmlspecialchars($producto['Origen']) ?></p>

                            <!-- Botón de añadir al carrito no completo -->
                            <form action="agregar_carrito.php" method="post">
                                <input type="hidden" name="id_producto" value="<?= $producto['ID_Producto'] ?>">
                                <button type="submit" class="btn btn-success">Añadir al Carrito</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
