<?php
/**
 * Portal de Ruteo - Vista principal con Sidebar Lateral (Estilo HSE App)
 * Shortcode: [portal_ruteo]
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wp_ruteo_webhook_url;
$ajax_url = esc_js( admin_url( 'admin-ajax.php' ) );
$nonce    = esc_js( wp_create_nonce( 'ruteo_submit_nonce' ) );

$pm_list = array(
    'PM 12 - Arequipa',
    'PM 13 - Cusco',
    'PM 22 - Espinar',
    'PM 23 - Puno',
    'PM 24 - Tacna',
    'PM 25 - Puerto Maldonado',
    'PM 26 - Moquegua',
    'PM 30 - Trujillo',
    'PM 31 - Piura',
    'PM 32 - Cajamarca',
    'PM 33 - Jaen',
    'PM 43 - Tarapoto',
    'PM 46 - Huaraz',
    'PM 47 - Sihuas',
    'PM 38 - Lima',
    'PM 39 - Junin',
    'PM 40 - Pasco',
    'PM 41 - Ayacucho',
    'PM 42 - Huancavelica',
    'PM 44 - Ica',
    'PM 45 - Abancay',
    'PM 48 - Huanuco',
    'PM 49 - Ucayali',
    'PM 50 - Tingo Maria'
);
?>
<style>
#wpadminbar {
    display: none !important;
}
html, body, #page, .wp-site-blocks, .entry-content, .wp-block-post-content, main, .site-main, .is-layout-constrained, .is-layout-flow {
    max-width: 100% !important;
    width: 100% !important;
    padding: 0 !important;
    margin: 0 !important;
    top: 0 !important;
}
header:not(.ruteo-top-header), header.wp-block-template-part, .wp-block-header, .site-header, .wp-block-navigation, .wp-block-post-title, .entry-title, footer, .wp-block-footer {
    display: none !important;
}
.ruteo-top-header {
    display: flex !important;
}
body.admin-bar .ruteo-app-layout {
    margin-top: 0 !important;
    min-height: 100vh !important;
}
</style>

<div class="ruteo-app-layout">
    
    <!-- BARRA LATERAL (SIDEBAR) -->
    <aside class="ruteo-sidebar" id="ruteo-sidebar">
        <div class="sidebar-header">
            <div class="sidebar-brand">
               <div class="brand-logo-icon" id="sidebar-brand-logo">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div class="brand-text">
                    <span class="brand-title">Software O&M</span>
                    <span class="brand-subtitle">Gestion Operaciones y Mantenimiento</span>
                </div>
            </div>
            <button class="sidebar-collapse-btn" id="btn-sidebar-collapse" title="Contraer panel">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                </svg>
                <span class="btn-collapse-label">Contraer</span>
            </button>
        </div>

        <nav class="sidebar-menu">
            <button class="sidebar-item active" data-tab="inicio">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>Inicio</span>
            </button>

            <button class="sidebar-item" data-tab="registros">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                <span>Registros</span>
            </button>

            <button class="sidebar-item" data-tab="formulario">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <span>Nuevo Registro</span>
            </button>

            <button class="sidebar-item" data-tab="materiales">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <span>Consumo Materiales</span>
            </button>

            <button class="sidebar-item" data-tab="sla-informes">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>SLA e Informes</span>
            </button>

            <button class="sidebar-item" data-tab="negativa">
    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
    </svg>
    <span>Negativa al Trabajo</span>
            </button>

            <button class="sidebar-item" data-tab="usuarios" id="tab-btn-usuarios" style="display:none;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <span>Usuarios</span>
            </button>

            <button class="sidebar-item" data-tab="perfil" id="tab-btn-perfil" style="display:none;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span>Perfil</span>
            </button>

            <button class="sidebar-item" data-tab="login" id="tab-btn-login">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
                <span>Iniciar Sesion</span>
            </button>
        </nav>

        <div class="sidebar-footer">
            <span class="sidebar-copy">&copy; 2026 HSE Ruteo App</span>
            <span class="sidebar-url">app.ruteo.org.pe</span>
        </div>
    </aside>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="ruteo-main-content">

        <!-- HEADER SUPERIOR -->
        <header class="ruteo-top-header">
            <div class="header-left">
                <button class="mobile-toggle-btn" id="btn-mobile-sidebar-toggle" type="button" title="Menu Movil">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <div class="header-title-box">
                    <h1 class="header-title" id="page-header-title">Panel de Administracion</h1>
                    <div class="header-subtitle-row">
                        <span class="header-date" id="current-date-str">--</span>
                        <span class="bullet-sep">•</span>
                        <span class="header-subtext">Gestion Ruteo y Mantenimiento</span>
                    </div>
                </div>
            </div>

            <div class="header-right">
                <div class="system-status-pill">
                    <span class="status-dot-active"></span>
                    <span>Sistema Activo</span>
                </div>

                <button class="theme-toggle-btn" id="btn-theme-toggle" type="button" title="Cambiar Tema Dia/Noche">
                    <span id="theme-toggle-icon">&#9790;</span>
                    <span id="theme-toggle-text">Modo Noche</span>
                </button>

                <div class="user-profile-badge" id="ruteo-user-badge">
                    <div class="user-avatar" id="user-avatar-box">
                        <span id="user-avatar-text">?</span>
                    </div>
                    <div class="user-details">
                        <span class="user-name" id="user-display-name">Invitado</span>
                        <span class="user-role-badge" id="user-role-label">Acceso Bloqueado</span>
                    </div>
                    <button class="btn-logout-icon" id="btn-ruteo-logout" style="display:none;" title="Cerrar Sesion">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </div>
            </div>
        </header>

        <!-- SECCIONES / PESTANAS -->

        <!-- SECCION 1: INICIO (DASHBOARD) -->
        <section class="ruteo-tab-content active" id="tab-inicio">
            <div class="ruteo-tab-protected-notice" style="display:none;">
                <div class="login-card-container" style="max-width:520px; margin: 30px auto; text-align:center; padding:32px 24px; background:var(--bg-glass); border:1px solid var(--border); border-radius:16px; backdrop-filter:blur(10px);">
                    <div style="width:64px; height:64px; margin:0 auto 16px auto; background:rgba(0, 151, 216, 0.12); border-radius:50%; display:flex; align-items:center; justify-content:center;">
                        <svg width="32" height="32" fill="none" stroke="#0097D8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h3 style="font-size:20px; font-weight:700; margin:0 0 8px 0; color:var(--menu-title);">Acceso Restringido - Inicia Sesion</h3>
                    <p style="font-size:14px; color:var(--text-muted); margin:0 0 24px 0;">Debes iniciar sesion con tu cuenta para acceder al Panel de Inicio y metricas del sistema.</p>

                    <form class="ruteo-auth-login-form" style="text-align:left; margin-bottom:20px;">
                        <div class="form-group" style="margin-bottom:14px;">
                            <label style="font-size:13px; font-weight:600;">Nombre de Usuario o Correo Electronico</label>
                            <div class="input-wrapper">
                                <input type="text" name="username" placeholder="Usuario o correo@dominio.com" required>
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom:18px;">
                            <label style="font-size:13px; font-weight:600;">Clave de Acceso</label>
                            <div class="input-wrapper">
                                <input type="password" name="password" placeholder="--------" required>
                            </div>
                        </div>
                        <button type="submit" class="ruteo-submit-btn" style="width:100%;">
                            <span class="btn-text">Iniciar Sesion e Ingresar</span>
                            <div class="spinner"></div>
                        </button>
                        <div class="ruteo-message"></div>
                    </form>

                    <div class="demo-accounts-box" style="padding:12px; background:var(--bg-light); border:1px solid var(--border); border-radius:10px;">
                        <p style="font-size:12px; font-weight:600; margin:0 0 8px 0; color:var(--text-secondary);">Cuentas Rapidas de Prueba (Click para ingresar):</p>
                        <div style="display:flex; gap:6px; flex-wrap:wrap; justify-content:center;">
                            <button type="button" class="btn-demo-login portal-btn portal-btn--refresh" data-user="admingeneral" data-pass="AdminGeneral123!" style="font-size:11px; padding:4px 8px;">Admin General</button>
                            <button type="button" class="btn-demo-login portal-btn portal-btn--refresh" data-user="tecnico1" data-pass="Tecnico123!" style="font-size:11px; padding:4px 8px;">Tecnico</button>
                            <button type="button" class="btn-demo-login portal-btn portal-btn--refresh" data-user="supervisor1" data-pass="Supervisor123!" style="font-size:11px; padding:4px 8px;">Supervisor Op.</button>
                            <button type="button" class="btn-demo-login portal-btn portal-btn--refresh" data-user="seguridad1" data-pass="Seguridad123!" style="font-size:11px; padding:4px 8px;">Supervisor Seg.</button>
                            <button type="button" class="btn-demo-login portal-btn portal-btn--refresh" data-user="hse1" data-pass="Hse123!" style="font-size:11px; padding:4px 8px;">Area HSE</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ruteo-tab-protected-content">
                <div class="dashboard-stats-grid">
                    <div class="dash-stat-card">
                        <div class="stat-icon icon-blue">
                            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div class="stat-info">
                            <span class="stat-title">Registros Activos</span>
                            <span class="stat-number" id="dash-stat-total">-</span>
                        </div>
                    </div>

                    <div class="dash-stat-card">
                        <div class="stat-icon icon-green">
                            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <div class="stat-info">
                            <span class="stat-title">Reportes Materiales</span>
                            <span class="stat-number" id="dash-stat-materiales">-</span>
                        </div>
                    </div>

                    <div class="dash-stat-card">
                        <div class="stat-icon icon-purple">
                            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                            </svg>
                        </div>
                        <div class="stat-info">
                            <span class="stat-title">Tramos Activos</span>
                            <span class="stat-number" id="dash-stat-tramos">-</span>
                        </div>
                    </div>

                    <div class="dash-stat-card">
                        <div class="stat-icon icon-red">
                            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <div class="stat-info">
                            <span class="stat-title">Cuentas Registradas</span>
                            <span class="stat-number" id="dash-stat-users">-</span>
                        </div>
                    </div>
                </div>

                <div class="dashboard-panels-grid">
                    <div class="dash-panel quick-actions-panel">
                        <h3 class="panel-title">Acciones Rapidas</h3>
                        <div class="quick-actions-list">
                            <button class="action-btn-card" data-goto="formulario">
                                <div class="btn-card-icon icon-blue-bg">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </div>
                                <div class="btn-card-text">
                                    <span class="card-text-title">Nuevo Registro de Campo</span>
                                    <span class="card-text-sub">Capturar estructura, herrajes y fotos</span>
                                </div>
                            </button>

                            <button class="action-btn-card" data-goto="materiales">
                                <div class="btn-card-icon icon-green-bg">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                                <div class="btn-card-text">
                                    <span class="card-text-title">Consumo de Materiales</span>
                                    <span class="card-text-sub">Reportar materiales usados por PM/Incidencia</span>
                                </div>
                            </button>

                            <button class="action-btn-card" data-goto="sla-informes">
                                <div class="btn-card-icon icon-purple-bg">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div class="btn-card-text">
                                    <span class="card-text-title">SLA e Informes</span>
                                    <span class="card-text-sub">Generar formatos de mantenimiento</span>
                                </div>
                            </button>
                        </div>

                        <!-- ACCESOS DIRECTOS A GOOGLE SHEETS PARA ADMIN -->
                        <div class="admin-sheets-panel" id="admin-sheets-box" style="margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--border); display: none;">
                            <h4 style="font-size: 14px; font-weight: 700; margin: 0 0 12px 0; color: var(--menu-title);">Accesos Directos a Google Sheets (Solo Admin)</h4>
                            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                                <a href="https://docs.google.com/spreadsheets/d/1m19aeKOuPJYw01yvFPP9_SGmpdJgUg_q/edit" target="_blank" class="portal-btn portal-btn--excel" id="btn-link-gsheet-ruteo" style="font-size: 12px; padding: 8px 14px; text-decoration: none;">
                                    📊 Abrir Google Sheet Ruteo
                                </a>
                                <a href="https://docs.google.com/spreadsheets" target="_blank" class="portal-btn portal-btn--download" id="btn-link-gsheet-materiales" style="font-size: 12px; padding: 8px 14px; text-decoration: none;">
                                    📦 Abrir Google Sheet Materiales
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="dash-panel system-status-panel">
                        <h3 class="panel-title">Estado del Sistema</h3>
                        <div class="status-items-list">
                            <div class="status-item-row">
                                <span>Sincronizacion Google Sheets</span>
                                <span class="status-badge-active">En linea</span>
                            </div>
                            <div class="status-item-row">
                                <span>Almacenamiento de Fotos Drive</span>
                                <span class="status-badge-active">Disponible</span>
                            </div>
                            <div class="status-item-row">
                                <span>Generacion de PDF / KMZ</span>
                                <span class="status-badge-active">Operativo</span>
                            </div>
                            <div class="status-item-row">
                                <span>Centros de Mantenimiento (PMs)</span>
                                <span class="status-badge-info">24 Sedes Activas</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECCION 2: REGISTROS DE CAMPO -->
        <section class="ruteo-tab-content" id="tab-registros">
            <div class="ruteo-tab-protected-notice" style="display:none;">
                <div class="login-card-container" style="max-width:520px; margin: 30px auto; text-align:center; padding:32px 24px; background:var(--bg-glass); border:1px solid var(--border); border-radius:16px; backdrop-filter:blur(10px);">
                    <div style="width:64px; height:64px; margin:0 auto 16px auto; background:rgba(0, 151, 216, 0.12); border-radius:50%; display:flex; align-items:center; justify-content:center;">
                        <svg width="32" height="32" fill="none" stroke="#0097D8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h3 style="font-size:20px; font-weight:700; margin:0 0 8px 0; color:var(--menu-title);">Acceso Restringido - Inicia Sesion</h3>
                    <p style="font-size:14px; color:var(--text-muted); margin:0 0 24px 0;">Debes iniciar sesion con tu cuenta para consultar los Registros de Campo y descargar reportes.</p>

                    <form class="ruteo-auth-login-form" style="text-align:left; margin-bottom:20px;">
                        <div class="form-group" style="margin-bottom:14px;">
                            <label style="font-size:13px; font-weight:600;">Nombre de Usuario o Correo Electronico</label>
                            <div class="input-wrapper">
                                <input type="text" name="username" placeholder="Usuario o correo@dominio.com" required>
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom:18px;">
                            <label style="font-size:13px; font-weight:600;">Clave de Acceso</label>
                            <div class="input-wrapper">
                                <input type="password" name="password" placeholder="--------" required>
                            </div>
                        </div>
                        <button type="submit" class="ruteo-submit-btn" style="width:100%;">
                            <span class="btn-text">Iniciar Sesion e Ingresar</span>
                            <div class="spinner"></div>
                        </button>
                        <div class="ruteo-message"></div>
                    </form>

                    <div class="demo-accounts-box" style="padding:12px; background:var(--bg-light); border:1px solid var(--border); border-radius:10px;">
                        <p style="font-size:12px; font-weight:600; margin:0 0 8px 0; color:var(--text-secondary);">Cuentas Rapidas de Prueba (Click para ingresar):</p>
                        <div style="display:flex; gap:6px; flex-wrap:wrap; justify-content:center;">
                            <button type="button" class="btn-demo-login portal-btn portal-btn--refresh" data-user="admingeneral" data-pass="AdminGeneral123!" style="font-size:11px; padding:4px 8px;">Admin General</button>
                            <button type="button" class="btn-demo-login portal-btn portal-btn--refresh" data-user="tecnico1" data-pass="Tecnico123!" style="font-size:11px; padding:4px 8px;">Tecnico</button>
                            <button type="button" class="btn-demo-login portal-btn portal-btn--refresh" data-user="supervisor1" data-pass="Supervisor123!" style="font-size:11px; padding:4px 8px;">Supervisor Op.</button>
                            <button type="button" class="btn-demo-login portal-btn portal-btn--refresh" data-user="seguridad1" data-pass="Seguridad123!" style="font-size:11px; padding:4px 8px;">Supervisor Seg.</button>
                            <button type="button" class="btn-demo-login portal-btn portal-btn--refresh" data-user="hse1" data-pass="Hse123!" style="font-size:11px; padding:4px 8px;">Area HSE</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ruteo-tab-protected-content">
                <div class="registros-container">
                    <div class="portal-toolbar-row">
                        <div class="portal-actions">
                            <button class="portal-btn portal-btn--refresh" id="btn-refresh-portal">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                <span>Actualizar</span>
                            </button>

                            <button class="portal-btn portal-btn--download" id="btn-download-pdf">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                                </svg>
                                <span class="btn-dl-text">Descargar PDF</span>
                            </button>

                            <button class="portal-btn portal-btn--excel" id="btn-download-excel">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span class="btn-xl-text">Descargar Excel</span>
                            </button>
                        </div>

                        <div class="portal-filters-row">
                            <div class="filter-group">
                                <label for="filter-tramo">Filtrar Tramo</label>
                                <select id="filter-tramo">
                                    <option value="">Todos los tramos</option>
                                </select>
                            </div>

                            <div class="portal-search-wrap">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <input type="text" id="portal-search" placeholder="Buscar por tramo, codigo, ID...">
                            </div>
                        </div>
                    </div>

                    <div class="portal-table-wrapper" id="portal-table-wrapper">
                        <div class="portal-loading" id="portal-loading">
                            <div class="portal-spinner"></div>
                            <span>Cargando registros de campo...</span>
                        </div>

                        <div class="portal-error" id="portal-error" style="display:none; flex-direction:column; align-items:center; gap:10px; padding:30px 20px; text-align:center;">
                            <svg width="36" height="36" fill="none" stroke="#D92625" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <p id="portal-error-msg" style="margin:0; font-weight:600; color:var(--text-main);">No se pudo conectar con Google Sheets.</p>
                            <button type="button" class="portal-btn portal-btn--refresh" id="btn-retry-portal-fetch" style="font-size:12px; padding:6px 14px; margin-top:4px;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                <span>Reintentar Conexion</span>
                            </button>
                        </div>

                        <div id="portal-data-section" style="display:none;">
                            <div class="portal-table-header">
                                <span class="portal-note" id="portal-last-update"></span>
                            </div>
                            <div class="portal-table-scroll">
                                <table class="portal-table" id="portal-table">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Tramo</th>
                                            <th>ID Consol</th>
                                            <th>Estructura</th>
                                            <th>Tipo</th>
                                            <th>Altura</th>
                                            <th>Codigo</th>
                                            <th>Ubicacion</th>
                                            <th>Mufa</th>
                                            <th>Retencion</th>
                                            <th>Suspension</th>
                                            <th>Cruceta</th>
                                            <th>Foto 1</th>
                                            <th>Foto 2</th>
                                            <th>KMZ</th>
                                        </tr>
                                    </thead>
                                    <tbody id="portal-tbody"></tbody>
                                </table>
                            </div>
                            <div class="portal-empty" id="portal-empty" style="display:none;">
                                <p>No hay registros aun.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECCION 3: NUEVO REGISTRO (FORMULARIO) -->
        <section class="ruteo-tab-content" id="tab-formulario">
            <?php include plugin_dir_path( __FILE__ ) . 'form-template.php'; ?>
        </section>

        <!-- SECCION 4: CONSUMO DE MATERIALES -->
        <section class="ruteo-tab-content" id="tab-materiales">
            <div class="ruteo-tab-protected-notice" style="display:none;">
                <div class="login-card-container" style="max-width:520px; margin: 30px auto; text-align:center; padding:32px 24px; background:var(--bg-glass); border:1px solid var(--border); border-radius:16px; backdrop-filter:blur(10px);">
                    <div style="width:64px; height:64px; margin:0 auto 16px auto; background:rgba(0, 151, 216, 0.12); border-radius:50%; display:flex; align-items:center; justify-content:center;">
                        <svg width="32" height="32" fill="none" stroke="#0097D8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h3 style="font-size:20px; font-weight:700; margin:0 0 8px 0; color:var(--menu-title);">Acceso Restringido - Inicia Sesion</h3>
                    <p style="font-size:14px; color:var(--text-muted); margin:0 0 24px 0;">Debes iniciar sesion con tu cuenta para registrar el Consumo de Materiales.</p>

                    <form class="ruteo-auth-login-form" style="text-align:left; margin-bottom:20px;">
                        <div class="form-group" style="margin-bottom:14px;">
                            <label style="font-size:13px; font-weight:600;">Nombre de Usuario o Correo Electronico</label>
                            <div class="input-wrapper">
                                <input type="text" name="username" placeholder="Usuario o correo@dominio.com" required>
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom:18px;">
                            <label style="font-size:13px; font-weight:600;">Clave de Acceso</label>
                            <div class="input-wrapper">
                                <input type="password" name="password" placeholder="--------" required>
                            </div>
                        </div>
                        <button type="submit" class="ruteo-submit-btn" style="width:100%;">
                            <span class="btn-text">Iniciar Sesion e Ingresar</span>
                            <div class="spinner"></div>
                        </button>
                        <div class="ruteo-message"></div>
                    </form>

                    <div class="demo-accounts-box" style="padding:12px; background:var(--bg-light); border:1px solid var(--border); border-radius:10px;">
                        <p style="font-size:12px; font-weight:600; margin:0 0 8px 0; color:var(--text-secondary);">Cuentas Rapidas de Prueba (Click para ingresar):</p>
                        <div style="display:flex; gap:6px; flex-wrap:wrap; justify-content:center;">
                            <button type="button" class="btn-demo-login portal-btn portal-btn--refresh" data-user="admingeneral" data-pass="AdminGeneral123!" style="font-size:11px; padding:4px 8px;">Admin General</button>
                            <button type="button" class="btn-demo-login portal-btn portal-btn--refresh" data-user="tecnico1" data-pass="Tecnico123!" style="font-size:11px; padding:4px 8px;">Tecnico</button>
                            <button type="button" class="btn-demo-login portal-btn portal-btn--refresh" data-user="supervisor1" data-pass="Supervisor123!" style="font-size:11px; padding:4px 8px;">Supervisor Op.</button>
                            <button type="button" class="btn-demo-login portal-btn portal-btn--refresh" data-user="seguridad1" data-pass="Seguridad123!" style="font-size:11px; padding:4px 8px;">Supervisor Seg.</button>
                            <button type="button" class="btn-demo-login portal-btn portal-btn--refresh" data-user="hse1" data-pass="Hse123!" style="font-size:11px; padding:4px 8px;">Area HSE</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ruteo-tab-protected-content">
                <div class="materiales-container">
                    <!-- TARJETA FORMULARIO MATERIALES -->
                    <div class="material-card">
                        <div class="card-header">
                            <p style="color:var(--text-muted); margin: 0 0 20px 0;">Registre los materiales utilizados en incidencias o mantenimientos por Almacen / PM</p>
                        </div>

                        <form id="form-consumo-materiales" class="ruteo-materiales-form">
                            <div class="form-grid-3">
                                <div class="form-group">
                                    <label>No. Incidencia / INC</label>
                                    <div class="input-wrapper">
                                        <input type="text" id="mat-incidencia" placeholder="Ej: INC 78093" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>CRQ (Opcional)</label>
                                    <div class="input-wrapper">
                                        <input type="text" id="mat-crq" placeholder="Ej: CRQ-90272">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Almacen / PM (Centro Mantenimiento)</label>
                                    <div class="input-wrapper">
                                        <select id="mat-almacen-pm" required>
                                            <option value="">-- Seleccionar Almacen / PM --</option>
                                            <?php foreach ($pm_list as $pm_item) : ?>
                                                <option value="<?php echo esc_attr($pm_item); ?>"><?php echo esc_html($pm_item); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Tramo</label>
                                    <div class="input-wrapper">
                                        <input type="text" id="mat-tramo" placeholder="Ej: Urubamba - Quillabamba" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Fecha de Intervencion</label>
                                    <div class="input-wrapper">
                                        <input type="date" id="mat-fecha" value="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                </div>

                                <div class="form-group full-width">
                                    <label>Descripcion de Incidencia / Trabajo</label>
                                    <div class="input-wrapper">
                                        <input type="text" id="mat-descripcion" placeholder="Ej: Perdida de enlace Cusco - Quillabamba / Preventivo PEXT" required>
                                    </div>
                                </div>
                            </div>

                            <!-- TABLA DINAMICA DE MATERIALES -->
                            <div class="materials-items-section">
                                <div class="items-section-header">
                                    <h4>Materiales Utilizados</h4>
                                    <button type="button" class="btn-add-item" id="btn-add-material-row">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        <span>Agregar Material</span>
                                    </button>
                                </div>

                                <div class="portal-table-scroll">
                                    <table class="items-table" id="table-material-items">
                                        <thead>
                                            <tr>
                                                <th style="width: 50px;">Item</th>
                                                <th>Descripcion del Material</th>
                                                <th style="width: 120px;">Unidad</th>
                                                <th style="width: 100px;">Cantidad</th>
                                                <th style="width: 160px;">Codigo SAP</th>
                                                <th style="width: 140px;">No. Drum</th>
                                                <th style="width: 60px;">Accion</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody-material-items">
                                            <!-- Filas dinamicas JS -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="form-footer-actions">
                                <button type="submit" class="ruteo-submit-btn" style="min-width: 220px;">
                                    <span class="btn-text">Guardar Reporte Materiales</span>
                                    <div class="spinner"></div>
                                </button>
                                <div id="mat-form-msg" class="ruteo-message"></div>
                            </div>
                        </form>
                    </div>

                    <!-- HISTORIAL DE MATERIALES -->
                    <div class="material-card" style="margin-top: 24px;">
                        <div class="card-header">
                            <h3>Historial de Reportes de Materiales</h3>
                        </div>

                        <div class="portal-filters-row">
                            <div class="filter-group">
                                <label for="filter-mat-pm">Filtrar por PM</label>
                                <select id="filter-mat-pm">
                                    <option value="">Todos los PMs</option>
                                    <?php foreach ($pm_list as $pm_item) : ?>
                                        <option value="<?php echo esc_attr($pm_item); ?>"><?php echo esc_html($pm_item); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="portal-search-wrap">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <input type="text" id="mat-search" placeholder="Buscar por INC, Tramo o Material...">
                            </div>
                        </div>

                        <div class="portal-table-scroll">
                            <table class="portal-table">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>INC / CRQ</th>
                                        <th>Almacen / PM</th>
                                        <th>Tramo</th>
                                        <th>Descripcion</th>
                                        <th>Materiales</th>
                                        <th>Usuario</th>
                                    </tr>
                                </thead>
                                <tbody id="mat-reports-tbody">
                                    <tr><td colspan="7" style="text-align:center; padding: 20px;">Cargando historial de materiales...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- SECCION 5: SLA E INFORMES DE MANTENIMIENTO -->
        <section class="ruteo-tab-content" id="tab-sla-informes">
            <div class="ruteo-tab-protected-notice" style="display:none;">
                <div class="login-card-container" style="max-width:520px; margin: 30px auto; text-align:center; padding:32px 24px; background:var(--bg-glass); border:1px solid var(--border); border-radius:16px; backdrop-filter:blur(10px);">
                    <div style="width:64px; height:64px; margin:0 auto 16px auto; background:rgba(0, 151, 216, 0.12); border-radius:50%; display:flex; align-items:center; justify-content:center;">
                        <svg width="32" height="32" fill="none" stroke="#0097D8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h3 style="font-size:20px; font-weight:700; margin:0 0 8px 0; color:var(--menu-title);">Acceso Restringido - Inicia Sesion</h3>
                    <p style="font-size:14px; color:var(--text-muted); margin:0 0 24px 0;">Debes iniciar sesion con tu cuenta para acceder a SLA e Informes de Mantenimiento.</p>

                    <form class="ruteo-auth-login-form" style="text-align:left; margin-bottom:20px;">
                        <div class="form-group" style="margin-bottom:14px;">
                            <label style="font-size:13px; font-weight:600;">Nombre de Usuario o Correo Electronico</label>
                            <div class="input-wrapper">
                                <input type="text" name="username" placeholder="Usuario o correo@dominio.com" required>
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom:18px;">
                            <label style="font-size:13px; font-weight:600;">Clave de Acceso</label>
                            <div class="input-wrapper">
                                <input type="password" name="password" placeholder="--------" required>
                            </div>
                        </div>
                        <button type="submit" class="ruteo-submit-btn" style="width:100%;">
                            <span class="btn-text">Iniciar Sesion e Ingresar</span>
                            <div class="spinner"></div>
                        </button>
                        <div class="ruteo-message"></div>
                    </form>

                    <div class="demo-accounts-box" style="padding:12px; background:var(--bg-light); border:1px solid var(--border); border-radius:10px;">
                        <p style="font-size:12px; font-weight:600; margin:0 0 8px 0; color:var(--text-secondary);">Cuentas Rapidas de Prueba (Click para ingresar):</p>
                        <div style="display:flex; gap:6px; flex-wrap:wrap; justify-content:center;">
                            <button type="button" class="btn-demo-login portal-btn portal-btn--refresh" data-user="admingeneral" data-pass="AdminGeneral123!" style="font-size:11px; padding:4px 8px;">Admin General</button>
                            <button type="button" class="btn-demo-login portal-btn portal-btn--refresh" data-user="tecnico1" data-pass="Tecnico123!" style="font-size:11px; padding:4px 8px;">Tecnico</button>
                            <button type="button" class="btn-demo-login portal-btn portal-btn--refresh" data-user="supervisor1" data-pass="Supervisor123!" style="font-size:11px; padding:4px 8px;">Supervisor Op.</button>
                            <button type="button" class="btn-demo-login portal-btn portal-btn--refresh" data-user="seguridad1" data-pass="Seguridad123!" style="font-size:11px; padding:4px 8px;">Supervisor Seg.</button>
                            <button type="button" class="btn-demo-login portal-btn portal-btn--refresh" data-user="hse1" data-pass="Hse123!" style="font-size:11px; padding:4px 8px;">Area HSE</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ruteo-tab-protected-content">
                <div class="sla-container">
                    <div class="sla-header">
                        <p style="color:var(--text-muted); margin: 0 0 24px 0;">Acceda a los formatos estandarizados de soporte y reportes tecnicos</p>
                    </div>

                    <div class="sla-cards-grid">
                        <div class="sla-card">
                            <div class="sla-icon icon-blue-bg">
                                <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <h4>Formato SLA Cumplimiento</h4>
                            <p>Generacion de reporte de cumplimiento de tiempos de respuesta y solucion segun acuerdo de nivel de servicio.</p>
                            <button class="portal-btn portal-btn--refresh btn-sla-action" data-type="Formato SLA">Abrir Formato SLA</button>
                        </div>

                        <div class="sla-card">
                            <div class="sla-icon icon-green-bg">
                                <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <h4>Informe Tecnico Preventivo</h4>
                            <p>Formato normalizado para mantenimientos preventivos PEXT en tramos de fibra optica.</p>
                            <button class="portal-btn portal-btn--refresh btn-sla-action" data-type="Informe Preventivo">Generar Informe</button>
                        </div>

                        <div class="sla-card">
                            <div class="sla-icon icon-purple-bg">
                                <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <h4>Informe Tecnico Correctivo</h4>
                            <p>Reporte detallado de atencion de incidencias, reparacion de hilos, empalmes y consumo de fibra.</p>
                            <button class="portal-btn portal-btn--refresh btn-sla-action" data-type="Informe Correctivo">Generar Informe</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECCION: NEGATIVA AL TRABAJO POR RIESGO INMINENTE -->
        <section class="ruteo-tab-content" id="tab-negativa">
            <div class="ruteo-tab-protected-notice" style="display:none;">
                <div class="login-card-container" style="max-width:520px; margin: 30px auto; text-align:center; padding:32px 24px; background:var(--bg-glass); border:1px solid var(--border); border-radius:16px; backdrop-filter:blur(10px);">
                    <div style="width:64px; height:64px; margin:0 auto 16px auto; background:rgba(0, 151, 216, 0.12); border-radius:50%; display:flex; align-items:center; justify-content:center;">
                        <svg width="32" height="32" fill="none" stroke="#0097D8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h3 style="font-size:20px; font-weight:700; margin:0 0 8px 0; color:var(--menu-title);">Acceso Restringido - Inicia Sesion</h3>
                    <p style="font-size:14px; color:var(--text-muted); margin:0 0 24px 0;">Debes iniciar sesion con tu cuenta para acceder al modulo de Negativa al Trabajo.</p>

                    <form class="ruteo-auth-login-form" style="text-align:left; margin-bottom:20px;">
                        <div class="form-group" style="margin-bottom:14px;">
                            <label style="font-size:13px; font-weight:600;">Nombre de Usuario o Correo Electronico</label>
                            <div class="input-wrapper">
                                <input type="text" name="username" placeholder="Usuario o correo@dominio.com" required>
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom:18px;">
                            <label style="font-size:13px; font-weight:600;">Clave de Acceso</label>
                            <div class="input-wrapper">
                                <input type="password" name="password" placeholder="--------" required>
                            </div>
                        </div>
                        <button type="submit" class="ruteo-submit-btn" style="width:100%;">
                            <span class="btn-text">Iniciar Sesion e Ingresar</span>
                            <div class="spinner"></div>
                        </button>
                        <div class="ruteo-message"></div>
                    </form>

                    <div class="demo-accounts-box" style="padding:12px; background:var(--bg-light); border:1px solid var(--border); border-radius:10px;">
                        <p style="font-size:12px; font-weight:600; margin:0 0 8px 0; color:var(--text-secondary);">Cuentas Rapidas de Prueba (Click para ingresar):</p>
                        <div style="display:flex; gap:6px; flex-wrap:wrap; justify-content:center;">
                            <button type="button" class="btn-demo-login portal-btn portal-btn--refresh" data-user="admingeneral" data-pass="AdminGeneral123!" style="font-size:11px; padding:4px 8px;">Admin General</button>
                            <button type="button" class="btn-demo-login portal-btn portal-btn--refresh" data-user="tecnico1" data-pass="Tecnico123!" style="font-size:11px; padding:4px 8px;">Tecnico</button>
                            <button type="button" class="btn-demo-login portal-btn portal-btn--refresh" data-user="supervisor1" data-pass="Supervisor123!" style="font-size:11px; padding:4px 8px;">Supervisor Op.</button>
                            <button type="button" class="btn-demo-login portal-btn portal-btn--refresh" data-user="seguridad1" data-pass="Seguridad123!" style="font-size:11px; padding:4px 8px;">Supervisor Seg.</button>
                            <button type="button" class="btn-demo-login portal-btn portal-btn--refresh" data-user="hse1" data-pass="Hse123!" style="font-size:11px; padding:4px 8px;">Area HSE</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ruteo-tab-protected-content">
                <div class="ruteo-form-card animate-fade-in">
                    <p class="form-header-sub">Complete el formato de Negativa al Trabajo por Riesgo Inminente (HSE-RE-NEG-01) segun la etapa que le corresponda.</p>

                    <div class="ruteo-form-section" style="margin-bottom:16px; display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                        <label style="font-weight:600;">Registro:</label>
                        <select id="negativa-select-registro" style="padding:8px 12px; border-radius:8px; border:1px solid var(--border); background:var(--bg-subtle); color:var(--text-main); min-width:260px;">
                            <option value="0">+ Nueva Negativa</option>
                        </select>
                        <span id="negativa-estado-badge" class="badge-pill"></span>
                    </div>

                    <!-- Resumen de lo ya llenado en etapas anteriores (solo lectura) -->
                    <div id="negativa-resumen" style="display:none; margin-bottom:20px; padding:16px; border-radius:10px; background:var(--bg-subtle); border:1px solid var(--border);"></div>

                    <!-- ETAPA 1: TECNICO (puntos 1 a 4) -->
                    <form id="form-negativa-tecnico" class="ruteo-form">
                        <div class="ruteo-form-section">
                            <h3 class="form-section-title"><span class="step-badge">1</span> Datos Generales y Empresa Cliente</h3>
                            <div class="ruteo-fields-grid">
                                <div class="form-group"><label>Cliente / Empresa Principal</label><div class="input-wrapper">
                                    <select name="cliente_nombre" class="neg-select-cliente" required>
                                        <option value="CYMTEL">CYMTEL</option>
                                    </select>
                                </div></div>
                                <div class="form-group"><label>Proceso</label><div class="input-wrapper"><input type="text" name="proceso" placeholder="Ej: Perdida de enlace..." required></div></div>
                                <div class="form-group"><label>CM / Localidad</label><div class="input-wrapper"><input type="text" name="cm_localidad" required></div></div>
                                <div class="form-group"><label>Contratista</label><div class="input-wrapper"><input type="text" name="contratista" required></div></div>
                                <div class="form-group"><label>Sub Contratista</label><div class="input-wrapper"><input type="text" name="sub_contratista"></div></div>
                                <div class="form-group"><label>Relacionado a</label><div class="input-wrapper">
                                    <select name="relacionado_a" required><option value="PEXT">PEXT</option><option value="PINT">PINT</option></select>
                                </div></div>
                                <div class="form-group"><label>Lugar de Trabajo</label><div class="input-wrapper"><input type="text" name="lugar_trabajo" required></div></div>
                                <div class="form-group"><label>Fecha</label><div class="input-wrapper"><input type="date" name="fecha" required></div></div>
                                <div class="form-group"><label>Hora Inicio</label><div class="input-wrapper"><input type="time" name="hora_inicio" required></div></div>
                                <div class="form-group"><label>Hora Fin</label><div class="input-wrapper"><input type="time" name="hora_fin"></div></div>
                                <div class="form-group"><label>Total de Horas</label><div class="input-wrapper"><input type="text" name="total_horas" placeholder="Ej: 08 horas, 43 minutos"></div></div>
                            </div>
                        </div>

                        <div class="ruteo-form-section">
                            <h3 class="form-section-title"><span class="step-badge">2</span> Investigacion del Supervisor Operativo</h3>
                            <div class="ruteo-fields-grid">
                                <div class="form-group"><label>Nombre del Supervisor Operativo</label><div class="input-wrapper"><input type="text" name="supervisor_operativo_nombre" required></div></div>
                                <div class="form-group"><label>Trabajador Reportante</label><div class="input-wrapper"><input type="text" name="trabajador_reportante" required></div></div>
                            </div>
                        </div>

                        <div class="ruteo-form-section">
                            <h3 class="form-section-title"><span class="step-badge">3</span> Razones para la Negativa</h3>
                            <div class="form-group"><textarea name="razones_negativa" rows="5" placeholder="Describa las condiciones adversas y la base legal..." required style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border); background:var(--bg-subtle); color:var(--text-main);"></textarea></div>
                        </div>

                        <div class="ruteo-form-section">
                            <h3 class="form-section-title"><span class="step-badge">4</span> Evidencias Fotograficas</h3>
                            <div class="ruteo-photos-grid">
                                <div class="ruteo-photo-upload" id="neg-upload-box-1">
                                    <label for="neg-foto1" class="upload-label">
                                        <svg class="upload-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span class="upload-text">Evidencia 1</span>
                                    </label>
                                    <input type="file" id="neg-foto1" name="foto1" accept="image/*">
                                    <div class="preview" id="neg-preview1"><button type="button" class="btn-remove-photo" data-input="neg-foto1" data-preview="neg-preview1">&times;</button></div>
                                </div>
                                <div class="ruteo-photo-upload" id="neg-upload-box-2">
                                    <label for="neg-foto2" class="upload-label">
                                        <svg class="upload-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <span class="upload-text">Evidencia 2</span>
                                    </label>
                                    <input type="file" id="neg-foto2" name="foto2" accept="image/*">
                                    <div class="preview" id="neg-preview2"><button type="button" class="btn-remove-photo" data-input="neg-foto2" data-preview="neg-preview2">&times;</button></div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn-primary ruteo-submit-btn">Guardar y Firmar como Tecnico</button>
                    </form>

                    <!-- ETAPA 2: SUPERVISOR OPERATIVO (Edicion completa Puntos 1, 2, 3 + Puntos 5 y 6) -->
                    <form id="form-negativa-supervisor" class="ruteo-form" style="display:none;">
                        <div class="ruteo-form-section">
                            <h3 class="form-section-title"><span class="step-badge">1</span> Revision y Edicion de Datos Generales</h3>
                            <div class="ruteo-fields-grid">
                                <div class="form-group"><label>Cliente / Empresa Principal</label><div class="input-wrapper">
                                    <select name="cliente_nombre" class="neg-select-cliente" required>
                                        <option value="CYMTEL">CYMTEL</option>
                                    </select>
                                </div></div>
                                <div class="form-group"><label>Proceso</label><div class="input-wrapper"><input type="text" name="proceso" required></div></div>
                                <div class="form-group"><label>CM / Localidad</label><div class="input-wrapper"><input type="text" name="cm_localidad" required></div></div>
                                <div class="form-group"><label>Contratista</label><div class="input-wrapper"><input type="text" name="contratista" required></div></div>
                                <div class="form-group"><label>Sub Contratista</label><div class="input-wrapper"><input type="text" name="sub_contratista"></div></div>
                                <div class="form-group"><label>Relacionado a</label><div class="input-wrapper">
                                    <select name="relacionado_a" required><option value="PEXT">PEXT</option><option value="PINT">PINT</option></select>
                                </div></div>
                                <div class="form-group"><label>Lugar de Trabajo</label><div class="input-wrapper"><input type="text" name="lugar_trabajo" required></div></div>
                                <div class="form-group"><label>Fecha</label><div class="input-wrapper"><input type="date" name="fecha" required></div></div>
                                <div class="form-group"><label>Hora Inicio</label><div class="input-wrapper"><input type="time" name="hora_inicio" required></div></div>
                                <div class="form-group"><label>Hora Fin</label><div class="input-wrapper"><input type="time" name="hora_fin"></div></div>
                                <div class="form-group"><label>Total de Horas</label><div class="input-wrapper"><input type="text" name="total_horas"></div></div>
                            </div>
                        </div>

                        <div class="ruteo-form-section">
                            <h3 class="form-section-title"><span class="step-badge">2</span> Investigacion y Datos de Reporte</h3>
                            <div class="ruteo-fields-grid">
                                <div class="form-group"><label>Nombre del Supervisor Operativo</label><div class="input-wrapper"><input type="text" name="supervisor_operativo_nombre" required></div></div>
                                <div class="form-group"><label>Trabajador Reportante</label><div class="input-wrapper"><input type="text" name="trabajador_reportante" required></div></div>
                            </div>
                        </div>

                        <div class="ruteo-form-section">
                            <h3 class="form-section-title"><span class="step-badge">3</span> Edicion de Razones para la Negativa (Punto 3)</h3>
                            <div class="form-group"><textarea name="razones_negativa" rows="5" placeholder="Corriga o complemente las razones indicadas por el tecnico..." required style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border); background:var(--bg-subtle); color:var(--text-main);"></textarea></div>
                        </div>

                        <div class="ruteo-form-section">
                            <h3 class="form-section-title"><span class="step-badge">5</span> Solucion y Medidas Correctivas (Supervisor Op.)</h3>
                            <div class="ruteo-fields-grid">
                                <div class="form-group full-width"><label>Medidas Correctivas Aplicadas</label><div class="input-wrapper"><textarea name="medidas_correctivas" rows="3" required style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border); background:var(--bg-subtle); color:var(--text-main);"></textarea></div></div>
                                <div class="form-group"><label>Satisface Negativa al Trabajo?</label><div class="input-wrapper">
                                    <select name="satisface_negativa" required><option value="SI">SI</option><option value="NO">NO</option></select>
                                </div></div>
                            </div>
                        </div>

                        <div class="ruteo-form-section">
                            <h3 class="form-section-title"><span class="step-badge">6</span> Reinicio de Labores</h3>
                            <div class="ruteo-fields-grid">
                                <div class="form-group"><label>Se reinician las labores?</label><div class="input-wrapper">
                                    <select name="reinicia_labores" required><option value="SI">SI</option><option value="NO">NO</option></select>
                                </div></div>
                                <div class="form-group"><label>Fecha Reinicio</label><div class="input-wrapper"><input type="date" name="fecha_reinicio"></div></div>
                                <div class="form-group"><label>Hora Reinicio</label><div class="input-wrapper"><input type="time" name="hora_reinicio"></div></div>
                            </div>
                        </div>

                        <button type="submit" class="btn-primary ruteo-submit-btn">Guardar y Firmar como Supervisor Operativo</button>
                    </form>

                    <!-- ETAPA 3: SUPERVISOR SEGURIDAD (Punto 7) -->
                    <form id="form-negativa-seguridad" class="ruteo-form" style="display:none;">
                        <div class="ruteo-form-section">
                            <h3 class="form-section-title"><span class="step-badge">7</span> Verificacion del Supervisor de Seguridad / SST</h3>
                            <div class="ruteo-fields-grid">
                                <div class="form-group"><label>Nombre Supervisor Seguridad</label><div class="input-wrapper"><input type="text" name="supervisor_seguridad_nombre" required></div></div>
                                <div class="form-group full-width"><label>Observaciones de Seguridad SST</label><div class="input-wrapper"><textarea name="observaciones_seguridad" rows="3" required style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border); background:var(--bg-subtle); color:var(--text-main);"></textarea></div></div>
                            </div>
                        </div>
                        <button type="submit" class="btn-primary ruteo-submit-btn">Guardar y Firmar como Supervisor de Seguridad</button>
                    </form>

                    <!-- ETAPA 4: HSE (Punto 8) -->
                    <form id="form-negativa-hse" class="ruteo-form" style="display:none;">
                        <div class="ruteo-form-section">
                            <h3 class="form-section-title"><span class="step-badge">8</span> Cierre Final por Area HSE</h3>
                            <div class="ruteo-fields-grid">
                                <div class="form-group"><label>Nombre del Responsable HSE</label><div class="input-wrapper"><input type="text" name="hse_nombre" required></div></div>
                                <div class="form-group full-width"><label>Dictamen Final HSE</label><div class="input-wrapper"><textarea name="dictamen_hse" rows="3" required style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border); background:var(--bg-subtle); color:var(--text-main);"></textarea></div></div>
                            </div>
                        </div>
                        <button type="submit" class="btn-primary ruteo-submit-btn">Guardar y Firmar como Area HSE</button>
                    </form>

                    <div style="margin-top:20px; display:flex; gap:16px; align-items:center; flex-wrap:wrap;">
                        <div id="negativa-msg" class="ruteo-message"></div>
                    </div>

                    <button type="button" id="btn-negativa-exportar-pdf" class="btn-secondary" style="display:none; margin-top:16px; gap:8px;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Exportar Negativa a PDF (Formato HSE-RE-NEG-01)</span>
                    </button>
                    <div id="negativa-pdf-notice" style="display:none; font-size:12.5px; color:var(--text-muted); background:var(--bg-subtle); padding:10px 14px; border-radius:8px; border:1px solid var(--border); margin-top:16px;">
                        La exportacion a PDF (Formato HSE-RE-NEG-01) estara disponible cuando se tengan todas las firmas completas (Visto Bueno final del Area HSE).
                    </div>
                </div>
            </div>
        </section>

        <!-- SECCION 6: GESTION DE USUARIOS Y CONFIGURACION (SOLO ADMIN) -->
        <section class="ruteo-tab-content" id="tab-usuarios">
            <div class="ruteo-tab-protected-notice" style="display:none;">
                <div class="login-card-container" style="max-width:520px; margin: 30px auto; text-align:center; padding:32px 24px; background:var(--bg-glass); border:1px solid var(--border); border-radius:16px; backdrop-filter:blur(10px);">
                    <div style="width:64px; height:64px; margin:0 auto 16px auto; background:rgba(0, 151, 216, 0.12); border-radius:50%; display:flex; align-items:center; justify-content:center;">
                        <svg width="32" height="32" fill="none" stroke="#0097D8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h3 style="font-size:20px; font-weight:700; margin:0 0 8px 0; color:var(--menu-title);">Acceso Restringido - Inicia Sesion</h3>
                    <p style="font-size:14px; color:var(--text-muted); margin:0 0 24px 0;">Debes iniciar sesion como Administrador General para gestionar cuentas de usuario y clientes.</p>

                    <form class="ruteo-auth-login-form" style="text-align:left; margin-bottom:20px;">
                        <div class="form-group" style="margin-bottom:14px;">
                            <label style="font-size:13px; font-weight:600;">Nombre de Usuario o Correo Electronico</label>
                            <div class="input-wrapper">
                                <input type="text" name="username" placeholder="Usuario o correo@dominio.com" required>
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom:18px;">
                            <label style="font-size:13px; font-weight:600;">Clave de Acceso</label>
                            <div class="input-wrapper">
                                <input type="password" name="password" placeholder="--------" required>
                            </div>
                        </div>
                        <button type="submit" class="ruteo-submit-btn" style="width:100%;">
                            <span class="btn-text">Iniciar Sesion e Ingresar</span>
                            <div class="spinner"></div>
                        </button>
                        <div class="ruteo-message"></div>
                    </form>

                    <div class="demo-accounts-box" style="padding:12px; background:var(--bg-light); border:1px solid var(--border); border-radius:10px;">
                        <p style="font-size:12px; font-weight:600; margin:0 0 8px 0; color:var(--text-secondary);">Cuentas Rapidas de Prueba (Click para ingresar):</p>
                        <div style="display:flex; gap:6px; flex-wrap:wrap; justify-content:center;">
                            <button type="button" class="btn-demo-login portal-btn portal-btn--refresh" data-user="admingeneral" data-pass="AdminGeneral123!" style="font-size:11px; padding:4px 8px;">Admin General</button>
                            <button type="button" class="btn-demo-login portal-btn portal-btn--refresh" data-user="tecnico1" data-pass="Tecnico123!" style="font-size:11px; padding:4px 8px;">Tecnico</button>
                            <button type="button" class="btn-demo-login portal-btn portal-btn--refresh" data-user="supervisor1" data-pass="Supervisor123!" style="font-size:11px; padding:4px 8px;">Supervisor Op.</button>
                            <button type="button" class="btn-demo-login portal-btn portal-btn--refresh" data-user="seguridad1" data-pass="Seguridad123!" style="font-size:11px; padding:4px 8px;">Supervisor Seg.</button>
                            <button type="button" class="btn-demo-login portal-btn portal-btn--refresh" data-user="hse1" data-pass="Hse123!" style="font-size:11px; padding:4px 8px;">Area HSE</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ruteo-tab-protected-content">
                <div class="users-container">

                    <!-- GESTION DE CLIENTES Y LOGOS -->
                    <div class="user-create-card" id="clientes-card" style="margin-bottom:20px;">
                        <h4>Empresas Clientes y Logos para Reportes (Ej: CYMTEL)</h4>
                        <p class="users-sub" style="margin-bottom:14px;">Registra los clientes del software, sus logos y datos de contacto para los formatos oficiales.</p>
                        <form id="form-cliente" enctype="multipart/form-data" style="margin-bottom:20px;">
                            <div class="user-form-grid">
                                <div class="form-group">
                                    <label>Nombre del Cliente</label>
                                    <div class="input-wrapper">
                                        <input type="text" id="cli-nombre-input" placeholder="Ej: CYMTEL" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>RUC / Identificacion</label>
                                    <div class="input-wrapper">
                                        <input type="text" id="cli-ruc-input" placeholder="Ej: 20512345678">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Direccion / Sede</label>
                                    <div class="input-wrapper">
                                        <input type="text" id="cli-direccion-input" placeholder="Ej: Av. Central 123 - Lima">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Contacto / Telefono</label>
                                    <div class="input-wrapper">
                                        <input type="text" id="cli-contacto-input" placeholder="Ej: Ing. Juan Lopez - 987654321">
                                    </div>
                                </div>
                                <div class="form-group full-width">
                                    <label>Logo del Cliente (PNG, JPG, WEBP - max 2MB)</label>
                                    <input type="file" id="cli-logo-file" accept="image/*">
                                </div>
                            </div>
                            <div style="margin-top:12px; display:flex; gap:10px; justify-content:flex-end;">
                                <button type="submit" class="ruteo-submit-btn" style="min-width:140px;">
                                    <span>Guardar Cliente</span>
                                </button>
                            </div>
                            <div id="cli-msg" class="ruteo-message"></div>
                        </form>

                        <h5 style="margin-bottom:10px;">Clientes Registrados</h5>
                        <div style="overflow-x:auto;">
                            <table class="portal-table" style="width:100%;">
                                <thead>
                                    <tr>
                                        <th>Logo</th>
                                        <th>Nombre Cliente</th>
                                        <th>RUC</th>
                                        <th>Direccion / Sede</th>
                                        <th>Contacto</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="clientes-tbody"></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- LOGO DEL SISTEMA -->
                    <div class="user-create-card" id="site-logo-card" style="margin-bottom:20px;">
                        <h4>Logo del Sistema (Software O&M)</h4>
                        <p class="users-sub" style="margin-bottom:14px;">Esta imagen se muestra en la barra lateral para todos los usuarios.</p>
                        <form id="form-site-logo" enctype="multipart/form-data" style="display:flex; align-items:center; gap:18px; flex-wrap:wrap;">
                            <div class="brand-logo-icon" id="site-logo-preview" style="width:56px; height:56px;">
                                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <div class="form-group" style="flex:1; min-width:220px; margin-bottom:0;">
                                <label>Subir nueva imagen (PNG, JPG, WEBP o SVG - max 2MB)</label>
                                <div class="input-wrapper">
                                    <input type="file" id="site-logo-file" accept="image/png,image/jpeg,image/webp,image/svg+xml" required>
                                </div>
                            </div>
                            <button type="submit" class="ruteo-submit-btn" style="min-width:140px;">
                                <span>Guardar Logo</span>
                            </button>
                            <div id="site-logo-msg" class="ruteo-message"></div>
                        </form>
                    </div>

                    <div class="users-header-row">
                        <div>
                            <p class="users-sub">Administra accesos para Administradores y Operarios Workers con foto y sede asignada.</p>
                        </div>
                        <button class="btn-secondary" id="btn-toggle-create-user">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span>Nuevo Usuario</span>
                        </button>
                    </div>

                    <!-- FORMULARIO CREAR USUARIO AMPLIADO -->
                    <div class="user-create-card" id="user-create-card" style="display:none;">
                        <h4>Crear Nueva Cuenta de Usuario</h4>
                        <form id="form-create-user" enctype="multipart/form-data">
                            <div class="user-form-grid">
                                <div class="form-group">
                                    <label>Nombre Completo</label>
                                    <div class="input-wrapper">
                                        <input type="text" id="user-display-name-input" placeholder="Ej: Juan Perez" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Nombre de Usuario</label>
                                    <div class="input-wrapper">
                                        <input type="text" id="user-username-input" placeholder="Ej: jperez" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Correo Electronico</label>
                                    <div class="input-wrapper">
                                        <input type="email" id="user-email-input" placeholder="juan@ejemplo.com" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Numero Telefonico</label>
                                    <div class="input-wrapper">
                                        <input type="tel" id="user-phone-input" placeholder="+51 987 654 321">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Clave de Acceso</label>
                                    <div class="input-wrapper">
                                        <input type="password" id="user-password-input" placeholder="--------" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Rol de Usuario</label>
                                    <div class="input-wrapper">
                                        <select id="user-role-select">
                                            <option value="worker">Operario (Worker)</option>
                                            <option value="admin">Administrador (Admin)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Rol Negativa al Trabajo</label>
                                    <div class="input-wrapper">
                                        <select id="user-negativa-rol-select">
                                            <option value="">-- Sin Rol Especifico --</option>
                                            <option value="tecnico">Tecnico Reportante</option>
                                            <option value="supervisor_operativo">Supervisor Operativo</option>
                                            <option value="supervisor_seguridad">Supervisor de Seguridad</option>
                                            <option value="hse">Area HSE</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group full-width">
                                    <label>Puesto de Trabajo</label>
                                    <div class="input-wrapper">
                                        <input type="text" id="user-position-input" placeholder="Ej: Especialista Fibra Optica / Supervisor SST / Tecnico PEXT">
                                    </div>
                                </div>
                                <div class="form-group full-width">
                                    <label>Centro de Mantenimiento / PM Asignado</label>
                                    <div class="input-wrapper">
                                        <select id="user-pm-select">
                                            <option value="">-- Sin PM Especifico --</option>
                                            <?php foreach ($pm_list as $pm_item) : ?>
                                                <option value="<?php echo esc_attr($pm_item); ?>"><?php echo esc_html($pm_item); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group full-width">
                                    <label>Foto de Perfil / Imagen Avatar</label>
                                    <input type="file" id="user-avatar-input" accept="image/*">
                                    <div class="avatar-preview-box" id="user-avatar-preview"></div>
                                </div>
                            </div>
                            <div style="margin-top:16px; display:flex; gap:10px; justify-content:flex-end;">
                                <button type="button" class="portal-btn portal-btn--refresh" id="btn-cancel-create-user">Cancelar</button>
                                <button type="submit" class="ruteo-submit-btn" style="min-width:140px;">
                                    <span>Guardar Usuario</span>
                                </button>
                            </div>
                            <div id="create-user-msg" class="ruteo-message"></div>
                        </form>
                    </div>

                    <!-- TABLA DE USUARIOS -->
                    <div class="portal-table-wrapper">
                        <div class="portal-table-header">
                            <h3>Cuentas Registradas</h3>
                            <span class="portal-note" id="users-count-note">Cargando usuarios...</span>
                        </div>
                        <div class="portal-table-scroll">
                            <table class="portal-table">
                                <thead>
                                    <tr>
                                        <th>Avatar</th>
                                        <th>Usuario</th>
                                        <th>Nombre Completo</th>
                                        <th>Correo</th>
                                        <th>Telefono</th>
                                        <th>PM Asignado</th>
                                        <th>Rol</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="users-tbody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECCION 7: PERFIL DE USUARIO -->
        <section class="ruteo-tab-content" id="tab-perfil">
            <div class="ruteo-tab-protected-notice" style="display:none;">
                <div class="login-card-container" style="max-width:520px; margin: 30px auto; text-align:center; padding:32px 24px; background:var(--bg-glass); border:1px solid var(--border); border-radius:16px; backdrop-filter:blur(10px);">
                    <div style="width:64px; height:64px; margin:0 auto 16px auto; background:rgba(0, 151, 216, 0.12); border-radius:50%; display:flex; align-items:center; justify-content:center;">
                        <svg width="32" height="32" fill="none" stroke="#0097D8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h3 style="font-size:20px; font-weight:700; margin:0 0 8px 0; color:var(--menu-title);">Acceso Restringido - Inicia Sesion</h3>
                    <p style="font-size:14px; color:var(--text-muted); margin:0 0 24px 0;">Debes iniciar sesion con tu cuenta para consultar y editar tu Perfil de Usuario.</p>

                    <form class="ruteo-auth-login-form" style="text-align:left; margin-bottom:20px;">
                        <div class="form-group" style="margin-bottom:14px;">
                            <label style="font-size:13px; font-weight:600;">Nombre de Usuario o Correo Electronico</label>
                            <div class="input-wrapper">
                                <input type="text" name="username" placeholder="Usuario o correo@dominio.com" required>
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom:18px;">
                            <label style="font-size:13px; font-weight:600;">Clave de Acceso</label>
                            <div class="input-wrapper">
                                <input type="password" name="password" placeholder="--------" required>
                            </div>
                        </div>
                        <button type="submit" class="ruteo-submit-btn" style="width:100%;">
                            <span class="btn-text">Iniciar Sesion e Ingresar</span>
                            <div class="spinner"></div>
                        </button>
                        <div class="ruteo-message"></div>
                    </form>

                    <div class="demo-accounts-box" style="padding:12px; background:var(--bg-light); border:1px solid var(--border); border-radius:10px;">
                        <p style="font-size:12px; font-weight:600; margin:0 0 8px 0; color:var(--text-secondary);">Cuentas Rapidas de Prueba (Click para ingresar):</p>
                        <div style="display:flex; gap:6px; flex-wrap:wrap; justify-content:center;">
                            <button type="button" class="btn-demo-login portal-btn portal-btn--refresh" data-user="admingeneral" data-pass="AdminGeneral123!" style="font-size:11px; padding:4px 8px;">Admin General</button>
                            <button type="button" class="btn-demo-login portal-btn portal-btn--refresh" data-user="tecnico1" data-pass="Tecnico123!" style="font-size:11px; padding:4px 8px;">Tecnico</button>
                            <button type="button" class="btn-demo-login portal-btn portal-btn--refresh" data-user="supervisor1" data-pass="Supervisor123!" style="font-size:11px; padding:4px 8px;">Supervisor Op.</button>
                            <button type="button" class="btn-demo-login portal-btn portal-btn--refresh" data-user="seguridad1" data-pass="Seguridad123!" style="font-size:11px; padding:4px 8px;">Supervisor Seg.</button>
                            <button type="button" class="btn-demo-login portal-btn portal-btn--refresh" data-user="hse1" data-pass="Hse123!" style="font-size:11px; padding:4px 8px;">Area HSE</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ruteo-tab-protected-content">
                <div class="profile-container">
                    <div class="profile-card">
                        <div class="profile-header-banner">
                            <div class="profile-avatar-large" id="profile-avatar-img-box">
                                <span id="profile-avatar-large-text">?</span>
                            </div>
                            <div class="profile-header-info">
                                <h2 id="profile-name-heading">Nombre de Usuario</h2>
                                <p id="profile-role-heading">Cargando perfil...</p>
                            </div>
                        </div>

                        <form id="form-update-profile" class="profile-form">
                            <div class="form-grid-2">
                                <div class="form-group">
                                    <label>Nombre Completo</label>
                                    <div class="input-wrapper">
                                        <input type="text" id="prof-display-name" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Correo Electronico (Solo Lectura)</label>
                                    <div class="input-wrapper">
                                        <input type="email" id="prof-email" disabled readonly>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Numero Telefonico</label>
                                    <div class="input-wrapper">
                                        <input type="tel" id="prof-phone" placeholder="+51 987 654 321">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Puesto de Trabajo</label>
                                    <div class="input-wrapper">
                                        <input type="text" id="prof-position" placeholder="Ej: Especialista Fibra Optica / Supervisor SST">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Centro de Mantenimiento / PM Asignado</label>
                                    <div class="input-wrapper">
                                        <select id="prof-pm">
                                            <option value="">-- Seleccionar PM --</option>
                                            <?php foreach ($pm_list as $pm_item) : ?>
                                                <option value="<?php echo esc_attr($pm_item); ?>"><?php echo esc_html($pm_item); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-footer-actions">
                                <button type="submit" class="ruteo-submit-btn" style="min-width: 180px;">
                                    <span class="btn-text">Actualizar Perfil</span>
                                    <div class="spinner"></div>
                                </button>
                                <div id="profile-form-msg" class="ruteo-message"></div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECCION 8: INICIAR SESION -->
        <section class="ruteo-tab-content" id="tab-login">
            <div class="login-card-container">
                <div class="login-card-title">
                    <h3>Acceso al Aplicativo HSE Ruteo</h3>
                    <p>Ingresa tus credenciales para acceder al sistema</p>
                </div>
                <form class="ruteo-auth-login-form" id="ruteo-login-form">
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
                    <div class="ruteo-message login-message" id="login-message"></div>
                </form>
                <div class="demo-accounts-box" style="margin-top:20px; padding:12px; background:var(--bg-light); border:1px solid var(--border); border-radius:10px;">
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
        </section>

        <!-- MODAL DE SLA E INFORMES -->
        <div class="ruteo-modal-overlay" id="sla-modal-overlay" style="display:none;">
            <div class="ruteo-modal-card animate-fade-in">
                <div class="modal-header">
                    <h3 id="sla-modal-title">Formato SLA / Informe Tecnico</h3>
                    <button type="button" class="btn-close-modal" id="btn-close-sla-modal" title="Cerrar">&times;</button>
                </div>
                <div class="modal-body">
                    <p id="sla-modal-desc" style="color:var(--text-muted); margin-bottom:16px;">Complete los detalles para generar el documento estandarizado.</p>
                    <form id="form-generar-sla">
                        <div class="form-group" style="margin-bottom:14px;">
                            <label>Tramo de Intervencion</label>
                            <div class="input-wrapper">
                                <input type="text" id="sla-input-tramo" placeholder="Ej: Tramo Cusco - Sicuani" required>
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom:14px;">
                            <label>No. de Incidencia / Ticket</label>
                            <div class="input-wrapper">
                                <input type="text" id="sla-input-incidencia" placeholder="Ej: INC-90412" required>
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom:14px;">
                            <label>Responsable / Tecnico</label>
                            <div class="input-wrapper">
                                <input type="text" id="sla-input-tecnico" placeholder="Nombre del responsable" required>
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom:20px;">
                            <label>Detalle o Resumen del Informe</label>
                            <div class="input-wrapper">
                                <textarea id="sla-input-detalle" rows="3" placeholder="Escriba los hallazgos o acciones realizadas..."></textarea>
                            </div>
                        </div>
                        <div style="display:flex; gap:12px; justify-content:flex-end;">
                            <button type="button" class="portal-btn portal-btn--refresh" id="btn-cancel-sla-modal">Cancelar</button>
                            <button type="submit" class="ruteo-submit-btn" style="min-width:140px;">
                                <span>Generar Documento</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </main>
</div>
