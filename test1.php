<?php
session_start();
include 'connection.php';

// Interpreta la cadena ansMaster y regresa el recuento por aptitud.
function interpretarAnsMaster($cadena, $aptitudes_max = 6) {
    $resultado = array_fill(1, $aptitudes_max, 0);
    $operaciones = array_filter(explode(';', $cadena));
    foreach ($operaciones as $op) {
        $partes = explode('->', $op);
        if (count($partes) !== 2) {
            continue;
        }
        $valor = intval(str_replace(array('{', '}', ' '), '', $partes[0]));
        $aptitud = intval(str_replace(array('{', '}', ' '), '', $partes[1]));
        if ($aptitud < 1 || $aptitud > $aptitudes_max || $valor === 0) {
            continue;
        }
        $resultado[$aptitud] += $valor;
    }
    return $resultado;
}

// Genera la cadena actMaster segun la seleccion realizada.
function construirActMaster($seleccion, $aptitud_a, $aptitud_b) {
    switch ($seleccion) {
        case 'opA1':
            return "{4}->{$aptitud_a};";
        case 'opA2':
            return "{3}->{$aptitud_a};{1}->{$aptitud_b};";
        case 'opB1':
            return "{1}->{$aptitud_a};{3}->{$aptitud_b};";
        case 'opB2':
            return "{4}->{$aptitud_b};";
        default:
            return '';
    }
}

// Estado inicial
$ansMaster = isset($_SESSION['ansMaster']) ? $_SESSION['ansMaster'] : '';
if (!isset($_SESSION['indice_actual'])) {
    $_SESSION['indice_actual'] = 1;
}
$respondidas_parte1 = isset($_SESSION['respondidas_parte1']) && is_array($_SESSION['respondidas_parte1'])
    ? $_SESSION['respondidas_parte1']
    : array();
$mensaje_error = '';
$mostrar_comprobacion = false;
$recuento_actual = array();

// Arrays para almacenar preguntas y opciones
$preguntas = array();
$opciones = array();

// Query para obtener todas las preguntas del bloque 1
$sql_preguntas = "SELECT id_pregunta, pregunta FROM `preguntas-test` WHERE parte = 1 ORDER BY id_pregunta";
$result_preguntas = $conn->query($sql_preguntas);
if ($result_preguntas === false) {
    echo "<pre>Query error: " . htmlspecialchars($conn->error) . "</pre>";
    $rows_preguntas = array();
} else {
    if (method_exists($result_preguntas, 'fetch_all')) {
        $rows_preguntas = $result_preguntas->fetch_all(MYSQLI_ASSOC);
    } else {
        $rows_preguntas = array();
        while ($r = $result_preguntas->fetch_assoc()) {
            $rows_preguntas[] = $r;
        }
    }
}
if (!empty($rows_preguntas)) {
    foreach ($rows_preguntas as $row) {
        $id_pregunta = $row['id_pregunta'];
        $preguntas[$id_pregunta] = $row['pregunta'];

        $sql_opciones = "SELECT opcion, id_apt_1 FROM `opciones-test` WHERE id_pregunta = ? ORDER BY id_opcion";
        $stmt = $conn->prepare($sql_opciones);
        $stmt->bind_param("s", $id_pregunta);
        $stmt->execute();
        $result_opciones = $stmt->get_result();

        $opciones_pregunta = array();
        while ($opcion_row = $result_opciones->fetch_assoc()) {
            $opciones_pregunta[] = array(
                'texto' => $opcion_row['opcion'],
                'aptitud' => isset($opcion_row['id_apt_1']) ? intval($opcion_row['id_apt_1']) : 0
            );
        }
        $opciones[$id_pregunta] = $opciones_pregunta;
    }
}

// Manejo de acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $indice_actual = $_SESSION['indice_actual'];
    $total_preguntas = count($preguntas);

    if (isset($_POST['ir_a'])) {
        $_SESSION['indice_actual'] = intval($_POST['ir_a']);
    } elseif (isset($_POST['siguiente'])) {
        $seleccion = isset($_POST['selector_opciones']) ? $_POST['selector_opciones'] : '';
        $aptitud_a = isset($_POST['aptitud_opcion_a']) ? intval($_POST['aptitud_opcion_a']) : 0;
        $aptitud_b = isset($_POST['aptitud_opcion_b']) ? intval($_POST['aptitud_opcion_b']) : 0;
        $actMaster = construirActMaster($seleccion, $aptitud_a, $aptitud_b);

        if ($actMaster === '') {
            $mensaje_error = 'Debes contestar la pregunta para avanzar.';
        } else {
            $ansMaster .= $actMaster;
            $_SESSION['ansMaster'] = $ansMaster;
            if (!in_array($indice_actual, $respondidas_parte1, true)) {
                $respondidas_parte1[] = $indice_actual;
            }
            $_SESSION['respondidas_parte1'] = $respondidas_parte1;
            if ($indice_actual >= $total_preguntas) {
                // Redirigir a test2.php manteniendo sesión
                header('Location: test2.php');
                exit;
            } else {
                $_SESSION['indice_actual'] = $indice_actual + 1;
            }
        }
    } elseif (isset($_POST['comprobar'])) {
        $mostrar_comprobacion = true;
        $recuento_actual = interpretarAnsMaster($ansMaster);
    }
}

// Ajuste de limites de navegacion
$total_preguntas = count($preguntas);
$indice_actual = $_SESSION['indice_actual'];
if ($total_preguntas > 0) {
    if ($indice_actual > $total_preguntas) {
        $_SESSION['indice_actual'] = $total_preguntas;
        $indice_actual = $total_preguntas;
    } elseif ($indice_actual < 1) {
        $_SESSION['indice_actual'] = 1;
        $indice_actual = 1;
    }
}

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
      font-size: 40px;
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
      font-size: 18px;
      flex-wrap: wrap;
    }
    .radio-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 8px;
      min-width: 140px;
    }
    .radio-group input {
      cursor: pointer;
      width: 20px;
      height: 20px;
    }
    .radio-label {
      text-align: center;
      max-width: 160px;
      line-height: 1.2;
    }

    /* Grilla de navegacion 6 x n */
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
    .celda-respondida {
      background: #c8f7c5;
      border-color: #b2e6ae;
      color: #1f5e1f;
    }

    .alerta {
      background: #fff3cd;
      color: #856404;
      border: 1px solid #ffeeba;
      padding: 12px 16px;
      border-radius: 8px;
      margin-bottom: 16px;
      font-size: 16px;
    }
  </style>
</head>
<body>
<div id="titulo-test" 
     style="
        font-size: 48px;
        font-weight: 800;
        margin-bottom: 20px;
        text-align: center;
        color: #4CAF50;
     ">
    Test de Allport
</div>

    <div class="texto-instruccion" style="font-size:24px; margin-top:-10px; margin-bottom:10px;text-align: center; color:#555;">Instrucciones: Indica tu grado de conformidad con las opciones presentadas.</div>


  <div class="contenedor">
    <?php
    $total_preguntas = count($preguntas);
    ?>

    <div class="layout">
      <div class="grid-column">
        <!-- Grilla de navegacion 6 x n -->
        <div class="grid-title">Parte I</div>
        <form method="POST" class="grid-form">
          <div class="grid-preguntas">
            <?php for ($i = 1; $i <= $total_preguntas; $i++):
              $clases = array('celda');
              if ($i === $indice_actual) $clases[] = 'celda-activa';
              if (in_array($i, $respondidas_parte1, true)) $clases[] = 'celda-respondida';
              $clase_str = implode(' ', $clases);
            ?>
              <button
                type="submit"
                name="ir_a"
                value="<?php echo $i; ?>"
                class="<?php echo $clase_str; ?>">
                <?php echo $i; ?>
              </button>
            <?php endfor; ?>
          </div>
        </form>
      </div>

      <div class="content-column">
        <?php if ($mensaje_error !== ''): ?>
          <div class="alerta"><?php echo htmlspecialchars($mensaje_error); ?></div>
        <?php endif; ?>
        
        <div class="pregunta" id="titulo-pregunta">Pregunta <?php echo $indice_actual; ?></div>
        
        <div class="texto-pregunta" id="texto-pregunta"><?php echo htmlspecialchars(isset($preguntas[$indice_actual]) ? $preguntas[$indice_actual] : ''); ?></div>

        <?php
          $opciones_actuales = isset($opciones[$indice_actual]) ? $opciones[$indice_actual] : array();
          $opcion_a = isset($opciones_actuales[0]['texto']) ? htmlspecialchars($opciones_actuales[0]['texto']) : '';
          $opcion_b = isset($opciones_actuales[1]['texto']) ? htmlspecialchars($opciones_actuales[1]['texto']) : '';
          $aptitud_a_actual = isset($opciones_actuales[0]['aptitud']) ? intval($opciones_actuales[0]['aptitud']) : 0;
          $aptitud_b_actual = isset($opciones_actuales[1]['aptitud']) ? intval($opciones_actuales[1]['aptitud']) : 0;
        ?>

        <form id="form-respuesta" method="POST" style="margin-top: 20px; display: flex; flex-direction: column; gap: 30px;">
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
                  <div class="radio-item">
                    <input type="radio" name="selector_opciones" id="opA1" value="opA1" aria-label="opA1">
                    <span class="radio-label">Totalmente de acuerdo con a)</span>
                  </div>
                  <div class="radio-item">
                    <input type="radio" name="selector_opciones" id="opA2" value="opA2" aria-label="opA2">
                    <span class="radio-label">Parcialmente de acuerdo con a)</span>
                  </div>
                  <div class="radio-item">
                    <input type="radio" name="selector_opciones" id="opB1" value="opB1" aria-label="opB1">
                    <span class="radio-label">Parcialmente de acuerdo con b)</span>
                  </div>
                  <div class="radio-item">
                    <input type="radio" name="selector_opciones" id="opB2" value="opB2" aria-label="opB2">
                    <span class="radio-label">Totalmente de acuerdo con b)</span>
                  </div>
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

          <div style="display: flex; justify-content: space-between; gap: 10px; align-items: center;">
            <button type="submit" name="comprobar" style="padding: 10px 20px; font-size: 18px; cursor: pointer;">Comprobacion</button>
            <?php if ($indice_actual <= $total_preguntas): ?>
              <button type="submit" name="siguiente" style="padding: 10px 20px; font-size: 18px; cursor: pointer;"><?php echo ($indice_actual == $total_preguntas) ? 'Ir a Parte II' : 'Siguiente'; ?></button>
            <?php endif; ?>
          </div>
        </form>
      </div>
    </div>

  </div>

  <div style="margin-top:40px;background:#fff;padding:20px;border-radius:12px;">
    <h3 style="margin-top:0;">ansMaster (debug)</h3>
    <pre style="margin:0;"><?php echo htmlspecialchars($ansMaster); ?></pre>
    <?php if ($mostrar_comprobacion): ?>
      <h4>Recuento actual</h4>
      <pre style="margin:0;"><?php echo htmlspecialchars(print_r($recuento_actual, true)); ?></pre>
    <?php endif; ?>
  </div>

  <script>
    (function() {
      const form = document.getElementById('form-respuesta');
      if (!form) return;
      form.addEventListener('submit', function(ev) {
        const esSiguiente = ev.submitter && ev.submitter.name === 'siguiente';
        if (!esSiguiente) return;
        const radios = form.querySelectorAll('input[name="selector_opciones"]');
        const seleccionado = Array.from(radios).some(r => r.checked);
        if (!seleccionado) {
          ev.preventDefault();
          alert('Debes contestar la pregunta para avanzar.');
        }
      });
    })();
  </script>

</body>
</html>
