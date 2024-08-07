<?php
session_start();
$servidor = "localhost";
$usuario = "root";
$clave = "";
$baseDeDatos = "proyecto";

$enlace = mysqli_connect($servidor, $usuario, $clave, $baseDeDatos);

if (!$enlace) {
    die("Error en la conexión: " . mysqli_connect_error());
}

// Manejo del formulario de login
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Autenticación simple
    if ($username == 'admin' && $password == '123') {
        $_SESSION['loggedin'] = true;
        header("Location: " . $_SERVER['PHP_SELF']); // Redirigir a la misma página para evitar reenvío de formulario
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos";
    }
}

// Manejo del logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Manejo del formulario de denuncias
if (isset($_POST['registro'])) {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $asunto = $_POST['asunto'];
    $descripcion = $_POST['descripcion'];
    $evidencia = $_FILES['evidencia'];

    // Procesar la carga de la imagen
    if (!is_dir('uploads')) {
        mkdir('uploads', 0777, true);
    }

    if ($evidencia['error'] == 0) {
        $rutaDestino = 'uploads/' . basename($evidencia['name']);
        if (move_uploaded_file($evidencia['tmp_name'], $rutaDestino)) {
            $insertarDatos = "INSERT INTO denuncias (nombre, correo, telefono, asunto, descripcion, evidencia) VALUES ('$nombre', '$correo', '$telefono', '$asunto', '$descripcion', '$rutaDestino')";
            $ejecutarInsertar = mysqli_query($enlace, $insertarDatos);

            if ($ejecutarInsertar) {
                echo "<p>Denuncia registrada con éxito.</p>";
            } else {
                echo "<p>Error: " . mysqli_error($enlace) . "</p>";
            }
        } else {
            echo "<p>Error al mover la imagen.</p>";
        }
    } else {
        $insertarDatos = "INSERT INTO denuncias (nombre, correo, telefono, asunto, descripcion) VALUES ('$nombre', '$correo', '$telefono', '$asunto', '$descripcion')";
        $ejecutarInsertar = mysqli_query($enlace, $insertarDatos);

        if ($ejecutarInsertar) {
            echo "<p>Denuncia registrada con éxito (sin evidencia).</p>";
        } else {
            echo "<p>Error: " . mysqli_error($enlace) . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Denuncias Anónimas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            background-color: #1e1e1e;
            color: #e0e0e0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        header {
            background-color: #343a40;
            color: #ffffff;
            padding: 20px;
            text-align: center;
            width: 100%;
            border-bottom: 2px solid #495057;
        }
        nav {
            background-color: #495057;
            overflow: hidden;
            width: 100%;
            text-align: center;
            border-bottom: 2px solid #6c757d;
        }
        nav a {
            display: inline-block;
            color: #ffffff;
            padding: 15px 20px;
            text-decoration: none;
            font-size: 16px;
        }
        nav a:hover {
            background-color: #6c757d;
        }
        .container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 30px;
            background-color: #343a40;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
        }
        .hidden {
            display: none;
        }
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .form-group {
            width: auto;
            margin-bottom: 15px;
            position: relative;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px;
            padding-left: 40px;
            font-size: 16px;
            border: 1px solid #495057;
            border-radius: 5px;
            background-color: #495057;
            color: #e0e0e0;
        }
        .form-group textarea {
            height: 200px;
            resize: none;
        }
        .form-group i {
            position: absolute;
            top: 50%;
            left: 10px;
            transform: translateY(-50%);
            color: #e0e0e0;
        }
        input[type="file"] {
            padding: 5px;
            color: #e0e0e0;
        }
        input[type="submit"], input[type="reset"] {
            width: auto;
            padding: 12px 25px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            color: #ffffff;
            background-color: #007bff;
            font-size: 16px;
            transition: background-color 0.3s ease;
            margin: 10px;
        }
        input[type="submit"]:hover, input[type="reset"]:hover {
            background-color: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            color: #e0e0e0;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #495057;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #495057;
            color: #ffffff;
        }
        tr:nth-child(even) {
            background-color: #3c3c3c;
        }
        img {
            max-width: 120px;
            height: auto;
            border-radius: 5px;
        }
        h1 {
            color: #ffffff;
            text-align: center;
        }
        p {
            text-align: center;
        }
        .login-form {
            display: flex;
            flex-direction: column;
            width:80%;
            align-items: center;
        }
        .login-form input {
            margin-bottom: 10px;
        }

        #formulario {
            width: 90%;
        }
        @media screen and (max-width: 600px) {
            body {
                padding: 10px;
            }
            nav a {
                padding: 10px;
                font-size: 14px;
            }
            .container {
                padding: 20px;
            }
            table, th, td {
                font-size: 12px;
            }
            img {
                max-width: 80px;
            }
            .form-group input, .form-group textarea {
                font-size: 14px;
            }
            input[type="submit"], input[type="reset"] {
                padding: 10px 20px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Denuncias Anónimas</h1>
    </header>
    <nav>
        <a href="#" onclick="showView('formulario')">Hacer Denuncia</a>
        <a href="#" onclick="showView('sobre_nosotros')">Sobre Nosotros</a>
        <a href="#" onclick="showView('faq')">FAQ</a>
        <a href="#" onclick="showView('denuncias')">Ver Denuncias</a>
    </nav>

    <div class="container">
        <div id="formulario">
            <h1>Formulario de Denuncia Anónima</h1>
            <form action="" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <i class="fas fa-user"></i>
                    <input type="text" name="nombre" placeholder="Nombre (opcional)">
                </div>
                <div class="form-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="correo" placeholder="Correo (opcional)">
                </div>
                <div class="form-group">
                    <i class="fas fa-phone"></i>
                    <input type="text" name="telefono" placeholder="Teléfono (opcional)">
                </div>
                <div class="form-group">
                    <i class="fas fa-tag"></i>
                    <input type="text" name="asunto" placeholder="Asunto de la denuncia" required>
                </div>
                <div class="form-group">
                    <i class="fas fa-align-left"></i>
                    <textarea name="descripcion" placeholder="Descripción de la denuncia" required></textarea>
                </div>
                <div class="form-group">
                    <i class="fas fa-paperclip"></i>
                    <input type="file" name="evidencia" accept="image/*">
                </div>
                <input type="submit" name="registro" value="Enviar">
                <input type="reset" value="Limpiar">
            </form>
            <?php
            if (isset($_POST['registro'])) {
                // Procesar formulario de denuncia
            }
            ?>
        </div>

        <div id="sobre_nosotros" class="hidden">
            <h1>Sobre Nosotros</h1>
            <p>Somos una organización dedicada a recibir y gestionar denuncias anónimas...</p>
            <p>La información proporcionada se procesa de manera confidencial y se utiliza para...</p>
        </div>

        <div id="faq" class="hidden">
            <h1>Preguntas Frecuentes (FAQ)</h1>
            <p><strong>¿Cómo puedo realizar una denuncia?</strong><br>Para realizar una denuncia, navega a la sección de 'Hacer Denuncia' y completa el formulario.</p>
            <p><strong>¿Es realmente anónima mi denuncia?</strong><br>Sí, tu denuncia será procesada de manera confidencial y no se revelará tu identidad.</p>
            <p><strong>¿Puedo adjuntar evidencia a mi denuncia?</strong><br>Sí, puedes adjuntar imágenes como evidencia utilizando el campo de carga de archivos en el formulario.</p>
            <p><strong>¿Cómo puedo ver las denuncias realizadas?</strong><br>Para ver las denuncias, debes iniciar sesión y navegar a la sección 'Ver Denuncias'.</p>
        </div>

        <div id="denuncias" class="hidden">
            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true): ?>
                <h1>Denuncias Registradas</h1>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Asunto</th>
                        <th>Descripción</th>
                        <th>Fecha</th>
                        <th>Evidencia</th>
                    </tr>
                    <?php
                    $consulta = "SELECT * FROM denuncias";
                    $resultado = mysqli_query($enlace, $consulta);

                    while ($fila = mysqli_fetch_assoc($resultado)) {
                        echo "<tr>";
                        echo "<td>" . $fila['id'] . "</td>";
                        echo "<td>" . $fila['nombre'] . "</td>";
                        echo "<td>" . $fila['correo'] . "</td>";
                        echo "<td>" . $fila['telefono'] . "</td>";
                        echo "<td>" . $fila['asunto'] . "</td>";
                        echo "<td>" . $fila['descripcion'] . "</td>";
                        echo "<td>" . $fila['fecha'] . "</td>";
                        if ($fila['evidencia']) {
                            echo "<td><img src='" . $fila['evidencia'] . "' alt='Evidencia'></td>";
                        } else {
                            echo "<td>No hay evidencia</td>";
                        }
                        echo "</tr>";
                    }
                    ?>
                </table>
                <p><a href="?logout=true">Cerrar sesión</a></p>
            <?php else: ?>
                <h1>Iniciar Sesión</h1>
                <?php if (isset($error)): ?>
                    <p style="color: red;"><?php echo $error; ?></p>
                <?php endif; ?>
                <form class="login-form" action="" method="post">
                    <div class="form-group">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username" placeholder="Usuario" required>
                    </div>
                    <div class="form-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" placeholder="Contraseña" required>
                    </div>
                    <input type="submit" name="login" value="Iniciar sesión">
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function showView(viewId) {
            const views = document.querySelectorAll('.container > div');
            views.forEach(view => view.classList.add('hidden'));
            document.getElementById(viewId).classList.remove('hidden');
        }
    </script>
</body>
</html>
