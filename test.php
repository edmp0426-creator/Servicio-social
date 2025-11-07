<?php
session_start();
include 'connection.php';

// Inicializar o manejar el índice actual
if (!isset($_SESSION['indice_actual'])) {
    $_SESSION['indice_actual'] = 1;
}

// Manejar los botones de navegación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['siguiente'])) {
        $_SESSION['indice_actual']++;
    } elseif (isset($_POST['anterior'])) {
        $_SESSION['indice_actual']--;
    }
}

// Arrays para almacenar preguntas y opciones
$preguntas = array();
$opciones = array();

// Query para obtener todas las preguntas del bloque 1
$sql_preguntas = "SELECT id_pregunta, pregunta FROM `preguntas-test` WHERE bloque = 1 ORDER BY id_pregunta";
$result_preguntas = $conn->query($sql_preguntas);

// Llenar el array de preguntas
while($row = $result_preguntas->fetch_assoc()) {
    $id_pregunta = $row['id_pregunta'];
    $preguntas[$id_pregunta] = $row['pregunta'];
    
    // Query para obtener las opciones de cada pregunta
    $sql_opciones = "SELECT opcion FROM `opciones-test` WHERE id_pregunta = ? ORDER BY id_opcion";
    $stmt = $conn->prepare($sql_opciones);
    $stmt->bind_param("s", $id_pregunta);
    $stmt->execute();
    $result_opciones = $stmt->get_result();
    
    // Almacenar las opciones en un array temporal
    $opciones_pregunta = array();
    while($opcion_row = $result_opciones->fetch_assoc()) {
        $opciones_pregunta[] = $opcion_row['opcion'];
    }
    
    // Guardar el array de opciones en el array principal
    $opciones[$id_pregunta] = $opciones_pregunta;
}

// Ejemplo de cómo acceder a los datos:
// $preguntas[1] contendrá el texto de la pregunta con id_pregunta = 1
// $opciones[1] contendrá un array con todas las opciones de la pregunta 1

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pregunta</title>
  <style>
    body {
      background-color: #d9d9d9; /* gris claro del fondo */
      font-family: Arial, sans-serif;
      padding: 80px;
    }

    .pregunta {
      font-size: 48px;
      font-weight: 800;
      margin-bottom: 20px;
    }

    .texto-pregunta {
      font-size: 28px;
      margin-bottom: 40px;
    }

    .opciones {
      font-size: 28px;
      line-height: 2;
    }

    .opcion {
      margin-bottom: 10px;
    }
  </style>
</head>
<body>

  <div class="contenedor">
    <?php
    $indice_actual = $_SESSION['indice_actual'];
    $total_preguntas = count($preguntas);

    // Asegurar que el índice esté dentro de los límites
    if ($indice_actual > $total_preguntas) {
        $_SESSION['indice_actual'] = $total_preguntas;
        $indice_actual = $total_preguntas;
    } elseif ($indice_actual < 1) {
        $_SESSION['indice_actual'] = 1;
        $indice_actual = 1;
    }
    ?>

    <div class="pregunta" id="titulo-pregunta">Pregunta <?php echo $indice_actual; ?></div>
    <div class="texto-pregunta" id="texto-pregunta"><?php echo htmlspecialchars($preguntas[$indice_actual]); ?></div>

    <div class="opciones">
      <?php
      $opciones_actuales = $opciones[$indice_actual];
      for ($i = 0; $i < min(count($opciones_actuales), 4); $i++) {
          echo '<div class="opcion" id="opcion'.($i+1).'">' . htmlspecialchars($opciones_actuales[$i]) . '</div>';
      }
      ?>
    </div>

    <form method="POST" style="margin-top: 40px; display: flex; justify-content: space-between;">
      <?php if ($indice_actual > 1): ?>
        <button type="submit" name="anterior" style="padding: 10px 20px; font-size: 18px; cursor: pointer;">Anterior</button>
      <?php endif; ?>
      
      <?php if ($indice_actual < $total_preguntas): ?>
        <button type="submit" name="siguiente" style="padding: 10px 20px; font-size: 18px; cursor: pointer; margin-left: auto;">Siguiente</button>
      <?php endif; ?>
    </form>
  </div>

</body>
</html>
