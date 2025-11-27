<?php
session_start();
require_once __DIR__ . '/../connection.php';

$aptitudes = array(
    1 => array('label' => 'Teorico', 'valor' => 0),
    2 => array('label' => 'Economico', 'valor' => 0),
    3 => array('label' => 'Estetico', 'valor' => 0),
    4 => array('label' => 'Social', 'valor' => 0),
    5 => array('label' => 'Politico', 'valor' => 0),
    6 => array('label' => 'Religioso', 'valor' => 0),
);
$error_msg = '';

if (isset($conn) && $conn instanceof mysqli) {
    mysqli_report(MYSQLI_REPORT_OFF);
    try {
        $id_alumno = isset($_SESSION['id_alumno']) ? intval($_SESSION['id_alumno']) : 0;
        if ($id_alumno > 0) {
            $stmt = $conn->prepare("SELECT * FROM `alumnos-test` WHERE `id_alumno` = ? LIMIT 1");
            $stmt->bind_param("i", $id_alumno);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $sql = "SELECT * FROM `alumnos-test` ORDER BY `id_alumno` DESC LIMIT 1";
            $result = $conn->query($sql);
        }
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $aptitudes[1]['valor'] = isset($row['apt1']) ? intval($row['apt1']) : 0;
            $aptitudes[2]['valor'] = isset($row['ap2']) ? intval($row['ap2']) : (isset($row['apt2']) ? intval($row['apt2']) : 0);
            $aptitudes[3]['valor'] = isset($row['ap3']) ? intval($row['ap3']) : (isset($row['apt3']) ? intval($row['apt3']) : 0);
            $aptitudes[4]['valor'] = isset($row['ap4']) ? intval($row['ap4']) : (isset($row['apt4']) ? intval($row['apt4']) : 0);
            $aptitudes[5]['valor'] = isset($row['ap5']) ? intval($row['ap5']) : (isset($row['apt5']) ? intval($row['apt5']) : 0);
            $aptitudes[6]['valor'] = isset($row['ap6']) ? intval($row['ap6']) : (isset($row['apt6']) ? intval($row['apt6']) : 0);
        } else {
            $error_msg = 'No se encontraron registros en la tabla alumnos-test.';
        }
    } catch (mysqli_sql_exception $e) {
        $error_msg = 'Error al consultar la tabla alumnos-test: ' . $e->getMessage();
    }
}

$labels_js = json_encode(array_column($aptitudes, 'label'), JSON_UNESCAPED_UNICODE);
$datos_js = json_encode(array_column($aptitudes, 'valor'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultados - Radar</title>
    <style>
      body { font-family: Arial, sans-serif; margin: 30px; background: #f7f7f7; }
      .panel { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 6px 14px rgba(0,0,0,0.08); }
      h1 { margin-top: 0; color: #0b7b4c; }
      table { width: 100%; border-collapse: collapse; margin-top: 16px; }
      th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
      th { background: #e8f5ef; }
      .alert { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; padding: 12px; border-radius: 8px; margin-bottom: 12px; }
      .grafica { max-width: 720px; margin: 30px auto; }
    </style>
</head>
<body>
  <div class="panel">
    <h1>Resultados del Test</h1>
    <?php if ($error_msg !== ''): ?>
      <div class="alert"><?php echo htmlspecialchars($error_msg); ?></div>
    <?php endif; ?>

    <table>
      <thead>
        <tr>
          <th>ID/AP</th>
          <th>Aptitud</th>
          <th>Valor</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($aptitudes as $id => $data): ?>
          <tr>
            <td><?php echo htmlspecialchars($id); ?></td>
            <td><?php echo htmlspecialchars($data['label']); ?></td>
            <td><?php echo htmlspecialchars($data['valor']); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="grafica">
      <canvas id="radarResultados"></canvas>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    const labels = <?php echo $labels_js; ?>;
    const datos = <?php echo $datos_js; ?>;
    const ctx = document.getElementById('radarResultados');

    new Chart(ctx, {
      type: 'radar',
      data: {
        labels,
        datasets: [{
          label: 'Aptitudes',
          data: datos,
          backgroundColor: 'rgba(0, 128, 96, 0.35)',
          borderColor: 'rgba(0, 128, 96, 1)',
          pointBackgroundColor: 'rgba(0, 128, 96, 1)',
          pointBorderColor: '#ffffff',
          pointRadius: 3,
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: true } },
        scales: {
          r: {
            beginAtZero: true,
            suggestedMax: Math.max(...datos, 10),
            ticks: { display: false },
            grid: { color: '#cccccc' },
            angleLines: { color: '#cccccc' },
            pointLabels: { font: { size: 12 }, color: '#333' }
          }
        }
      }
    });
  </script>
</body>
</html>
