<?php
// views/gestor/exportar_pagos.php — Exportación CSV de pagos
// Se llama desde router.php; genera CSV y termina (sin layout).

$user = check_dashboard_access([1, 2]);

global $pdo;
if (empty($pdo)) {
    $pdo = (new \app\models\mainModel())->conectar();
}

$f_barrio = (int)($_GET['barrio_id'] ?? 0);
$f_mes = (int)($_GET['mes'] ?? 0);
$f_anio = (int)($_GET['anio'] ?? 0);

$where = [];
$params = [];
if ($f_barrio > 0) {
    $where[] = "v.barrio_id = ?";
    $params[] = $f_barrio;
}
if ($f_mes > 0) {
    $where[] = "c.mes = ?";
    $params[] = $f_mes;
}
if ($f_anio > 0) {
    $where[] = "c.anio = ?";
    $params[] = $f_anio;
}
$whereSql = $where ? "WHERE " . implode(" AND ", $where) : "";

$rs = $pdo->prepare("
    SELECT c.id, v.numero_casa, v.propietario, v.direccion, 
           b.nombre as barrio, c.nombre as calle,
           c2.mes, c2.anio, c2.monto, c2.estado, c2.referencia_pago, c2.fecha_emision,
           c2.observaciones
    FROM viviendas v
    JOIN barrios b ON v.barrio_id = b.id
    LEFT JOIN calles c ON v.calle_id = c.id
    LEFT JOIN cobros c2 ON c2.vivienda_id = v.id
    $whereSql
    ORDER BY b.nombre, c.nombre, v.numero_casa, c2.anio DESC, c2.mes DESC
");
$rs->execute($params);
$rows = $rs->fetchAll(PDO::FETCH_ASSOC);

$meses_nombres = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

$filename = 'exportacion_pagos_' . date('Ymd_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

fputcsv($output, ['#', 'Casa', 'Propietario', 'Direccion', 'Barrio', 'Calle', 'Periodo', 'Monto', 'Estado', 'Referencia', 'Fecha Emision', 'Observaciones']);

$idx = 1;
foreach ($rows as $r) {
    fputcsv($output, [
        $idx++,
        html_entity_decode($r['numero_casa'] ?? '', ENT_QUOTES, 'UTF-8'),
        html_entity_decode($r['propietario'] ?? '', ENT_QUOTES, 'UTF-8'),
        html_entity_decode($r['direccion'] ?? '', ENT_QUOTES, 'UTF-8'),
        html_entity_decode($r['barrio'] ?? '', ENT_QUOTES, 'UTF-8'),
        html_entity_decode($r['calle'] ?? '', ENT_QUOTES, 'UTF-8'),
        ($r['mes'] ? ($meses_nombres[$r['mes']] ?? '') . ' ' . ($r['anio'] ?? '') : ''),
        number_format((float)$r['monto'], 2),
        $r['estado'] ?? '',
        $r['referencia_pago'] ?? '',
        $r['fecha_emision'] ?? '',
        $r['observaciones'] ?? '',
    ]);
}

fclose($output);
exit;
