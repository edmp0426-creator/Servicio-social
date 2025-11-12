<?php
session_start();
include 'connection.php';

// Inicializar o manejar el índice actual
if (!isset($_SESSION['indice_actual'])) {
    $_SESSION['indice_actual'] = 1;
}

// Manejar los botones de navegación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ir directamente a una pregunta específica desde la grilla
    if (isset($_POST['ir_a'])) {
        $destino = intval($_POST['ir_a']);
        $_SESSION['indice_actual'] = $destino;
    } elseif (isset($_POST['siguiente'])) {
        $_SESSION['indice_actual']++;
    } elseif (isset($_POST['anterior'])) {
        $_SESSION['indice_actual']--;
    } elseif (isset($_POST['primera'])) {
        $_SESSION['indice_actual'] = 1;
    }
}

// Arrays para almacenar preguntas y opciones
$preguntas = array();
$opciones = array();

// Query para obtener todas las preguntas del bloque 1
$sql_preguntas = "SELECT id_pregunta, pregunta FROM `preguntas-test` WHERE parte = 1 ORDER BY id_pregunta";
$result_preguntas = $conn->query($sql_preguntas);
// Convertir todas las filas devueltas a un array y mostrar para depuración
if ($result_preguntas === false) {
  // Mostrar error de consulta si falla
  echo "<pre>Query error: " . htmlspecialchars($conn->error) . "</pre>";
  $rows_preguntas = array();
} else {
  // fetch_all devuelve un array asociativo con todas las filas
  if (method_exists($result_preguntas, 'fetch_all')) {
    $rows_preguntas = $result_preguntas->fetch_all(MYSQLI_ASSOC);
  } else {
    // Fallback: construir manualmente el array si fetch_all no está disponible
    $rows_preguntas = array();
    while ($r = $result_preguntas->fetch_assoc()) {
      $rows_preguntas[] = $r;
    }
  }
}
// Llenar el array de preguntas a partir de las filas obtenidas
if (!empty($rows_preguntas)) {
  foreach ($rows_preguntas as $row) {
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
}

echo "<pre>";
echo "Array de Preguntas:\n";
print_r($preguntas);
echo "\n\nArray de Opciones:\n";
print_r($opciones);
echo "</pre>";

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

    /* Grilla de navegación 6 x n */
    .grid-form {
      margin-bottom: 24px;
    }
    .grid-preguntas {
      display: grid;
      grid-template-columns: repeat(6, minmax(48px, 1fr));
      gap: 8px;
      align-items: stretch;
    }
    .celda {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 10px 0;
      font-size: 16px;
      background: #ffffff;
      border: 1px solid #bfbfbf;
      border-radius: 6px;
      cursor: pointer;
      color: #333;
      transition: background 0.15s ease, transform 0.05s ease;
    }
    .celda:hover { background: #f1f1f1; }
    .celda:active { transform: scale(0.98); }
    .celda-activa {
      background: #4CAF50;
      color: #fff;
      border-color: #4CAF50;
      font-weight: 700;
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

    <!-- Grilla de navegación 6 x n -->
    <form method="POST" class="grid-form">
      <div class="grid-preguntas">
        <?php for ($i = 1; $i <= $total_preguntas; $i++): ?>
          <button
            type="submit"
            name="ir_a"
            value="<?php echo $i; ?>"
            class="celda <?php echo ($i === $indice_actual) ? 'celda-activa' : ''; ?>">
            <?php echo $i; ?>
          </button>
        <?php endfor; ?>
      </div>
    </form>

    <div class="pregunta" id="titulo-pregunta">Pregunta <?php echo $indice_actual; ?></div>
    <div class="texto-pregunta" id="texto-pregunta"><?php echo htmlspecialchars(isset($preguntas[$indice_actual]) ? $preguntas[$indice_actual] : ''); ?></div>

    <div class="opciones">
      <?php
      $opciones_actuales = isset($opciones[$indice_actual]) ? $opciones[$indice_actual] : array();
      for ($i = 0; $i < min(count($opciones_actuales), 4); $i++) {
          echo '<div class="opcion" id="opcion'.($i+1).'">' . htmlspecialchars($opciones_actuales[$i]) . '</div>';
      }
      ?>
    </div>

    <form method="POST" style="margin-top: 40px; display: flex; justify-content: space-between; gap: 10px;">
      <button type="submit" name="primera" style="padding: 10px 20px; font-size: 18px; cursor: pointer; background-color: #4CAF50; color: white; border: none; border-radius: 4px;">Primera Pregunta</button>
      
      <?php if ($indice_actual > 1): ?>
        <button type="submit" name="anterior" style="padding: 10px 20px; font-size: 18px; cursor: pointer;">Anterior</button>
      <?php endif; ?>
      
      <?php if ($indice_actual < $total_preguntas): ?>
        <button type="submit" name="siguiente" style="padding: 10px 20px; font-size: 18px; cursor: pointer;">Siguiente</button>
      <?php endif; ?>
    </form>

  </div>

</body>
</html>
