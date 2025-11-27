<?php
session_start();
require_once __DIR__ . '/connection.php';

$mensaje_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $apellido1 = isset($_POST['apellido1']) ? trim($_POST['apellido1']) : '';
    $apellido2 = isset($_POST['apellido2']) ? trim($_POST['apellido2']) : '';
    $matricula = isset($_POST['matricula']) ? trim($_POST['matricula']) : '';

    if ($nombre === '' || $apellido1 === '' || $matricula === '') {
        $mensaje_error = 'Completa al menos nombre, primer apellido y matrícula.';
    } elseif (!ctype_digit($matricula)) {
        $mensaje_error = 'La matrícula debe ser numérica.';
    } else {
        $aptitudes_cero = array(0, 0, 0, 0, 0, 0);
        if (isset($conn) && $conn instanceof mysqli) {
            mysqli_report(MYSQLI_REPORT_OFF);
            try {
                $sql = "INSERT INTO `alumnos-test` (`nombre_alumno`, `apellido1_alumno`, `apellido2_alumno`, `matricula-alumno`, `apt1`, `ap2`, `ap3`, `ap4`, `ap5`, `ap6`, `respuestas-alumno`, `estado`)"
                     . " VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '', 0)";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $matricula_int = intval($matricula);
                    $stmt->bind_param(
                        "sssiiiiiii",
                        $nombre,
                        $apellido1,
                        $apellido2,
                        $matricula_int,
                        $aptitudes_cero[0],
                        $aptitudes_cero[1],
                        $aptitudes_cero[2],
                        $aptitudes_cero[3],
                        $aptitudes_cero[4],
                        $aptitudes_cero[5]
                    );
                    $stmt->execute();
                    $nuevo_id = $stmt->insert_id;
                    $stmt->close();

                    // Guardar info básica en sesión
                    $_SESSION['id_alumno'] = $nuevo_id;
                    $_SESSION['nombre_alumno'] = $nombre;
                    $_SESSION['apellido1_alumno'] = $apellido1;
                    $_SESSION['apellido2_alumno'] = $apellido2;
                    $_SESSION['matricula_alumno'] = $matricula_int;

                    header('Location: test1.php');
                    exit;
                } else {
                    $mensaje_error = 'No se pudo preparar el registro: ' . $conn->error;
                }
            } catch (mysqli_sql_exception $e) {
                $mensaje_error = 'Error al registrar: ' . $e->getMessage();
            }
        } else {
            $mensaje_error = 'Conexión a BD no disponible.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Signup de Prueba</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f5f5f5; padding:40px; }
    .card { max-width: 480px; margin: 0 auto; background:#fff; padding:24px; border-radius:12px; box-shadow:0 6px 14px rgba(0,0,0,0.08); }
    h1 { margin-top:0; color:#0b7b4c; }
    label { display:block; margin-top:12px; font-weight:600; }
    input { width:100%; padding:10px; border:1px solid #ccc; border-radius:6px; margin-top:6px; }
    button { margin-top:18px; padding:12px; width:100%; background:#0b7b4c; color:#fff; border:none; border-radius:8px; font-size:16px; cursor:pointer; }
    button:hover { background:#0a6d43; }
    .error { margin-top:12px; background:#fff3cd; color:#856404; border:1px solid #ffeeba; padding:10px; border-radius:8px; }
  </style>
</head>
<body>
  <div class="card">
    <h1>Registro de prueba</h1>
    <form method="POST">
      <label for="nombre">Nombre</label>
      <input type="text" id="nombre" name="nombre" required>

      <label for="apellido1">Primer apellido</label>
      <input type="text" id="apellido1" name="apellido1" required>

      <label for="apellido2">Segundo apellido</label>
      <input type="text" id="apellido2" name="apellido2">

      <label for="matricula">Matrícula (número)</label>
      <input type="text" id="matricula" name="matricula" required>

      <button type="submit">Registrarme y comenzar test</button>
    </form>
    <?php if ($mensaje_error !== ''): ?>
      <div class="error"><?php echo htmlspecialchars($mensaje_error); ?></div>
    <?php endif; ?>
  </div>
</body>
</html>
