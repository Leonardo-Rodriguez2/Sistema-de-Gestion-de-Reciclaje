<?php
// views/components/dashboard_alerts.php
// Se asume que $exito y $error son las variables que contienen los mensajes.
?>

<?php if (isset($exito) && $exito): ?>
    <div class="alert alert-success">
        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        <?php echo $exito; ?>
    </div>
<?php endif; ?>

<?php if (isset($error) && $error): ?>
    <div class="alert alert-error">
        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<style>
    .alert {
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 25px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slideDown 0.3s ease-out;
    }
    .alert-success { background: #D1FAE5; color: #065F46; border: 1px solid #6EE7B7; }
    .alert-error { background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5; }
    
    @keyframes slideDown {
        from { transform: translateY(-10px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
</style>
