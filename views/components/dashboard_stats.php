<?php
// views/components/dashboard_stats.php
?>
<div class="kpis">
    <?php foreach ($stats as $stat): ?>
        <div class="kpi">
            <div class="kpi-value"><?php echo $stat['value']; ?></div>
            <div class="kpi-label"><?php echo htmlspecialchars($stat['title']); ?></div>
        </div>
    <?php endforeach; ?>
</div>
<style>
.kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:10px;margin:0 0 16px 0}
.kpi{background:#fff;border:1px solid #ddd;padding:10px 14px;display:flex;flex-direction:column}
.kpi-value{font-size:22px;font-weight:600;color:#222}
.kpi-label{font-size:10px;text-transform:uppercase;color:#888;letter-spacing:.03em;margin-top:2px}
@media(max-width:480px){.kpis{grid-template-columns:1fr 1fr}.kpi{padding:8px 10px}.kpi-value{font-size:18px}}
</style>
