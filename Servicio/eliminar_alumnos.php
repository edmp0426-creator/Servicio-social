<?php

if (isset($_GET["id"])) {
    $id = $_GET["id"];

    $servername = "db";
    $username = "usuario";
    $password = "12345";
    $database = "servicioSocial";

    // Crear conexión
    $connection = new mysqli($servername, $username, $password, $database);

    // Consulta SQL para eliminar el alumno
    $sql = "DELETE FROM alumnos WHERE id=$id";
    $connection->query($sql);
}
header("location: /crud_alumnos.php");
exit;
?>