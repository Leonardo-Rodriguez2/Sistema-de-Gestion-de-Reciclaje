<?php
// views/components/dashboard_stats.php
// Se asume que $stats es un array de arrays: [ [title, value, color, icon], ... ]
?>

<section class="grid dashboard-stats-grid">
    <?php foreach ($stats as $stat): ?>
        <div class="card stat-card" style="--primary: <?php echo $stat['color'] ?? '#374151'; ?>;">
            <div class="stat-content">
                <div class="stat-info">
                    <h3><?php echo htmlspecialchars($stat['title']); ?></h3>
                    <div class="value"><?php echo $stat['value']; ?></div>
                </div>
                <?php if (isset($stat['icon'])): ?>
                    <div class="stat-icon" style="color: <?php echo $stat['color'] ?? 'var(--primary)'; ?>;">
                        <?php echo $stat['icon']; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</section>

<style>
    .dashboard-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 15px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: #FFFFFF;
        padding: 18px;
        border-radius: 8px;
        border: 1px solid #E5E7EB;
        position: relative;
    }
    
    .stat-card::before {
        content: "";
        position: absolute;
        top: 0; left: 0; bottom: 0;
        width: 4px;
        border-radius: 8px 0 0 8px;
        background: var(--primary);
    }
    
    .stat-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .stat-info h3 {
        margin: 0;
        font-size: 12px;
        color: #6B7280;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .stat-info .value {
        font-size: 22px;
        font-weight: 700;
        color: #111827;
        margin-top: 5px;
    }
    
    .stat-icon {
        background: #F9FAFB;
        width: 38px;
        height: 38px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        opacity: 0.7;
    }
</style>
