<?php
// views/gestor/ver_recibo.php
$user = check_dashboard_access([1, 2]);

$cobro_id = (int)($_GET['cobro_id'] ?? 0);

if (!$cobro_id) {
    die("ID de cobro no proporcionado.");
}

// Obtener datos del pago/cobro
$sql = "SELECT c.*, v.propietario, v.direccion, v.numero_casa, b.nombre as barrio_nombre, ca.nombre as calle_nombre
        FROM cobros c
        JOIN viviendas v ON c.vivienda_id = v.id
        JOIN barrios b ON v.barrio_id = b.id
        LEFT JOIN calles ca ON v.calle_id = ca.id
        WHERE c.id = ? AND c.estado = 'Pagado'";

$stmt = $pdo->prepare($sql);
$stmt->execute([$cobro_id]);
$pago = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pago) {
    die("El pago no existe o aún no ha sido procesado como 'Pagado'.");
}

// Obtener fecha del mes en texto
$meses = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
$mes_texto = $meses[$pago['mes']];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo_<?= $pago['id'] ?>_EcoCusco</title>
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; color: #1f2937; margin: 0; padding: 40px; background: #f3f4f6; }
        .receipt-container { background: white; max-width: 800px; margin: 0 auto; padding: 40px; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border-top: 10px solid #10b981; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; border-bottom: 1px solid #e5e7eb; padding-bottom: 20px; }
        .brand h1 { margin: 0; color: #10b981; font-size: 28px; letter-spacing: -0.025em; font-weight: 800; }
        .receipt-info { text-align: right; }
        .receipt-info h2 { margin: 0; font-size: 14px; text-transform: uppercase; color: #6b7280; letter-spacing: 0.05em; }
        .receipt-info p { margin: 5px 0 0; font-weight: 700; font-size: 18px; }
        
        .details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 40px; }
        .section-title { font-size: 11px; font-weight: 700; color: #9ca3af; text-transform: uppercase; margin-bottom: 8px; border-bottom: 1px solid #f3f4f6; padding-bottom: 4px; }
        .info-item { margin-bottom: 15px; }
        .info-label { font-size: 12px; color: #6b7280; }
        .info-value { font-size: 15px; font-weight: 600; }

        .table { width: 100%; border-collapse: collapse; margin-bottom: 40px; }
        .table th { background: #f9fafb; padding: 12px; text-align: left; font-size: 12px; color: #4b5563; border-bottom: 2px solid #e5e7eb; }
        .table td { padding: 15px 12px; border-bottom: 1px solid #f3f4f6; }
        .total-row { display: flex; justify-content: flex-end; align-items: center; gap: 20px; }
        .total-label { font-size: 16px; font-weight: 600; }
        .total-value { font-size: 32px; font-weight: 800; color: #10b981; }

        .footer { margin-top: 60px; text-align: center; font-size: 12px; color: #9ca3af; border-top: 1px solid #f3f4f6; padding-top: 20px; }
        .print-btn { position: fixed; bottom: 20px; right: 20px; padding: 12px 24px; background: #111827; color: white; border: none; border-radius: 100px; font-weight: 600; cursor: pointer; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
        
        @media print {
            body { background: white; padding: 0; }
            .receipt-container { box-shadow: none; border-radius: 0; border-top: none; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>

<div class="receipt-container">
    <div class="header">
        <div class="brand">
            <h1>EcoCusco</h1>
            <p style="font-size: 12px; color: #6b7280; margin: 4px 0 0;">Gestión Integral de Reciclaje</p>
        </div>
        <div class="receipt-info">
            <h2>Comprobante de Pago</h2>
            <p>N° <?= str_pad($pago['id'], 6, '0', STR_PAD_LEFT) ?></p>
            <div style="font-size: 12px; color: #6b7280; margin-top: 5px;"><?= date('d/m/Y') ?></div>
        </div>
    </div>

    <div class="details-grid">
        <div>
            <div class="section-title">Información del Vecino</div>
            <div class="info-item">
                <div class="info-label">Propietario / Familia</div>
                <div class="info-value"><?= htmlspecialchars($pago['propietario']) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Ubicación Registrada</div>
                <div class="info-value">
                    Barrio: <?= htmlspecialchars($pago['barrio_nombre']) ?><br>
                    Calle: <?= htmlspecialchars($pago['calle_nombre'] ?? 'S/N') ?>
                </div>
            </div>
        </div>
        <div>
            <div class="section-title">Resumen de Cuenta</div>
            <div class="info-item">
                <div class="info-label">Dirección Fiscal</div>
                <div class="info-value"><?= htmlspecialchars($pago['direccion']) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Número de Casa</div>
                <div class="info-value">#<?= htmlspecialchars($pago['numero_casa']) ?></div>
            </div>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Concepto / Descripción</th>
                <th>Periodo</th>
                <th style="text-align: right;">Monto</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>Pago de Servicio de Recolección</strong><br>
                    <small style="color: #6b7280;">Tipo: Servicio Mensual</small>
                </td>
                <td><?= $mes_texto ?> de <?= $pago['anio'] ?></td>
                <td style="text-align: right; font-weight: 700;">S/ <?= number_format($pago['monto'], 2) ?></td>
            </tr>
            <?php if ($pago['tipo_cobro'] == 'Multa'): ?>
            <tr>
                <td>
                    <strong>Multa por Mora / Retraso</strong><br>
                    <small style="color: #6b7280;">Penalidad por cumplimiento tardío</small>
                </td>
                <td>-</td>
                <td style="text-align: right; font-weight: 700;">S/ <?= number_format($pago['monto'], 2) ?></td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="total-row">
        <div class="total-label">Subtotal Liquido:</div>
        <div class="total-value">S/ <?= number_format($pago['monto'], 2) ?></div>
    </div>

    <div class="footer">
        <p>Este documento es un comprobante válido de pago electrónico generado por el sistema EPSIC.</p>
        <p>Gracias por contribuir a un Cusco más limpio y sostenible.</p>
    </div>
</div>

<button class="print-btn" onclick="window.print()">🖨️ Imprimir Recibo</button>

</body>
</html>
