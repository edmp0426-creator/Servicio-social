<?php
session_start();
include 'connection.php';

// Inicializar o manejar el índice actual
if (!isset($_SESSION['indice_actual'])) {
    $_SESSION['indice_actual'] = 31;
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
        $_SESSION['indice_actual'] = 31;
    }
}

// Arrays para almacenar preguntas y opciones
$preguntas = array();
$opciones = array();

// Query para obtener todas las preguntas del bloque 1
$sql_preguntas = "SELECT id_pregunta, pregunta FROM `preguntas-test` WHERE parte = 2 ORDER BY id_pregunta";
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
    $sql_opciones = "SELECT opcion, id_apt_1 FROM `opciones-test` WHERE id_pregunta = ? ORDER BY id_opcion LIMIT 4";
    $stmt = $conn->prepare($sql_opciones);
    $stmt->bind_param("s", $id_pregunta);
    $stmt->execute();
    $result_opciones = $stmt->get_result();
    
    // Almacenar las opciones en un array temporal
    $opciones_pregunta = array();
    while($opcion_row = $result_opciones->fetch_assoc()) {
        $opciones_pregunta[] = array(
          'opcion' => $opcion_row['opcion'],
          'id_apt_1' => $opcion_row['id_apt_1']
        );
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
//*/
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
      max-width: 20%;
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
      font-size: 48px;
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

    /* Grilla de navegación 6 x n */
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
    $indices = array_keys($preguntas);
    sort($indices, SORT_NUMERIC);
    $total_preguntas = count($indices);
    $min_indice = $total_preguntas ? min($indices) : 31;
    $max_indice = $total_preguntas ? max($indices) : 31;

    // Asegurar que el índice esté dentro de los límites
    if ($indice_actual > $max_indice) {
        $_SESSION['indice_actual'] = $max_indice;
        $indice_actual = $max_indice;
    } elseif ($indice_actual < $min_indice) {
        $_SESSION['indice_actual'] = $min_indice;
        $indice_actual = $min_indice;
    }
    ?>

    <div class="layout">
      <div class="grid-column">
        <!-- Grilla de navegación 5 x n -->
        <div class="grid-title">Parte II</div>
        <form method="POST" class="grid-form">
          <div class="grid-preguntas">
            <?php foreach ($indices as $i): ?>
              <button
                type="submit"
                name="ir_a"
                value="<?php echo $i; ?>"
                class="celda <?php echo ($i === $indice_actual) ? 'celda-activa' : ''; ?>">
                <?php echo $i; ?>
              </button>
            <?php endforeach; ?>
          </div>
        </form>
      </div>

      <div class="content-column">
        <div class="pregunta" id="titulo-pregunta">Pregunta <?php echo $indice_actual; ?></div>
        <div class="texto-pregunta" id="texto-pregunta"><?php echo htmlspecialchars(isset($preguntas[$indice_actual]) ? $preguntas[$indice_actual] : ''); ?></div>

        <div class="opciones">
          <?php
          $opciones_actuales = isset($opciones[$indice_actual]) ? $opciones[$indice_actual] : array();
          for ($i = 0; $i < min(count($opciones_actuales), 4); $i++) {
              $texto = isset($opciones_actuales[$i]['opcion']) ? $opciones_actuales[$i]['opcion'] : '';
              $label = '#' . ($i + 1);
              echo '<div class="opcion-row">';
              echo '<span class="opcion-label">' . htmlspecialchars($label) . '</span>';
              echo '<div class="opcion" id="opcion'.($i+1).'">';
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

        <form method="POST" style="margin-top: 40px; display: flex; justify-content: space-between; gap: 10px;">
          
          <?php if ($indice_actual > $min_indice): ?>
            <button type="submit" name="anterior" style="padding: 10px 20px; font-size: 18px; cursor: pointer;">Anterior</button>
          <?php endif; ?>
          
          <?php if ($indice_actual < $max_indice): ?>
            <button type="submit" name="siguiente" style="padding: 10px 20px; font-size: 18px; cursor: pointer;">Siguiente</button>
          <?php endif; ?>
        </form>
      </div>
    </div>

</div>

<script>
  (function() {
    const contenedorOpciones = document.querySelector('.opciones');
    if (!contenedorOpciones) return;

    const actualizarEtiquetas = () => {
      const filas = contenedorOpciones.querySelectorAll('.opcion-row');
      filas.forEach((fila, index) => {
        const label = fila.querySelector('.opcion-label');
        if (label) {
          label.textContent = '#' + (index + 1);
        }
      });
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
        }
      } else if (direccion === 'down') {
        const siguiente = fila.nextElementSibling;
        if (siguiente) {
          contenedorOpciones.insertBefore(siguiente, fila);
          actualizarEtiquetas();
        }
      }
    });

    actualizarEtiquetas();
  })();
</script>

</body>
</html>
