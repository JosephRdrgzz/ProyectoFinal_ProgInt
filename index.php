<?php
// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "Anahuac57";
$dbname = "proyectofinal";

$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Consulta para obtener los productos disponibles
$sql = "SELECT ID_Producto, Nombre, Precio, Fotos FROM Productos WHERE Cantidad_en_almacen > 0";
$result = $conn->query($sql);
?>


<!DOCTYPE HTML>

<html>
	<head>
		<title>Dopetrope by HTML5 UP</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
		<link rel="stylesheet" href="assets/css/main.css" />
        <!-- Latest compiled and minified CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

        <!-- Latest compiled JavaScript -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	</head>
	<body class="homepage is-preload">
		<div id="page-wrapper">

			<!-- Header -->
				<section id="header">

					<!-- Logo -->
						<h1><a href="index.php">J&J</a></h1>
                        <h2>Códigos de juegos</h2>

					<!-- Nav -->
						<nav id="nav">
							<ul>
								<li class="current"><a href="index.html">Home</a></li>
								<li>
									<a href="#">Categorías</a>
									<ul>
										<li><a href="#">Deportes</a></li>
										<li><a href="#">Acción</a></li>
										<li><a href="#">Mobile</a></li>
                                        <li><a href="#">?</a></li>
									</ul>
								</li>
								<li><a href="left-sidebar.html">Cerrar Sesión</a></li>
							</ul>
						</nav>
				</section>

			<!-- Main -->
				<section id="main">
					<div class="container">
						<div class="row">
							<div class="col-12">

								<!-- Portfolio -->
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
                                                            <!-- Botón para enviar el ID del producto mediante POST -->
                                                            <form action="detalle_producto.php" method="post">
                                                                <input type="hidden" name="id_producto" value="<?= $row['ID_Producto'] ?>">
                                                                <button type="submit" class="btn btn-primary">Más información</button>

                                                                <!-- Botón de añadir al carrito, funcionalidad futura -->
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

			<!-- Footer -->
				<section id="footer">
					<div class="container">
					</div>
				</section>

		</div>

		<!-- Scripts -->
			<script src="assets/js/jquery.min.js"></script>
			<script src="assets/js/jquery.dropotron.min.js"></script>
			<script src="assets/js/browser.min.js"></script>
			<script src="assets/js/breakpoints.min.js"></script>
			<script src="assets/js/util.js"></script>
			<script src="assets/js/main.js"></script>

	</body>
</html>