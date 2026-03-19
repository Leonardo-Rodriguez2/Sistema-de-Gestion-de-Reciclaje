<?php
// views/components/dashboard_stats.php
// Se asume que $stats es un array de arrays: [ [title, value, color, icon], ... ]
?>

<section class="grid dashboard-stats-grid">
    <?php foreach ($stats as $stat): ?>
        <div class="card stat-card" style="border-top-color: <?php echo $stat['color'] ?? 'var(--primary)'; ?>;">
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
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
    }
    
    .stat-card {
        background: #FFFFFF;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        border-top: 4px solid var(--primary);
        transition: transform 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.1);
    }
    
    .stat-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .stat-info h3 {
        margin: 0;
        font-size: 15px;
        color: #6B7280;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .stat-info .value {
        font-size: 28px;
        font-weight: 700;
        color: #111827;
        margin-top: 8px;
    }
    
    .stat-icon {
        background: rgba(0,0,0,0.03);
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
