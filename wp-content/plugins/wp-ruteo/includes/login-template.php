<?php
/**
 * Template de Iniciar Sesion - Aplicativo Ruteo
 * Shortcode: [login_ruteo]
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$ajax_url = esc_js( admin_url( 'admin-ajax.php' ) );
$nonce    = esc_js( wp_create_nonce( 'ruteo_submit_nonce' ) );
?>

<div class="ruteo-wrapper">
    <div class="ruteo-glass-container animate-fade-in">
        
        <div class="ruteo-app-header">
            <div class="ruteo-brand">
                <div class="ruteo-logo-icon">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                </div>
                <div class="ruteo-title-area">
                    <h2>Acceso al Aplicativo de Ruteo</h2>
                    <p>Ingresa tus credenciales para administrar o registrar datos</p>
                </div>
            </div>
        </div>

        <div class="login-card-container">
            <div class="login-card-title">
                <h3>Acceso al Aplicativo</h3>
                <p>Ingresa tus credenciales para acceder como Admin o Worker</p>
            </div>
            <form class="ruteo-auth-login-form" data-redirect="true">
                <div class="form-group" style="margin-bottom:16px;">
                    <label>Nombre de Usuario o Correo Electronico</label>
                    <div class="input-wrapper">
                        <input type="text" name="username" placeholder="Usuario o correo@dominio.com" required>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom:24px;">
                    <label>Clave de Acceso</label>
                    <div class="input-wrapper">
                        <input type="password" name="password" placeholder="--------" required>
                    </div>
                </div>
                <button type="submit" class="ruteo-submit-btn" style="width:100%;">
                    <span class="btn-text">Ingresar al Sistema</span>
                    <div class="spinner"></div>
                </button>
                <div class="ruteo-message login-message"></div>
            </form>
            <div class="demo-accounts-box" style="margin-top:20px; padding:12px; background:rgba(255,255,255,0.03); border:1px solid var(--border); border-radius:10px;">
                <p style="font-size:12px; font-weight:600; margin:0 0 8px 0; color:var(--text-secondary);">Cuentas Rapidas de Prueba (Click para ingresar):</p>
                <div style="display:flex; gap:6px; flex-wrap:wrap;">
                    <button type="button" class="btn-demo-login portal-btn portal-btn--refresh" data-user="admingeneral" data-pass="AdminGeneral123!" style="font-size:11px; padding:4px 8px;">Admin General</button>
                    <button type="button" class="btn-demo-login portal-btn portal-btn--refresh" data-user="tecnico1" data-pass="Tecnico123!" style="font-size:11px; padding:4px 8px;">Tecnico</button>
                    <button type="button" class="btn-demo-login portal-btn portal-btn--refresh" data-user="supervisor1" data-pass="Supervisor123!" style="font-size:11px; padding:4px 8px;">Supervisor Op.</button>
                    <button type="button" class="btn-demo-login portal-btn portal-btn--refresh" data-user="seguridad1" data-pass="Seguridad123!" style="font-size:11px; padding:4px 8px;">Supervisor Seg.</button>
                    <button type="button" class="btn-demo-login portal-btn portal-btn--refresh" data-user="hse1" data-pass="Hse123!" style="font-size:11px; padding:4px 8px;">Area HSE</button>
                </div>
            </div>
        </div>

    </div>
</div>
