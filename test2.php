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

// Estado inicial
if (isset($_REQUEST['ansMaster'])) {
    $ansMaster = $_REQUEST['ansMaster'];
    $_SESSION['ansMaster'] = $ansMaster;
} else {
    $ansMaster = isset($_SESSION['ansMaster']) ? $_SESSION['ansMaster'] : '';
}
if (!isset($_SESSION['indice_actual'])) {
    $_SESSION['indice_actual'] = 31;
}
$respondidas_parte2 = isset($_SESSION['respondidas_parte2']) && is_array($_SESSION['respondidas_parte2'])
    ? $_SESSION['respondidas_parte2']
    : array();
$mensaje_error = '';
$mostrar_comprobacion = false;
$recuento_actual = array();

// Arrays para almacenar preguntas y opciones
$preguntas = array();
$opciones = array();

$sql_preguntas = "SELECT id_pregunta, pregunta FROM `preguntas-test` WHERE parte = 2 ORDER BY id_pregunta";
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

        $sql_opciones = "SELECT opcion, id_apt_1 FROM `opciones-test` WHERE id_pregunta = ? ORDER BY id_opcion LIMIT 4";
        $stmt = $conn->prepare($sql_opciones);
        $stmt->bind_param("s", $id_pregunta);
        $stmt->execute();
        $result_opciones = $stmt->get_result();

        $opciones_pregunta = array();
        while ($opcion_row = $result_opciones->fetch_assoc()) {
            $opciones_pregunta[] = array(
                'opcion' => $opcion_row['opcion'],
                'id_apt_1' => $opcion_row['id_apt_1']
            );
        }
        $opciones[$id_pregunta] = $opciones_pregunta;
    }
}

// Manejo de acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $indice_actual = $_SESSION['indice_actual'];

    if (isset($_POST['ir_a'])) {
        $_SESSION['indice_actual'] = intval($_POST['ir_a']);
    } elseif (isset($_POST['siguiente'])) {
        $actMaster = isset($_POST['actMaster']) ? trim($_POST['actMaster']) : '';
        if ($actMaster === '') {
            $mensaje_error = 'Debes ordenar las opciones antes de continuar.';
        } else {
            $ansMaster .= $actMaster;
            $_SESSION['ansMaster'] = $ansMaster;
            if (!in_array($indice_actual, $respondidas_parte2, true)) {
                $respondidas_parte2[] = $indice_actual;
            }
            $_SESSION['respondidas_parte2'] = $respondidas_parte2;
            $_SESSION['indice_actual'] = $indice_actual + 1;
        }
    } elseif (isset($_POST['finalizar'])) {
        $actMaster = isset($_POST['actMaster']) ? trim($_POST['actMaster']) : '';
        if ($actMaster === '') {
            $mensaje_error = 'Debes ordenar las opciones antes de continuar.';
        } else {
            $ansMaster .= $actMaster;
            $_SESSION['ansMaster'] = $ansMaster;
            if (!in_array($indice_actual, $respondidas_parte2, true)) {
                $respondidas_parte2[] = $indice_actual;
            }
            $_SESSION['respondidas_parte2'] = $respondidas_parte2;
            // Calcular ponderaciones de aptitudes a partir del ansMaster
            $recuento_final = interpretarAnsMaster($ansMaster);
            // Guardar ponderaciones en sesión para cálculos posteriores
            $_SESSION['ponderaciones_aptitudes'] = $recuento_final;
            
            // Actualizar aptitudes en BD para el usuario loggeado
            $aptitudes_guardar = array_fill(1, 6, 0);
            foreach ($recuento_final as $apt => $valor) {
                if (isset($aptitudes_guardar[$apt])) {
                    $aptitudes_guardar[$apt] = floatval($valor);
                }
            }
            $id_alumno = isset($_SESSION['id_alumno']) ? intval($_SESSION['id_alumno']) : 0;
            if ($id_alumno > 0 && isset($conn) && $conn instanceof mysqli) {
                mysqli_report(MYSQLI_REPORT_OFF);
                try {
                    $stmtUpd = $conn->prepare(
                        "UPDATE `alumnos-test` SET `apt1`=?, `ap2`=?, `ap3`=?, `ap4`=?, `ap5`=?, `ap6`=? WHERE `id_alumno`=? LIMIT 1"
                    );
                    if ($stmtUpd) {
                        $stmtUpd->bind_param(
                            "ddddddi",
                            $aptitudes_guardar[1],
                            $aptitudes_guardar[2],
                            $aptitudes_guardar[3],
                            $aptitudes_guardar[4],
                            $aptitudes_guardar[5],
                            $aptitudes_guardar[6],
                            $id_alumno
                        );
                        $stmtUpd->execute();
                        $stmtUpd->close();
                    }
                } catch (mysqli_sql_exception $e) {
                    // Silenciar error para no interrumpir el flujo hacia resultados
                }
            }
            // Redirigir a página de resultados
            header('Location: Disenos/resultado.php');
            exit;
        }
    } elseif (isset($_POST['comprobar'])) {
        $mostrar_comprobacion = true;
        $recuento_actual = interpretarAnsMaster($ansMaster);
    }
}

$indices = array_keys($preguntas);
sort($indices, SORT_NUMERIC);
$total_preguntas = count($indices);
$min_indice = $total_preguntas ? min($indices) : 31;
$max_indice = $total_preguntas ? max($indices) : 31;

// Ajuste de limites de navegacion
$indice_actual = $_SESSION['indice_actual'];
if ($indice_actual > $max_indice) {
    $_SESSION['indice_actual'] = $max_indice;
    $indice_actual = $max_indice;
} elseif ($indice_actual < $min_indice) {
    $_SESSION['indice_actual'] = $min_indice;
    $indice_actual = $min_indice;
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
      background: #f5f5f5;
      border-radius: 12px;
      padding: 20px;
      border-right: 2px solid #bfbfbf;
    }

    .grid-column .grid-title {
      font-size: 24px;
      font-weight: 700;
      color: #4CAF50;
      margin: 0 0 18px;
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
      display: flex;
      flex-direction: column;
      gap: 10px;
      font-size: 28px;
    }
    .opcion-row {
      display: flex;
      align-items: center;
      gap: 16px;
    }
    .opcion-label {
      font-weight: 700;
      color: #4CAF50;
      min-width: 64px;
    }

    .opcion {
      display: flex;
      align-items: center;
      gap: 16px;
      background: #fff;
      padding: 12px 16px;
      border-radius: 8px;
      border: 1px solid #d0d0d0;
      flex: 1;
    }
    .opcion-texto {
      flex: 1;
    }
    .opcion-controles {
      display: flex;
      gap: 6px;
    }
    .btn-mover {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      border: 1px solid #4CAF50;
      background: #fff;
      color: #4CAF50;
      font-size: 18px;
      cursor: pointer;
      transition: background 0.15s ease, color 0.15s ease;
    }
    .btn-mover:hover {
      background: #4CAF50;
      color: #fff;
    }

    /* Grilla de navegacion 5 x n */
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

  <div class="texto-instruccion" style="font-size:24px; margin-top:-10px; margin-bottom:10px;text-align: center; color:#555;">Instrucciones: Ordena las opciones segun tu preferencia.</div>

  <div class="contenedor">
    <div class="layout">
      <div class="grid-column">
        <!-- Grilla de navegacion 5 x n -->
        <div class="grid-title">Parte II</div>
        <form method="POST" class="grid-form">
          <div class="grid-preguntas">
            <?php foreach ($indices as $i):
              $clases = array('celda');
              if ($i === $indice_actual) $clases[] = 'celda-activa';
              if (in_array($i, $respondidas_parte2, true)) $clases[] = 'celda-respondida';
              $clase_str = implode(' ', $clases);
            ?>
              <button
                type="submit"
                name="ir_a"
                value="<?php echo $i; ?>"
                class="<?php echo $clase_str; ?>">
                <?php echo $i; ?>
              </button>
            <?php endforeach; ?>
          </div>
        </form>
      </div>

      <div class="content-column">
        <?php if ($mensaje_error !== ''): ?>
          <div class="alerta"><?php echo htmlspecialchars($mensaje_error); ?></div>
        <?php endif; ?>
        
        <div class="pregunta" id="titulo-pregunta">Pregunta <?php echo $indice_actual; ?></div>
        
        <div class="texto-pregunta" id="texto-pregunta"><?php echo htmlspecialchars(isset($preguntas[$indice_actual]) ? $preguntas[$indice_actual] : ''); ?></div>

        <div class="opciones" id="opciones-lista">
          <?php
          $opciones_actuales = isset($opciones[$indice_actual]) ? $opciones[$indice_actual] : array();
          for ($i = 0; $i < min(count($opciones_actuales), 4); $i++) {
              $texto = isset($opciones_actuales[$i]['opcion']) ? $opciones_actuales[$i]['opcion'] : '';
              $aptitud_op = isset($opciones_actuales[$i]['id_apt_1']) ? intval($opciones_actuales[$i]['id_apt_1']) : 0;
              echo '<div class="opcion-row" data-aptitud="'.htmlspecialchars($aptitud_op).'">';
              echo '<span class="opcion-label">#' . ($i + 1) . '</span>';
              echo '<div class="opcion">';
              echo '<span class="opcion-texto">' . htmlspecialchars($texto) . '</span>';
              echo '<div class="opcion-controles">';
              echo '<button type="button" class="btn-mover" data-dir="up" aria-label="Mover opcion hacia arriba">&#8593;</button>';
              echo '<button type="button" class="btn-mover" data-dir="down" aria-label="Mover opcion hacia abajo">&#8595;</button>';
              echo '</div>';
              echo '</div>';
              echo '</div>';
          }
          ?>
        </div>

        <form id="form-parte2" method="POST" style="margin-top: 40px; display: flex; justify-content: space-between; gap: 10px; align-items: center;">
          <input type="hidden" name="actMaster" id="actMaster" value="">
          <button type="submit" name="comprobar" style="padding: 10px 20px; font-size: 18px; cursor: pointer;">Comprobacion</button>
          <?php if ($indice_actual < $max_indice): ?>
            <button type="submit" name="siguiente" style="padding: 10px 20px; font-size: 18px; cursor: pointer;">Siguiente</button>
          <?php else: ?>
            <button type="submit" name="finalizar" style="padding: 10px 20px; font-size: 18px; cursor: pointer;">Finalizar y ver resultados</button>
          <?php endif; ?>
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
    const contenedorOpciones = document.getElementById('opciones-lista');
    const inputActMaster = document.getElementById('actMaster');
    if (!contenedorOpciones || !inputActMaster) return;

    const actualizarEtiquetas = () => {
      const filas = contenedorOpciones.querySelectorAll('.opcion-row');
      filas.forEach((fila, index) => {
        const label = fila.querySelector('.opcion-label');
        if (label) {
          label.textContent = '#' + (index + 1);
        }
      });
    };

    const actualizarActMaster = () => {
      const filas = Array.from(contenedorOpciones.querySelectorAll('.opcion-row'));
      const secciones = filas.map((fila, idx) => {
        const apt = fila.getAttribute('data-aptitud');
        // Posiciones: 1->valor 4, 2->valor 3, 3->valor 2, 4->valor 1
        const valor = 4 - idx;
        return `{${valor}}->{${apt}};`;
      });
      inputActMaster.value = secciones.join('');
    };

    contenedorOpciones.addEventListener('click', function(evento) {
      const boton = evento.target.closest('.btn-mover');
      if (!boton) return;

      const fila = boton.closest('.opcion-row');
      if (!fila) return;

      const direccion = boton.getAttribute('data-dir');

      if (direccion === 'up') {
        const anterior = fila.previousElementSibling;
        if (anterior) {
          contenedorOpciones.insertBefore(fila, anterior);
          actualizarEtiquetas();
          actualizarActMaster();
        }
      } else if (direccion === 'down') {
        const siguiente = fila.nextElementSibling;
        if (siguiente) {
          contenedorOpciones.insertBefore(siguiente, fila);
          actualizarEtiquetas();
          actualizarActMaster();
        }
      }
    });

    actualizarEtiquetas();
    actualizarActMaster();
  })();
</script>

</body>
</html>
