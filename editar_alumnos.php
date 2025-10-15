<?php

$servername = "db";
$username = "usuario";
$password = "12345";
$database = "servicioSocial";

// Crear conexión
$connection = new mysqli($servername, $username, $password, $database);


$id = "";
$nombre = "";
$email = "";

$errorMessage = "";
$successMessage = "";

if ( $_SERVER['REQUEST_METHOD']== 'GET') {
    // GET metodo: Mostrar los datos del alumno
    if (!isset($_GET["id"])) {
        header("location: /crud_alumnos.php");
        exit;
    }

    $id = $_GET["id"];

    // Consulta SQL para obtener los datos del alumno
    $sql = "SELECT * FROM alumnos WHERE id=$id";
    $result = $connection->query($sql);
    $row = $result->fetch_assoc();

    if (!$row) {
        header("location: /crud_alumnos.php");
        exit;
    }

    $nombre = $row["nombre"];
    $email = $row["email"];
}
else {
    // POST metodo: Actualizar los datos del alumno
    $id = $_POST["id"];
    $nombre = $_POST["nombre"];
    $email = $_POST["email"];

    do {
        if (empty($id) || empty($nombre) || empty($email)) {
            $errorMessage = "Todos los campos son obligatorios";
            break;
        }

        $sql = "UPDATE alumnos " . 
               "SET nombre = '$nombre', email = '$email' " . 
               "WHERE id = $id";

        $result = $connection->query($sql);
        if (!$result) {
            $errorMessage = "Error en la consulta: " . $connection->error;
            break;
        }

        $successMessage = "Alumno actualizado correctamente";

        header("location: /crud_alumnos.php");
        exit;

    } while (false);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Alumnos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <script> src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2>Nuevo Alumno</h2>

        <?php
        if (!empty($errorMessage)) {
            echo "
            <div class='alert alert-warning alert-dismissible fade show' role='alert'>
                <strong>$errorMessage</strong>
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>
            ";
        }
        ?>

        <form method="post">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Nombre</label> 
                <div class="row mb-3">
                    <input type="text" class="form-control" name="nombre" value="<?php echo $nombre; ?>">
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Email</label>
                <div class="row mb-3">
                    <input type="text" class="form-control" name="email" value="<?php echo $email; ?>">
                </div>
            </div>

            <?php
            if (!empty($successMessage)) {
                echo "
                <div class='row mb-3'>
                    <div class='offset-sm-3 col-sm-6'>
                        <div class='alert alert-success alert-dismissible fade show' role='alert'>
                            <strong>$successMessage</strong>
                            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                        </div>
                    </div>
                </div>
                ";
            }
            ?>

            <div class="row mb-3">
                <div class="col-sm-3 d-grid col-sm-3 d-grid">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
                <div class="col-sm-3 d-grid">
                    <a class="btn btn-outline-primary" href="/crud_alumnos.php" role="button">Cancelar</a>
                </div>
            </div>
        </form> 
    </div>
</body>
</html>