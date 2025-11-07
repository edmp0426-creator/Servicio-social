<?php
session_start();

// Si no hay sesión iniciada, redirigir al login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Nombre seguro del usuario
$userName = isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : htmlspecialchars($_SESSION['user_email']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/style_login.css">
</head>
<body>
    <div class="container">
        <div class="form-box">
            <h2>Bienvenido, <?php echo $userName; ?></h2>
            <p>Has iniciado sesión correctamente.</p>
            <form method="POST" action="logout.php">
                <button type="submit" class="btn">Cerrar sesión</button>
            </form>
        </div>
    </div>
</body>
</html>