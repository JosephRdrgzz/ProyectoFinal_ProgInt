
<!DOCTYPE HTML>
<html lang="es">
<head>
    <title>Registro de Usuario - J&J</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <link rel="stylesheet" href="../assets/css/main.css" />
</head>
<body class="is-preload">
<div id="page-wrapper">
    <section id="header">
        <h1><a href="index.php">J&J</a></h1>
    </section>

    <section id="main">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <?php
                    if ($_SERVER["REQUEST_METHOD"] == "POST") {

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

                        // Capturar datos del formulario
                        $nombre_usuario = $_POST['nombre_usuario'];
                        $correo_electronico = $_POST['correo_electronico'];
                        //por seguridad de mysql siempre se usa el hash de contraseña para evitar inyecciones sql
                        $contrasena = password_hash($_POST['contrasena'], PASSWORD_BCRYPT);
                        $fecha_nacimiento = $_POST['fecha_nacimiento'];
                        $numero_tarjeta_bancaria = $_POST['numero_tarjeta_bancaria'];
                        $direccion_postal = $_POST['direccion_postal'];
                        $administrador = 0; // Valor predeterminado de administrador

                        // Insertar datos en la tabla Usuarios
                        $sql = "INSERT INTO Usuarios (Nombre_usuario, Correo_electronico, Contraseña, Fecha_nacimiento, Numero_tarjeta_bancaria, Direccion_Postal, administrador)
                                VALUES (?, ?, ?, ?, ?, ?, ?)";

                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("ssssssi", $nombre_usuario, $correo_electronico, $contrasena, $fecha_nacimiento, $numero_tarjeta_bancaria, $direccion_postal, $administrador);

                        if ($stmt->execute()) {
                            echo "<div class='alert alert-success'>Registro exitoso. Ahora puedes iniciar sesión.</div>";
                        } else {
                            echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
                        }

                        // Cerrar conexión
                        $stmt->close();
                        $conn->close();
                    }
                    ?>

                    <div class="p-5 m-5 border bg-black text-white">
                        <h2 class="text-center mb-4">Inicio de sesión</h2>
                        <form action="index.php" method="POST">
                            <div class="mb-3">
                                <label for="correoLog" class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" id="correoLog" name="correo" placeholder="Correo electrónico" required>
                            </div>
                            <div class="mb-3">
                                <label for="contrasenaLog" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="contrasenaLog" name="contrasena" placeholder="Contraseña" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Iniciar Sesión</button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </section>
</div>

<script src="../assets/js/jquery.min.js"></script>
<script src="../assets/js/jquery.dropotron.min.js"></script>
<script src="../assets/js/browser.min.js"></script>
<script src="../assets/js/breakpoints.min.js"></script>
<script src="../assets/js/util.js"></script>
<script src="../assets/js/main.js"></script>
</body>
</html>
