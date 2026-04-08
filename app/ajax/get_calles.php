<?php
// app/ajax/get_calles.php
require_once __DIR__ . '/../../autoload.php';
use app\models\mainModel;

$barrio_id = isset($_GET['barrio_id']) ? (int)$_GET['barrio_id'] : 0;

if ($barrio_id > 0) {
    $model = new class extends mainModel {};
    $stmt = $model->ejecutarConsulta("SELECT id, nombre FROM calles WHERE barrio_id = ? ORDER BY nombre ASC", [$barrio_id]);
    $calles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($calles);
} else {
    echo json_encode([]);
}
