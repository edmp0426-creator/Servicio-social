<?php
session_start();

// Configuración de la base de datos
$host = 'mysql';  // Este es el nombre del servicio en docker-compose.yml
$dbname = 'test-allport';
$username = 'root';
$password = 'root';

// Verificar si ya hay una sesión iniciada
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Conectar a la base de datos
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obtener datos del formulario (proteger si los campos no vienen)
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password_input = isset($_POST['password']) ? $_POST['password'] : '';

        // Preparar la consulta
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($email === '' || $password_input === '') {
            $error = "Por favor completa email y contraseña";
        } else if ($user && password_verify($password_input, $user['password'])) {
            // Iniciar sesión
            $_SESSION['user_id'] = $user['id_usuario'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['nombre'];

            // Redirigir al dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Email o contraseña incorrectos";
        }
    } catch(PDOException $e) {
        $error = "Error de conexión: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/style_login.css">
</head>
<body>
    <div class="container">
        <div class="form-box" id="login-form">
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <img src="imagenes/logo.png" alt="Logo" class="logo">
                <h2>Ingresar</h2>
                <?php if (isset($error)): ?>
                    <div class="error-message" style="color: red; margin-bottom: 10px;">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                <input type="email" name="email" placeholder="Correo Electronico" required>
                <input type="password" name="password" placeholder="Contraseña" required>
                <button type="submit" class="btn">Ingresar</button>
            </form>
        </div>
    </div>
</body>
</html>