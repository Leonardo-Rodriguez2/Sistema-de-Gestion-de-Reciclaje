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
        padding: 10px 15px;
        border-radius: 6px;
        margin-bottom: 20px;
        font-weight: 500;
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 8px;
        animation: slideDown 0.3s ease-out;
    }
    .alert-success { background: #F0FDF4; color: #166534; border: 1px solid #BBF7D0; }
    .alert-error { background: #FEF2F2; color: #991B1B; border: 1px solid #FECACA; }
    
    @keyframes slideDown {
        from { transform: translateY(-10px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
</style>
