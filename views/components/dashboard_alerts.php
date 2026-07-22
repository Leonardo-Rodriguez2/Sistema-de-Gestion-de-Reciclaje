<?php
// views/components/dashboard_alerts.php
// Se asume que $exito y $error son las variables que contienen los mensajes.
?>

<?php if (isset($exito) && $exito): ?>
    <div class="alert alert-success" id="alert-success">
        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        <span><?php echo $exito; ?></span>
        <button onclick="this.parentElement.remove()" style="background:none; border:none; cursor:pointer; margin-left:auto; color:#166534; font-size:16px;">×</button>
    </div>
<?php endif; ?>

<?php if (isset($error) && $error): ?>
    <div class="alert alert-error" id="alert-error">
        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
        <span><?php echo $error; ?></span>
        <button onclick="this.parentElement.remove()" style="background:none; border:none; cursor:pointer; margin-left:auto; color:#991B1B; font-size:16px;">×</button>
    </div>
<?php endif; ?>

<style>
    .alert {
        padding: 12px 16px;
        border-radius: 3px;
        margin-bottom: 20px;
        font-weight: 500;
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slideDown 0.3s ease-out;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .alert-success { background: #F0FDF4; color: #166534; border: 1px solid #BBF7D0; border-left: 4px solid #10B981; }
    .alert-error { background: #FEF2F2; color: #991B1B; border: 1px solid #FECACA; border-left: 4px solid #EF4444; }
    
    @keyframes slideDown {
        from { transform: translateY(-10px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    
    /* Auto-dismiss after 5 seconds */
    .alert-success { animation: slideDown 0.3s ease-out, fadeOut 0.5s ease-in 4.5s forwards; }
    .alert-error { animation: slideDown 0.3s ease-out, fadeOut 0.5s ease-in 4.5s forwards; }
    
    @keyframes fadeOut {
        from { opacity: 1; }
        to { opacity: 0; pointer-events: none; }
    }
</style>
