<?php
session_start();

// Configuración de la base de datos
$host = 'mysql';  // Nombre del servicio en docker-compose.yml
$dbname = 'test-allport';
$username = 'root';
$password = 'root';

// Si ya hay sesión, redirige
if (isset($_SESSION['id_alumno'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Conectar a la base de datos
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Obtener datos del formulario
        $matricula = isset($_POST['matricula']) ? trim($_POST['matricula']) : '';
        $apellido1 = isset($_POST['apellido1']) ? trim($_POST['apellido1']) : '';

        if ($matricula === '' || $apellido1 === '') {
            $error = "Ingresa matrícula y primer apellido.";
        } elseif (!ctype_digit($matricula)) {
            $error = "La matrícula debe ser numérica.";
        } else {
            // Preparar la consulta contra alumnos-test
            $stmt = $conn->prepare("SELECT * FROM `alumnos-test` WHERE `matricula-alumno` = ? AND `apellido1_alumno` = ? LIMIT 1");
            $stmt->execute([$matricula, $apellido1]);
            $alumno = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($alumno) {
                // Iniciar sesión con datos del alumno
                $_SESSION['id_alumno'] = (int)$alumno['id_alumno'];
                $_SESSION['nombre_alumno'] = $alumno['nombre_alumno'];
                $_SESSION['apellido1_alumno'] = $alumno['apellido1_alumno'];
                $_SESSION['apellido2_alumno'] = $alumno['apellido2_alumno'];
                $_SESSION['matricula_alumno'] = (int)$alumno['matricula-alumno'];
                $_SESSION['ponderaciones_aptitudes'] = array(
                    1 => (int)$alumno['apt1'],
                    2 => (int)$alumno['ap2'],
                    3 => (int)$alumno['ap3'],
                    4 => (int)$alumno['ap4'],
                    5 => (int)$alumno['ap5'],
                    6 => (int)$alumno['ap6'],
                );

                header("Location: dashboard.php");
                exit();
            } else {
                $error = "No se encontró un alumno con esos datos.";
            }
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
                <?php if (!empty($error)): ?>
                    <div class="error-message" style="color: red; margin-bottom: 10px;">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                <input type="text" name="matricula" placeholder="Matrícula (número)" required>
                <input type="text" name="apellido1" placeholder="Primer apellido" required>
                <button type="submit" class="btn">Ingresar</button>
            </form>
        </div>
    </div>
</body>
</html>
