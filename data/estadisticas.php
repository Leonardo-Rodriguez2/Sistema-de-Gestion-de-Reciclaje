<?php
// estadisticas.php
require_once('conexion.php');

// Fechas mes actual y anterior
$mesActual = date('Y-m');
$mesAnteriorObj = (new DateTime($mesActual . '-01'))->modify('-1 month');
$mesAnterior = $mesAnteriorObj->format('Y-m');

// Tipos a mostrar
$tipos = [
    'Total'    => '',
    'Plástico' => 'Plástico',
    'Papel'    => 'Papel',
    'Vidrio'   => 'Vidrio',
];

// Cálculo estadísticas
$estadisticas = [];
foreach ($tipos as $clave => $valor) {
    $condAct = $valor === ''
        ? "DATE_FORMAT(fecha_reporte, '%Y-%m') = '$mesActual'"
        : "tipo_residuo = '$valor' AND DATE_FORMAT(fecha_reporte, '%Y-%m') = '$mesActual'";
    $resAct = $pdo->query("SELECT SUM(cantidad) AS total FROM reportes WHERE $condAct");
    $totAct = (float)($resAct->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

    $condAnt = $valor === ''
        ? "DATE_FORMAT(fecha_reporte, '%Y-%m') = '$mesAnterior'"
        : "tipo_residuo = '$valor' AND DATE_FORMAT(fecha_reporte, '%Y-%m') = '$mesAnterior'";
    $resAnt = $pdo->query("SELECT SUM(cantidad) AS total FROM reportes WHERE $condAnt");
    $totAnt = (float)($resAnt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

    $pct = null;
    if ($totAnt > 0) {
        $pct = round((($totAct - $totAnt) / $totAnt) * 100, 1);
    }

    $estadisticas[$clave] = ['actual' => $totAct, 'pct' => $pct];
}

// Progreso mensual
$anioActual = date('Y');
$resMensual = $pdo->query("SELECT DATE_FORMAT(fecha_reporte, '%Y-%m') AS mes, SUM(cantidad) AS total FROM reportes WHERE YEAR(fecha_reporte) = $anioActual GROUP BY mes ORDER BY mes");
$meses = []; $valMensual = [];
while ($r = $resMensual->fetch(PDO::FETCH_ASSOC)) {
    $meses[] = $r['mes'];
    $valMensual[] = (float)$r['total'];
}

// Distribución por material
$resDist = $pdo->query("SELECT tipo_residuo, SUM(cantidad) AS total FROM reportes GROUP BY tipo_residuo");
$mat = []; $valDist = [];
while ($r = $resDist->fetch(PDO::FETCH_ASSOC)) {
    $mat[] = $r['tipo_residuo'];
    $valDist[] = (float)$r['total'];
}

// Reportes iniciales (5)
$limitInicial = 5;
$resAct = $pdo->query("SELECT fecha_reporte, tipo_residuo, cantidad, ubicacion_nombre, estado FROM reportes ORDER BY fecha_reporte DESC LIMIT $limitInicial");
$reportes = [];
while ($f = $resAct->fetch(PDO::FETCH_ASSOC)) {
    $reportes[] = $f;
}

// Total de reportes
$totalReportes = $pdo->query("SELECT COUNT(*) AS total FROM reportes")->fetch(PDO::FETCH_ASSOC)['total'];

// Si solo estamos requiriendo los datos (desde el index de estadísticas)
if (!isset($_GET['limite'])) {
    return compact('estadisticas', 'meses', 'valMensual', 'mat', 'valDist', 'reportes', 'totalReportes');
}

// Si es una petición AJAX para cargar más reportes
$limite = (int)$_GET['limite'];
$offset = $limite - 5;  // Asumimos que la primera página comienza con 5 reportes

// Query para obtener los reportes con el límite
$resAct = $pdo->query("SELECT fecha_reporte, tipo_residuo, cantidad, ubicacion_nombre, estado FROM reportes ORDER BY fecha_reporte DESC LIMIT $offset, 5");
$reportesAjax = [];
while ($f = $resAct->fetch(PDO::FETCH_ASSOC)) {
    $reportesAjax[] = $f;
}

// Total de reportes para AJAX
$totalReportesAjax = $totalReportes;

// Respuesta en JSON
header('Content-Type: application/json');
echo json_encode([
    'reportes' => $reportesAjax,
    'totalReportes' => $totalReportesAjax
]);
exit;
?>