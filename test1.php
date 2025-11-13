<?php
session_start();
include 'connection.php';

// Diccionario base para las ponderaciones de aptitudes (1 a 6)
$ponderaciones_base = array(
    1 => 0,
    2 => 0,
    3 => 0,
    4 => 0,
    5 => 0,
    6 => 0,
);

$ponderaciones_aptitudes = $ponderaciones_base;
if (isset($_SESSION['ponderaciones_aptitudes']) && is_array($_SESSION['ponderaciones_aptitudes'])) {
    foreach ($ponderaciones_aptitudes as $aptitud => $valor) {
        if (isset($_SESSION['ponderaciones_aptitudes'][$aptitud])) {
            $ponderaciones_aptitudes[$aptitud] = floatval($_SESSION['ponderaciones_aptitudes'][$aptitud]);
        }
    }
}
$_SESSION['ponderaciones_aptitudes'] = $ponderaciones_aptitudes;

function sumarPonderacion(&$mapa, $aptitud, $valor) {
    if (!is_numeric($aptitud)) {
        return;
    }
    $aptitud = intval($aptitud);
    if ($aptitud < 1 || $aptitud > 6 || $valor == 0) {
        return;
    }
    if (!isset($mapa[$aptitud])) {
        $mapa[$aptitud] = 0;
    }
    $mapa[$aptitud] += $valor;
}

// Inicializar o manejar el índice actual
if (!isset($_SESSION['indice_actual'])) {
    $_SESSION['indice_actual'] = 1;
}

// Manejar los botones de navegación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['selector_opciones'], $_POST['pregunta_actual'])) {
        $seleccion = $_POST['selector_opciones'];
        $aptitud_a = isset($_POST['aptitud_opcion_a']) ? intval($_POST['aptitud_opcion_a']) : 0;
        $aptitud_b = isset($_POST['aptitud_opcion_b']) ? intval($_POST['aptitud_opcion_b']) : 0;

        switch ($seleccion) {
            case 'opA1':
                sumarPonderacion($ponderaciones_aptitudes, $aptitud_a, 4);
                break;
            case 'opA2':
                sumarPonderacion($ponderaciones_aptitudes, $aptitud_a, 3);
                sumarPonderacion($ponderaciones_aptitudes, $aptitud_b, 1);
                break;
            case 'opB1':
                sumarPonderacion($ponderaciones_aptitudes, $aptitud_b, 3);
                sumarPonderacion($ponderaciones_aptitudes, $aptitud_a, 1);
                break;
            case 'opB2':
                sumarPonderacion($ponderaciones_aptitudes, $aptitud_b, 4);
                break;
        }
    }

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

    $_SESSION['ponderaciones_aptitudes'] = $ponderaciones_aptitudes;
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
    $sql_opciones = "SELECT opcion, id_apt_1 FROM `opciones-test` WHERE id_pregunta = ? ORDER BY id_opcion";
    $stmt = $conn->prepare($sql_opciones);
    $stmt->bind_param("s", $id_pregunta);
    $stmt->execute();
    $result_opciones = $stmt->get_result();
    
    // Almacenar las opciones en un array temporal
    $opciones_pregunta = array();
    while($opcion_row = $result_opciones->fetch_assoc()) {
        $opciones_pregunta[] = array(
            'texto' => $opcion_row['opcion'],
            'aptitud' => isset($opcion_row['id_apt_1']) ? intval($opcion_row['id_apt_1']) : 0
        );
    }
    
    // Guardar el array de opciones en el array principal
    $opciones[$id_pregunta] = $opciones_pregunta;
  }
}
/*
echo "<pre>";
echo "Array de Preguntas:\n";
print_r($preguntas);
echo "\n\nArray de Opciones:\n";
print_r($opciones);
echo "</pre>";
*/
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
      padding: 60px 80px;
    }

    .layout {
      display: flex;
      gap: 40px;
      align-items: flex-start;
    }

    .grid-column {
      flex: 0 0 20%;
      
      border-right: 2px solid #bfbfbf;
      padding-right: 20px;
      background: #f5f5f5;
      border-radius: 12px;
      padding: 20px;
    }

    .grid-column .grid-form {
      position: sticky;
      top: 40px;
    }

    .content-column {
      flex: 1;
      padding: 30px;
      background: #ffffff;
      border-radius: 16px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.08);
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
    .opciones-row {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      flex-wrap: wrap;
      font-size: 24px;
      margin-bottom: 10px;
    }
    .opcion-label,
    .separator {
      font-weight: 700;
    }
    .separator {
      font-size: 26px;
    }
    .opcion-text {
      max-width: 320px;
      text-align: center;
    }
    .radio-group {
      display: flex;
      justify-content: center;
      gap: 20px;
      margin: 20px 0;
      font-size: 20px;
    }
    .radio-group input {
      cursor: pointer;
      width: 20px;
      height: 20px;
    }

    /* Grilla de navegación 6 x n */
    .grid-title {
      font-size: 24px;
      font-weight: 700;
      color: #4CAF50;
      margin: 0 0 18px;
    }
    .grid-form {
      margin-bottom: 24px;
    }
    .grid-preguntas {
      display: grid;
      grid-template-columns: repeat(5, minmax(48px, 1fr));
      gap: 12px;
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

    <div class="layout">
      <div class="grid-column">
        <!-- Grilla de navegación 6 x n -->
        <div class="grid-title">Parte I</div>
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
      </div>

      <div class="content-column">
        <div class="pregunta" id="titulo-pregunta">Pregunta <?php echo $indice_actual; ?></div>
        <div class="texto-pregunta" id="texto-pregunta"><?php echo htmlspecialchars(isset($preguntas[$indice_actual]) ? $preguntas[$indice_actual] : ''); ?></div>

        <?php
          $opciones_actuales = isset($opciones[$indice_actual]) ? $opciones[$indice_actual] : array();
          $opcion_a = isset($opciones_actuales[0]['texto']) ? htmlspecialchars($opciones_actuales[0]['texto']) : '';
          $opcion_b = isset($opciones_actuales[1]['texto']) ? htmlspecialchars($opciones_actuales[1]['texto']) : '';
          $aptitud_a_actual = isset($opciones_actuales[0]['aptitud']) ? intval($opciones_actuales[0]['aptitud']) : 0;
          $aptitud_b_actual = isset($opciones_actuales[1]['aptitud']) ? intval($opciones_actuales[1]['aptitud']) : 0;
        ?>

        <form method="POST" style="margin-top: 20px; display: flex; flex-direction: column; gap: 30px;">
          <input type="hidden" name="pregunta_actual" value="<?php echo $indice_actual; ?>">
          <input type="hidden" name="aptitud_opcion_a" value="<?php echo $aptitud_a_actual; ?>">
          <input type="hidden" name="aptitud_opcion_b" value="<?php echo $aptitud_b_actual; ?>">

          <div class="opciones">
            <?php if ($opcion_a !== '' || $opcion_b !== ''): ?>
              <div class="opciones-row">
                <span class="opcion-label">a)</span>
                <span class="separator"> </span>
                <span class="opcion-text opcion-a"><?php echo $opcion_a; ?></span>
                <span class="separator"> </span>
                <div class="radio-group">
                  <input type="radio" name="selector_opciones" id="opA1" value="opA1" aria-label="opA1">
                  <input type="radio" name="selector_opciones" id="opA2" value="opA2" aria-label="opA2">
                  <input type="radio" name="selector_opciones" id="opB1" value="opB1" aria-label="opB1">
                  <input type="radio" name="selector_opciones" id="opB2" value="opB2" aria-label="opB2">
                </div>
                <?php if ($opcion_b !== ''): ?>
                  <span class="separator"> </span>
                  <span class="opcion-label">b)</span>
                  <span class="separator"> </span>
                  <span class="opcion-text opcion-b"><?php echo $opcion_b; ?></span>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>

          <div style="display: flex; justify-content: space-between; gap: 10px;">
            <?php if ($indice_actual > 1): ?>
              <button type="submit" name="anterior" style="padding: 10px 20px; font-size: 18px; cursor: pointer;">Anterior</button>
            <?php endif; ?>
            
            <?php if ($indice_actual < $total_preguntas): ?>
              <button type="submit" name="siguiente" style="padding: 10px 20px; font-size: 18px; cursor: pointer;">Siguiente</button>
            <?php endif; ?>
          </div>
        </form>

        <?php if ($indice_actual == 30): ?>
          <form action="test2.php" method="get" style="margin-top: 30px; text-align: center;">
            <?php foreach ($ponderaciones_aptitudes as $aptitud => $valor): ?>
              <input type="hidden" name="ponderaciones[<?php echo $aptitud; ?>]" value="<?php echo htmlspecialchars($valor, ENT_QUOTES, 'UTF-8'); ?>">
            <?php endforeach; ?>
            <button type="submit" style="padding: 12px 28px; font-size: 18px; cursor: pointer; background: #4CAF50; color: #ffffff; border: none; border-radius: 10px;">
              Ir a la Parte II
            </button>
          </form>
        <?php endif; ?>
      </div>
    </div>

  </div>

  <?php
    echo '<div style="margin-top:40px;background:#fff;padding:20px;border-radius:12px;">';
    echo '<h3 style="margin-top:0;">Ponderaciones actuales</h3>';
    echo '<pre style="margin:0;">' . htmlspecialchars(print_r($ponderaciones_aptitudes, true)) . '</pre>';
    echo '</div>';
  ?>

</body>
</html>
