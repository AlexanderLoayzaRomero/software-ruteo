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
                    <h2>Acceso a Software O&M</h2>
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
        </div>

    </div>
</div>
