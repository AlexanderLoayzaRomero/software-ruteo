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
    <div class="ruteo-sidebar-backdrop" id="ruteo-sidebar-backdrop"></div>
    
    <!-- BARRA LATERAL (SIDEBAR) -->
    <aside class="ruteo-sidebar" id="ruteo-sidebar">
        <div class="sidebar-header">
            <div class="sidebar-brand">
                <div class="brand-logo-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div class="brand-text">
                    <span class="brand-title">Software Ruteo</span>
                    <span class="brand-subtitle">Gestion y Ruteo</span>
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
        </section>

        <!-- SECCION 2: REGISTROS DE CAMPO -->
        <section class="ruteo-tab-content" id="tab-registros">
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

                    <div class="portal-error" id="portal-error" style="display:none;">
                        <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <p id="portal-error-msg">No se pudo conectar con Google Sheets.</p>
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
        </section>

        <!-- SECCION 3: NUEVO REGISTRO (FORMULARIO) -->
        <section class="ruteo-tab-content" id="tab-formulario">
            <?php include plugin_dir_path( __FILE__ ) . 'form-template.php'; ?>
        </section>

        <!-- SECCION 4: CONSUMO DE MATERIALES -->
        <section class="ruteo-tab-content" id="tab-materiales">
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
        </section>

        <!-- SECCION 5: SLA E INFORMES DE MANTENIMIENTO -->
        <section class="ruteo-tab-content" id="tab-sla-informes">
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
        </section>

        <!-- SECCION 6: GESTION DE USUARIOS (SOLO ADMIN) -->
        <section class="ruteo-tab-content" id="tab-usuarios">
            <div class="users-container">
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
        </section>

        <!-- SECCION 7: PERFIL DE USUARIO -->
        <section class="ruteo-tab-content" id="tab-perfil">
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

                            <div class="form-group full-width">
                                <label>Actualizar Foto de Perfil</label>
                                <input type="file" id="prof-avatar-file" accept="image/*">
                            </div>
                        </div>

                        <div class="form-footer-actions">
                            <button type="submit" class="ruteo-submit-btn">
                                <span>Guardar Cambios de Perfil</span>
                            </button>
                            <div id="prof-form-msg" class="ruteo-message"></div>
                        </div>
                    </form>
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
                        <label>Nombre de Usuario</label>
                        <div class="input-wrapper">
                            <input type="text" name="username" placeholder="Ej: admin_ruteo o worker_ruteo" required>
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

<script>
window.AJAX_URL = '<?php echo $ajax_url; ?>';
window.NONCE    = '<?php echo $nonce; ?>';
(function() {
    var AJAX_URL = window.AJAX_URL;
    var NONCE    = window.NONCE;
    var IS_LOGGED_IN_WP = <?php echo is_user_logged_in() ? 'true' : 'false'; ?>;
    var allRegistros = [];

    function downloadBlob(blob, filename) {
        if (typeof window.saveAs === 'function') {
            window.saveAs(blob, filename);
        } else {
            var url = window.URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            setTimeout(function() {
                if (a.parentNode) a.parentNode.removeChild(a);
                window.URL.revokeObjectURL(url);
            }, 500);
        }
    }
    window.downloadBlobRuteo = downloadBlob;

    function normalizarRegistro(r) {
        if (!r) return {};
        return {
            fecha: r.fecha || r.date || '',
            tramo: r.tramo || '',
            id_consol: r.id_consol || r.id_consol_ || '',
            estructura: r.estructura || '',
            tipo_estructura: r.tipo_estructura || r.tipo || '',
            altura: r.altura || r.altura_estructura || '',
            codigo: r.codigo || r.cdigo_estructura || r.codigo_estructura || '',
            ubicacion: r.ubicacion || r.ubicacin || '',
            mufa: r.mufa || '0',
            retencion: r.retencion || r.retencin || '0',
            suspension: r.suspension || r.suspensin || '0',
            cruceta: r.cruceta || '0',
            hebillas: r.hebillas || '0',
            fleje: r.fleje || '0',
            amortiguador: r.amortiguador || '0',
            brazo_extensor: r.brazo_extensor || '0',
            kit_retenida: r.kit_retenida || '0',
            observacion: r.observacion || r.observacin || '',
            foto_1: r.foto_1 || r.foto1_url || r.foto1 || '',
            foto_2: r.foto_2 || r.foto2_url || r.foto2 || '',
            link_kmz: r.link_kmz || r.kmz || '',
            link_docx: r.link_docx || r.link_doc || r.doc_url || r.docx || ''
        };
    }
    window.normalizarRegistroRuteo = normalizarRegistro;

    function formatFecha(str) {
        if (!str) return '-';
        return str;
    }

    function linkIcon(url, label, color) {
        if (!url) return '<span class="portal-cell-empty">-</span>';
        var isEarth = label.indexOf('Earth') > -1 || label.indexOf('KMZ') > -1;
        var icon = isEarth ? 
            '<svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/><path stroke-width="2" d="M2 12h20M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10z"/></svg>' :
            '<svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>';
        return '<a href="' + url + '" target="_blank" class="portal-link portal-link--' + color + '" title="Abrir en Google Drive">' +
               icon + ' ' + label + '</a>';
    }

    function renderTabla(registros) {
        var tbody = document.getElementById('portal-tbody');
        if (!tbody) return;
        tbody.innerHTML = '';

        var empty = document.getElementById('portal-empty');
        if (!registros || registros.length === 0) {
            if (empty) empty.style.display = 'flex';
            return;
        }
        if (empty) empty.style.display = 'none';

        registros.forEach(function(raw, idx) {
            var r = normalizarRegistro(raw);
            var tr = document.createElement('tr');
            tr.className = idx % 2 === 0 ? 'row-even' : 'row-odd';
            tr.innerHTML =
                '<td class="td-fecha">' + formatFecha(r.fecha) + '</td>' +
                '<td class="td-tramo">' + (r.tramo || '-') + '</td>' +
                '<td class="td-id"><strong>' + (r.id_consol || '-') + '</strong></td>' +
                '<td>' + (r.estructura || '-') + '</td>' +
                '<td>' + (r.tipo_estructura || '-') + '</td>' +
                '<td class="td-center">' + (r.altura || '-') + ' m</td>' +
                '<td><code class="portal-code">' + (r.codigo || '-') + '</code></td>' +
                '<td class="td-ubicacion">' + (r.ubicacion || '-') + '</td>' +
                '<td class="td-center">' + (r.mufa || '0') + '</td>' +
                '<td class="td-center">' + (r.retencion || '0') + '</td>' +
                '<td class="td-center">' + (r.suspension || '0') + '</td>' +
                '<td class="td-center">' + (r.cruceta || '0') + '</td>' +
                '<td>' + linkIcon(r.foto_1, 'Foto 1', 'blue') + '</td>' +
                '<td>' + linkIcon(r.foto_2, 'Foto 2', 'blue') + '</td>' +
                '<td>' + linkIcon(r.link_kmz, 'Earth KMZ', 'green') + '</td>' +
                '<td>' +
                '<a href="javascript:void(0)" onclick="window.generarDocumentoPDF(' + idx + ')" title="Descargar PDF" class="portal-link portal-link--red" style="margin-right:4px; padding:4px 8px;"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg> PDF</a>' +
                '<a href="javascript:void(0)" onclick="window.abrirODocumentoGoogleDocs(' + idx + ')" title="Abrir Google Doc en Drive" class="portal-link portal-link--blue" style="padding:4px 8px;"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg> Doc Drive</a>' +
                '</td>';
            tbody.appendChild(tr);
        });
    }

    function poblarFiltroTramo(registros) {
        var select = document.getElementById('filter-tramo');
        if (!select) return;
        var actual = select.value;
        var tramos = new Set(registros.map(function(raw) { return normalizarRegistro(raw).tramo; }).filter(Boolean));
        var tramosArr = Array.from(tramos).sort();
        select.innerHTML = '<option value="">Todos los tramos</option>';
        tramosArr.forEach(function(t) {
            var opt = document.createElement('option');
            opt.value = t;
            opt.textContent = t;
            if (t === actual) opt.selected = true;
            select.appendChild(opt);
        });
    }

    function filtrarRegistros() {
        var tramoFiltro = document.getElementById('filter-tramo') ? document.getElementById('filter-tramo').value : '';
        var textoBusqueda = document.getElementById('portal-search') ? document.getElementById('portal-search').value.toLowerCase().trim() : '';

        var filtrados = allRegistros.filter(function(raw) {
            var r = normalizarRegistro(raw);
            if (tramoFiltro && r.tramo !== tramoFiltro) return false;
            if (textoBusqueda) {
                var haystack = (r.tramo + ' ' + r.id_consol + ' ' + r.codigo + ' ' + r.ubicacion + ' ' + r.estructura + ' ' + r.tipo_estructura + ' ' + r.observacion).toLowerCase();
                if (haystack.indexOf(textoBusqueda) === -1) return false;
            }
            return true;
        });
        renderTabla(filtrados);
    }

    function calcularStats(registros) {
        var elTotal = document.getElementById('dash-stat-total');
        if (elTotal) elTotal.textContent = registros ? registros.length : 0;

        var tramos = new Set((registros || []).map(function(raw) { return normalizarRegistro(raw).tramo; }).filter(Boolean));
        var elTramos = document.getElementById('dash-stat-tramos');
        if (elTramos) elTramos.textContent = tramos.size;
    }

    function actualizarFechaHeader() {
        var hoy = new Date();
        var dias = ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
        var meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        var diaSemana = dias[hoy.getDay()];
        var diaNum = hoy.getDate();
        var mesNom = meses[hoy.getMonth()];
        var anio = hoy.getFullYear();
        var elDate = document.getElementById('current-date-str');
        if (elDate) {
            elDate.textContent = diaSemana + ', ' + diaNum + ' de ' + mesNom + ' de ' + anio;
        }
    }
    actualizarFechaHeader();

    var isFetching = false;
    var syncTimer = null;
    var datosCargadosEnProgreso = false;

    function iniciarSincronizacionAutomatica() {
        if (syncTimer) clearInterval(syncTimer);
        syncTimer = setInterval(function() {
            var isLogged = IS_LOGGED_IN_WP || (window.wpRuteoAjax && window.wpRuteoAjax.user && window.wpRuteoAjax.user.isLoggedIn);
            var activeTab = document.getElementById('tab-registros');
            if (isLogged && activeTab && activeTab.classList.contains('active')) {
                cargarDatos(true);
            }
        }, 60000);
    }

    function procesarRegistros(payload, silent) {
        var loader = document.getElementById('portal-loading');
        var section = document.getElementById('portal-data-section');
        if (loader) loader.style.display = 'none';
        if (section) section.style.display = 'block';

        allRegistros = payload.registros || [];
        window._ruteoRegistros = allRegistros;
        poblarFiltroTramo(allRegistros);
        renderTabla(allRegistros);
        calcularStats(allRegistros);

        var ahora = new Date();
        var elUpdate = document.getElementById('portal-last-update');
        if (elUpdate) {
            elUpdate.textContent = 'Actualizado: ' + ahora.toLocaleTimeString('es-PE');
        }

        isFetching = false;
        datosCargadosEnProgreso = false;
        iniciarSincronizacionAutomatica();
        document.dispatchEvent(new Event('ruteo:datos-cargados'));
    }

    function mostrarErrorProxy(silent) {
        isFetching = false;
        datosCargadosEnProgreso = false;

        if (silent) {
            var elUpdate = document.getElementById('portal-last-update');
            if (elUpdate) {
                elUpdate.textContent = 'Error de sincronizacion temporal';
            }
            return;
        }

        var loader = document.getElementById('portal-loading');
        var error = document.getElementById('portal-error');
        if (loader) loader.style.display = 'none';
        if (error) error.style.display = 'flex';
        var elErrMsg = document.getElementById('portal-error-msg');
        if (elErrMsg) {
            elErrMsg.innerHTML = 'Tiempo de espera agotado. El servidor WordPress no puede conectar con Google Sheets.<br><small>Verifica la conectividad del contenedor Docker a internet.</small>';
        }
    }

    function cargarDatos(silent) {
        if (isFetching) return;
        isFetching = true;

        var loader = document.getElementById('portal-loading');
        var section = document.getElementById('portal-data-section');
        var error = document.getElementById('portal-error');

        if (!silent) {
            if (loader) loader.style.display = 'flex';
            if (section) section.style.display = 'none';
            if (error) error.style.display = 'none';
        } else {
            var elUpdate = document.getElementById('portal-last-update');
            if (elUpdate) {
                elUpdate.textContent = 'Sincronizando...';
            }
        }

        // METODO 1: Proxy via PHP (intento principal, timeout 12s)
        var formData = new FormData();
        formData.append('action', 'ruteo_get_registros');
        formData.append('nonce', NONCE);

        var phpProxyPromise = fetch(AJAX_URL, { method: 'POST', body: formData })
        .then(function(resp) {
            if (!resp.ok) throw new Error('HTTP ' + resp.status);
            return resp.json();
        })
        .then(function(json) {
            if (!json.success) throw new Error((json.data && json.data.message) || 'Error proxy PHP');
            var payload = json.data;
            if (payload.status === 'error') throw new Error(payload.message || 'Error en Google Script');
            procesarRegistros(payload, silent);
            return true;
        });

        // Timeout 12s para el proxy PHP
        var phpPromise = Promise.race([
            phpProxyPromise,
            new Promise(function(_, reject) {
                setTimeout(function() { reject(new Error('proxy_timeout')); }, 12000);
            })
        ]);

        phpPromise.catch(function(err) {
            // METODO 2: JSONP directo a Google Script (fallback para cualquier error del proxy)
            console.warn('[Portal Ruteo] Proxy PHP fallo:', err.message);
            var webhookUrl = (window.wpRuteoAjax && window.wpRuteoAjax.webhook) ? window.wpRuteoAjax.webhook : '';
            if (webhookUrl) {
                var script = document.createElement('script');
                script.src = webhookUrl + '?callback=window._ruteoJsonpCallback';
                var timeoutId = setTimeout(function() {
                    if (script.parentNode) script.parentNode.removeChild(script);
                    mostrarErrorProxy(silent);
                }, 10000);
                window._ruteoJsonpCallback = function(data) {
                    clearTimeout(timeoutId);
                    if (script.parentNode) script.parentNode.removeChild(script);
                    if (data && data.status === 'success') {
                        procesarRegistros(data, silent);
                    } else {
                        mostrarErrorProxy(silent);
                    }
                };
                document.head.appendChild(script);
            } else {
                mostrarErrorProxy(silent);
            }
        });
    }

    var searchInput = document.getElementById('portal-search');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            filtrarRegistros();
        });
    }

    var filterTramo = document.getElementById('filter-tramo');
    if (filterTramo) {
        filterTramo.addEventListener('change', function() {
            filtrarRegistros();
        });
    }

    var btnRefresh = document.getElementById('btn-refresh-portal');
    if (btnRefresh) {
        btnRefresh.addEventListener('click', function() {
            cargarDatos(false);
        });
    }

    // Cargar datos automaticamente al cargar la pagina si el usuario esta autenticado
    function autoCargarPortal() {
        if (datosCargadosEnProgreso || isFetching) return;
        var isLogged = IS_LOGGED_IN_WP || (window.wpRuteoAjax && window.wpRuteoAjax.user && window.wpRuteoAjax.user.isLoggedIn);
        if (isLogged) {
            datosCargadosEnProgreso = true;
            cargarDatos(false);
        }
    }

    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(autoCargarPortal, 10);
    } else {
        document.addEventListener('DOMContentLoaded', autoCargarPortal);
        window.addEventListener('load', autoCargarPortal);
    }

    // Exponer globalmente para que app.js pueda recargar tras login o tras envio del formulario
    window.cargarDatosPortal = function(silent) {
        cargarDatos(silent);
    };
})();

    // Attach download button click handlers
    var _btnDl = document.getElementById("btn-download-pdf");
    var _btnXl = document.getElementById("btn-download-excel");

    function _generarPDF() {
        var data = window._ruteoRegistros || [];
        if (!data.length) { alert("No hay registros."); return; }
        function cargarJSPDF() {
            var j = (window.jspdf && window.jspdf.jsPDF) ? window.jspdf.jsPDF : window.jsPDF;
            if (!j) { var s = document.createElement("script"); s.src = "https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"; s.onload = cargarAutoTable; document.head.appendChild(s); return; }
            cargarAutoTable();
        }
        function cargarAutoTable() {
            var j = (window.jspdf && window.jspdf.jsPDF) ? window.jspdf.jsPDF : window.jsPDF;
            if (j && j.API && typeof j.API.autoTable === "function") { _hacerPDF(); return; }
            var s = document.createElement("script"); s.src = "https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"; s.onload = _hacerPDF; document.head.appendChild(s);
        }
        function _hacerPDF() {
            var j = (window.jspdf && window.jspdf.jsPDF) ? window.jspdf.jsPDF : window.jsPDF;
            var doc = new j({ orientation: "landscape", unit: "mm", format: "a4" });
            var w = doc.internal.pageSize.getWidth();
            var f = new Date().toLocaleDateString("es-PE", { day: "2-digit", month: "long", year: "numeric" });
            doc.setFillColor(0,151,216); doc.rect(0,0,w,28,"F"); doc.setTextColor(255,255,255); doc.setFontSize(16); doc.text("REPORTE GENERAL DE RUTEO DE CAMPO",14,13);
            doc.setFontSize(9); doc.text("Fecha: "+f+"  |  Total: "+data.length+" registros",14,21);
            var cols=[{header:"Fecha",dataKey:"fecha"},{header:"Tramo",dataKey:"tramo"},{header:"ID Consol",dataKey:"id_consol"},{header:"Estructura",dataKey:"estructura"},{header:"Tipo",dataKey:"tipo_estructura"},{header:"Alt.(m)",dataKey:"altura"},{header:"Codigo",dataKey:"codigo"},{header:"Ubicacion",dataKey:"ubicacion"},{header:"Mufa",dataKey:"mufa"},{header:"Ret.",dataKey:"retencion"},{header:"Susp.",dataKey:"suspension"},{header:"Cruceta",dataKey:"cruceta"},{header:"Heb.",dataKey:"hebillas"},{header:"Fleje",dataKey:"fleje"},{header:"Amort.",dataKey:"amortiguador"},{header:"Br.Ext.",dataKey:"brazo_extensor"},{header:"Kit Ret.",dataKey:"kit_retenida"},{header:"Obs.",dataKey:"observacion"}];
            var rows=data.map(function(raw){var r=window.normalizarRegistroRuteo?window.normalizarRegistroRuteo(raw):raw;var o={};cols.forEach(function(c){o[c.dataKey]=r[c.dataKey]||""});return o;});
            try{doc.autoTable({startY:32,columns:cols,body:rows,theme:"grid",headStyles:{fillColor:[0,151,216],textColor:[255,255,255],fontStyle:"bold",fontSize:7.5,halign:"center"},bodyStyles:{fontSize:7,cellPadding:2},alternateRowStyles:{fillColor:[244,246,249]},margin:{top:32,bottom:12,left:10,right:10},styles:{overflow:"linebreak"}})}catch(e){}
            var blob=doc.output("blob"), u=URL.createObjectURL(blob), a=document.createElement("a"); a.href=u; a.download="Reporte_Ruteo_"+f.replace(/\//g,"-")+".pdf"; document.body.appendChild(a); a.click(); setTimeout(function(){document.body.removeChild(a);URL.revokeObjectURL(u)},500);
        }
        cargarJSPDF();
    }

    function _generarExcel() {
        var data = window._ruteoRegistros || [];
        if (!data.length) { alert("No hay registros."); return; }
        function cargarExcelJS() {
            if (window.ExcelJS) { _hacerExcel(); return; }
            var s = document.createElement("script"); s.src = "https://cdn.jsdelivr.net/npm/exceljs@4.4.0/dist/exceljs.min.js"; s.onload = _hacerExcel; document.head.appendChild(s);
        }
        function _hacerExcel() {
            var EX = window.ExcelJS; if (!EX) { alert("Libreria Excel no disponible."); return; }
            if (_btnXl) { _btnXl.disabled = true; var _tx = _btnXl.querySelector(".btn-xl-text"); if (_tx) _tx.textContent = "Generando..."; }
            try {
                var regs = data.map(function(raw) { return window.normalizarRegistroRuteo ? window.normalizarRegistroRuteo(raw) : raw; });
                var wb = new EX.Workbook(), ws = wb.addWorksheet("Registros de Campo");
                ws.columns = [
                    { header:"Fecha", key:"fecha", width:16 },{ header:"Tramo", key:"tramo", width:14 },{ header:"ID Consol", key:"id_consol", width:14 },{ header:"Estructura", key:"estructura", width:14 },{ header:"Tipo", key:"tipo_estructura", width:14 },{ header:"Alt.(m)", key:"altura", width:10 },{ header:"Codigo", key:"codigo", width:14 },{ header:"Ubicacion", key:"ubicacion", width:26 },{ header:"Mufa", key:"mufa", width:8 },{ header:"Ret.", key:"retencion", width:8 },{ header:"Susp.", key:"suspension", width:8 },{ header:"Cruceta", key:"cruceta", width:8 },{ header:"Heb.", key:"hebillas", width:8 },{ header:"Fleje", key:"fleje", width:8 },{ header:"Amort.", key:"amortiguador", width:8 },{ header:"Br.Ext.", key:"brazo_extensor", width:8 },{ header:"Kit Ret.", key:"kit_retenida", width:8 },{ header:"Observacion", key:"observacion", width:28 },{ header:"Foto 1", key:"foto1", width:30 },{ header:"Foto 2", key:"foto2", width:30 },{ header:"Link KMZ", key:"link_kmz", width:32 }
                ];
                var hr = ws.getRow(1); hr.height = 30; hr.eachCell(function(c) { c.fill = { type:"pattern", pattern:"solid", fgColor:{ argb:"FF0097D8" } }; c.font = { bold:true, color:{ argb:"FFFFFFFF" }, size:10, name:"Calibri" }; c.alignment = { horizontal:"center", vertical:"middle", wrapText:true }; });
                regs.forEach(function(r, i) {
                    var row = ws.addRow({ fecha:r.fecha||"", tramo:r.tramo||"", id_consol:r.id_consol||"", estructura:r.estructura||"", tipo_estructura:r.tipo_estructura||"", altura:r.altura||"", codigo:r.codigo||"", ubicacion:r.ubicacion||"", mufa:r.mufa||"0", retencion:r.retencion||"0", suspension:r.suspension||"0", cruceta:r.cruceta||"0", hebillas:r.hebillas||"0", fleje:r.fleje||"0", amortiguador:r.amortiguador||"0", brazo_extensor:r.brazo_extensor||"0", kit_retenida:r.kit_retenida||"0", observacion:r.observacion||"", link_kmz:r.link_kmz||"", foto1:"", foto2:"" });
                    row.eachCell({ includeEmpty: true }, function(cell, colNum) {
                        if ((i%2)===0) cell.fill = { type:"pattern", pattern:"solid", fgColor:{ argb:"FFEFF6FF" } };
                        cell.font = { name:"Calibri", size:9 };
                        if (colNum === 19 && r.foto_1) { cell.value = { text:"Ver Foto 1", hyperlink: r.foto_1 }; cell.font = { name:"Calibri", size:9, color:{ argb:"FF2563EB" }, underline:true }; }
                        if (colNum === 20 && r.foto_2) { cell.value = { text:"Ver Foto 2", hyperlink: r.foto_2 }; cell.font = { name:"Calibri", size:9, color:{ argb:"FF2563EB" }, underline:true }; }
                        if (colNum === 21 && r.link_kmz) { cell.value = { text:"Abrir KMZ", hyperlink: r.link_kmz }; cell.font = { name:"Calibri", size:9, color:{ argb:"FF16A34A" }, underline:true }; }
                    });
                });
                wb.xlsx.writeBuffer().then(function(buf) {
                    var b = new Blob([buf], { type:"application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" }), u = URL.createObjectURL(b), a = document.createElement("a");
                    a.href = u; a.download = "Reporte_Ruteo.xlsx"; document.body.appendChild(a); a.click();
                    setTimeout(function() { document.body.removeChild(a); URL.revokeObjectURL(u); }, 500);
                    if (_btnXl) { _btnXl.disabled = false; var _tx2 = _btnXl.querySelector(".btn-xl-text"); if (_tx2) _tx2.textContent = "Descargar Excel"; }
                }).catch(function(e) { alert("Error Excel: "+e.message); if (_btnXl) { _btnXl.disabled = false; var _tx3 = _btnXl.querySelector(".btn-xl-text"); if (_tx3) _tx3.textContent = "Descargar Excel"; } });
            } catch(e) { alert("Error Excel: "+e.message); if (_btnXl) { _btnXl.disabled = false; var _tx4 = _btnXl.querySelector(".btn-xl-text"); if (_tx4) _tx4.textContent = "Descargar Excel"; } }
        }
        cargarExcelJS();
    }

    if (_btnDl) _btnDl.addEventListener("click", _generarPDF);
    if (_btnXl) _btnXl.addEventListener("click", _generarExcel);

    // Helper: convertir URLs de Drive a data URLs
    window.fetchImageDataUrlRuteo = function(url, timeoutMs) {
        if (!url) return Promise.resolve(null);
        timeoutMs = timeoutMs || 8000;
        var m1 = url.match(/drive\.google\.com\/file\/d\/([a-zA-Z0-9_-]+)/);
        var m2 = url.match(/drive\.google\.com\/open\?id=([a-zA-Z0-9_-]+)/);
        var fileId = (m1 && m1[1]) || (m2 && m2[1]);
        if (fileId) { url = 'https://drive.google.com/uc?export=view&id=' + fileId; }
        var form = new FormData(); form.append('action', 'ruteo_proxy_image'); form.append('nonce', '<?php echo $nonce; ?>');
        var proxyUrl = '<?php echo $ajax_url; ?>?url=' + encodeURIComponent(url);
        var fetchP = fetch(proxyUrl, { method: 'POST', body: form })
            .then(function(r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
            .then(function(j) { return (j.success && j.data && j.data.dataUrl) ? j.data.dataUrl : null; });
        var timeoutP = new Promise(function(_, reject) { setTimeout(function() { reject(new Error('timeout')); }, timeoutMs); });
        return Promise.race([fetchP, timeoutP]).catch(function() { return null; });
    };

    // PDF individual por registro con fotos optimizado para tablets
    window.generarDocumentoPDF = function(idx) {
        var raw = window._ruteoRegistros[idx]; if (!raw) return;
        var r = window.normalizarRegistroRuteo ? window.normalizarRegistroRuteo(raw) : raw;
        function _hacer(imgs) {
            var j = (window.jspdf && window.jspdf.jsPDF) ? window.jspdf.jsPDF : window.jsPDF;
            if (!j) { alert("Libreria PDF no disponible."); return; }
            var doc = new j({ orientation: "portrait", unit: "mm", format: "a4" });
            var w = doc.internal.pageSize.getWidth();
            var pageH = doc.internal.pageSize.getHeight();
            doc.setFillColor(0,151,216); doc.rect(0,0,w,32,"F"); doc.setTextColor(255,255,255);
            doc.setFontSize(18); doc.text("FICHA TECNICA DE REGISTRO", w/2, 14, { align: "center" });
            doc.setFontSize(10); doc.text("Fecha: "+r.fecha+" | Tramo: "+r.tramo, w/2, 24, { align: "center" });
            var data = [
                ["ID Consol", r.id_consol || '-'],
                ["Codigo Estructura", r.codigo || '-'],
                ["Estructura", r.estructura || '-'],
                ["Tipo Estructura", r.tipo_estructura || '-'],
                ["Altura", (r.altura || '-') + " m"],
                ["Ubicacion", r.ubicacion || '-'],
                ["Mufa / Herrajes", "Mufa: " + r.mufa + " | Ret: " + r.retencion + " | Susp: " + r.suspension],
                ["Cruceta / Accesorios", "Cruceta: " + r.cruceta + " | Hebillas: " + r.hebillas + " | Fleje: " + r.fleje],
                ["Amortiguador / Extensor", "Amort: " + r.amortiguador + " | Brazo Ext: " + r.brazo_extensor + " | Kit Ret: " + r.kit_retenida],
                ["Observacion", r.observacion || '-']
            ];
            var yy = 38;
            if (typeof doc.autoTable === "function") {
                doc.autoTable({ startY:yy, body:data, theme:"grid", headStyles:{fillColor:[0,151,216],textColor:[255,255,255],fontStyle:"bold"}, bodyStyles:{fontSize:8.5,cellPadding:2.5}, margin:{left:12,right:12} });
                yy = doc.lastAutoTable ? doc.lastAutoTable.finalY + 10 : yy + 90;
            }
            var imgW = 75, imgH = 58, gap = 12, x1 = (w - imgW*2 - gap) / 2, x2 = x1 + imgW + gap;
            if (yy + imgH + 15 > pageH) { doc.addPage(); yy = 20; }
            doc.setFontSize(10); doc.setTextColor(0,151,216);
            doc.text("EVIDENCIA FOTOGRAFICA EN CAMPO", w/2, yy, { align: "center" });
            yy += 6;
            function addImg(dUrl, x) {
                if (!dUrl) { doc.setFillColor(241,245,249); doc.rect(x,yy,imgW,imgH,"F"); doc.setTextColor(165,172,184); doc.setFontSize(9); doc.text("Sin Imagen",x+imgW/2,yy+imgH/2,{align:"center"}); return; }
                try { var t = dUrl.indexOf("image/png") !== -1 ? "PNG" : "JPEG"; doc.addImage(dUrl,t,x,yy,imgW,imgH); } catch(e) { doc.setFillColor(241,245,249); doc.rect(x,yy,imgW,imgH,"F"); doc.setTextColor(165,172,184); doc.setFontSize(9); doc.text("No disponible",x+imgW/2,yy+imgH/2,{align:"center"}); }
            }
            addImg(imgs[0], x1); addImg(imgs[1], x2);
            var blob = doc.output("blob");
            window.downloadBlobRuteo(blob, "Ficha_Ruteo_"+(r.codigo || r.id_consol)+".pdf");
        }
        var imgs = [null, null], pending = 0;
        function checkDone() { pending--; if (pending <= 0) _hacer(imgs); }
        [r.foto_1, r.foto_2].forEach(function(url, i) {
            if (!url) return;
            pending++;
            window.fetchImageDataUrlRuteo(url, 8000).then(function(dataUrl) { imgs[i] = dataUrl; checkDone(); }).catch(function() { checkDone(); });
        });
        if (pending === 0) _hacer(imgs);
    };

    // Word / Google Docs individual por registro
    function downloadHtmlWordBlobRuteo(r) {
        var htmlContent = "<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>" +
        "<head><meta charset='utf-8'><title>Ficha Tecnica</title><style>body{font-family:Calibri,sans-serif;padding:20px;} table{width:100%;border-collapse:collapse;margin:15px 0;} th,td{border:1px solid #ccc;padding:8px;text-align:left;} th{background:#0097D8;color:#fff;} h1{color:#0097D8;text-align:center;}</style></head>" +
        "<body>" +
        "<h1>FICHA TECNICA DE REGISTRO DE CAMPO</h1>" +
        "<p style='text-align:center;'><b>Fecha:</b> " + (r.fecha || "-") + " | <b>Tramo:</b> " + (r.tramo || "-") + " | <b>Codigo:</b> " + (r.codigo || "-") + "</p>" +
        "<table>" +
        "<tr><th>Parametro</th><th>Valor Registrado</th></tr>" +
        "<tr><td>Estructura</td><td>" + (r.estructura || "-") + "</td></tr>" +
        "<tr><td>Tipo Estructura</td><td>" + (r.tipo_estructura || "-") + "</td></tr>" +
        "<tr><td>Altura</td><td>" + (r.altura || "-") + " m</td></tr>" +
        "<tr><td>Ubicacion</td><td>" + (r.ubicacion || "-") + "</td></tr>" +
        "<tr><td>ID Consol</td><td>" + (r.id_consol || "-") + "</td></tr>" +
        "<tr><td>Mufa</td><td>" + (r.mufa || "0") + "</td></tr>" +
        "<tr><td>Retencion</td><td>" + (r.retencion || "0") + "</td></tr>" +
        "<tr><td>Suspension</td><td>" + (r.suspension || "0") + "</td></tr>" +
        "<tr><td>Cruceta</td><td>" + (r.cruceta || "0") + "</td></tr>" +
        "<tr><td>Hebillas</td><td>" + (r.hebillas || "0") + "</td></tr>" +
        "<tr><td>Fleje</td><td>" + (r.fleje || "0") + "</td></tr>" +
        "<tr><td>Amortiguador</td><td>" + (r.amortiguador || "0") + "</td></tr>" +
        "<tr><td>Brazo Extensor</td><td>" + (r.brazo_extensor || "0") + "</td></tr>" +
        "<tr><td>Kit Retenida</td><td>" + (r.kit_retenida || "0") + "</td></tr>" +
        "<tr><td>Observacion</td><td>" + (r.observacion || "-") + "</td></tr>" +
        "</table>";

        if (r.foto_1) htmlContent += "<p><b>Foto 1:</b> <a href='" + r.foto_1 + "'>" + r.foto_1 + "</a></p>";
        if (r.foto_2) htmlContent += "<p><b>Foto 2:</b> <a href='" + r.foto_2 + "'>" + r.foto_2 + "</a></p>";

        htmlContent += "</body></html>";

        var blob = new Blob(['\ufeff', htmlContent], { type: 'application/msword' });
        var fileName = 'Ficha_Ruteo_' + (r.codigo || r.id_consol || 'Registro') + '.doc';
        if (typeof window.downloadBlobRuteo === 'function') {
            window.downloadBlobRuteo(blob, fileName);
        } else {
            var u = URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = u; a.download = fileName;
            document.body.appendChild(a); a.click();
            setTimeout(function() { document.body.removeChild(a); URL.revokeObjectURL(u); }, 500);
        }
    }

    window.abrirODocumentoGoogleDocs = function(idx) {
        var raw = window._ruteoRegistros[idx]; if (!raw) return;
        var r = window.normalizarRegistroRuteo ? window.normalizarRegistroRuteo(raw) : raw;
        
        if (r.link_docx && r.link_docx.length > 5) {
            window.open(r.link_docx, '_blank');
            return;
        }

        var win = window.open('about:blank', '_blank');
        if (win) {
            win.document.write('<div style="font-family:sans-serif; padding:40px; text-align:center; color:#0097D8;"><h2>Generando documento Google Docs en Drive...</h2><p>Por favor espere unos segundos mientras se abre en Google Drive.</p></div>');
        }

        var activeNonce   = window.NONCE || '';
        var activeAjaxUrl = window.AJAX_URL || '';

        var formData = new FormData();
        formData.append('action', 'ruteo_proxy_post');
        formData.append('nonce', activeNonce);
        formData.append('payload', JSON.stringify({
            action_type: 'create_doc',
            record: r
        }));

        fetch(activeAjaxUrl, { method: 'POST', body: formData })
        .then(function(res) { return res.json(); })
        .then(function(json) {
            var docUrl = '';
            if (json.success && json.data) {
                var resData = typeof json.data === 'string' ? JSON.parse(json.data) : json.data;
                docUrl = resData.doc_url || resData.url || '';
            }
            if (docUrl) {
                r.link_docx = docUrl;
                if (win) win.location.href = docUrl;
            } else {
                var driveFolderUrl = 'https://drive.google.com/drive/folders/1e9qvf_OKyqzCTxzhs8cF0E3t61UVlRXO';
                if (win) win.location.href = driveFolderUrl;
            }
        })
        .catch(function() {
            var driveFolderUrl = 'https://drive.google.com/drive/folders/1e9qvf_OKyqzCTxzhs8cF0E3t61UVlRXO';
            if (win) win.location.href = driveFolderUrl;
        });
    };

    window.generarDocumentoWord = window.abrirODocumentoGoogleDocs;

    // Pre-load CDN libraries
    (function() {
        var libs = ["https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js","https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js","https://cdn.jsdelivr.net/npm/exceljs@4.4.0/dist/exceljs.min.js","https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js","https://unpkg.com/docx@8.5.0/build/index.js"];
        libs.forEach(function(src) { var s = document.createElement("script"); s.src = src; s.async = true; document.head.appendChild(s); });
    })();
</script>

<script>
    var CORP_BLUE    = [0, 151, 216];       // #0097D8
    var CORP_BLUE_HX = '#0097D8';
    var CORP_DARK    = [15, 23, 42];        // Fondo oscuro
    var CORP_GREEN   = [131, 202, 22];      // #83CA16
    var CORP_GRAY    = [165, 172, 184];     // #A5ACB8

    var btnDl = document.getElementById('btn-download-pdf');
    var btnXl = document.getElementById('btn-download-excel');

    // -------------------------------------------------------
    // Helper: datos del usuario logueado
    // -------------------------------------------------------
    function getUsuario() {
        if (window.wpRuteoAjax && window.wpRuteoAjax.user) {
            return window.wpRuteoAjax.user;
        }
        return { displayName: 'Operario', username: '', role: 'worker', isLoggedIn: false };
    }

    // -------------------------------------------------------
    // Helper: proxy de imagen para PDF y Excel con timeout
    // -------------------------------------------------------
    window.fetchImageDataUrl = function fetchImageDataUrl(url, timeoutMs) {
        if (!url) return Promise.resolve(null);
        timeoutMs = timeoutMs || 15000;
        var m1 = url.match(/drive\.google\.com\/file\/d\/([a-zA-Z0-9_-]+)/);
        var m2 = url.match(/drive\.google\.com\/open\?id=([a-zA-Z0-9_-]+)/);
        var fileId = (m1 && m1[1]) || (m2 && m2[1]);
        if (fileId) {
            url = 'https://drive.google.com/uc?export=view&id=' + fileId;
        }
        var form = new FormData();
        form.append('action', 'ruteo_proxy_image');
        form.append('nonce', '<?php echo $nonce; ?>');
        var proxyUrl = '<?php echo $ajax_url; ?>?url=' + encodeURIComponent(url);
        // Timeout para evitar que una imagen lenta bloquee todo
        var fetchPromise = fetch(proxyUrl, {
            method: 'POST',
            body: form
        })
        .then(function(r) { 
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json(); 
        })
        .then(function(j) { 
            return (j.success && j.data && j.data.dataUrl) ? j.data.dataUrl : null; 
        });
        var timeoutPromise = new Promise(function(_, reject) {
            setTimeout(function() { reject(new Error('timeout')); }, timeoutMs);
        });
        return Promise.race([fetchPromise, timeoutPromise])
        .catch(function() { return null; });
    };

    // -------------------------------------------------------
    // PDF GRUPAL - todos los registros
    // -------------------------------------------------------
    function generarPDF() {
        var registros = window._ruteoRegistros || [];
        if (!registros.length) { alert('No hay registros cargados para exportar.'); return; }

        var jsPDFConstructor = (window.jspdf && window.jspdf.jsPDF) ? window.jspdf.jsPDF : window.jsPDF;

        if (!jsPDFConstructor) {
            if (btnDl) {
                btnDl.disabled = true;
                var ts = btnDl.querySelector('.btn-dl-text');
                if (ts) ts.textContent = 'Cargando PDF...';
            }
            // Cargar jspdf dinamicamente
            var s = document.createElement('script');
            s.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
            s.onload = function() {
                var s2 = document.createElement('script');
                s2.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js';
                s2.onload = function() { generarPDF(); };
                document.head.appendChild(s2);
            };
            s.onerror = function() {
                alert('No se pudo cargar la libreria PDF. Verifica tu conexion a internet.');
                if (btnDl) { btnDl.disabled = false; var ts2 = btnDl.querySelector('.btn-dl-text'); if (ts2) ts2.textContent = 'Descargar PDF'; }
            };
            document.head.appendChild(s);
            return;
        }

        if (btnDl) {
            btnDl.disabled = true;
            var ts = btnDl.querySelector('.btn-dl-text');
            if (ts) ts.textContent = 'Generando...';
        }

        try {
            var doc = new jsPDFConstructor({ orientation: 'landscape', unit: 'mm', format: 'a4' });
            var pageW = doc.internal.pageSize.getWidth();
            var pageH = doc.internal.pageSize.getHeight();
            var fecha = new Date().toLocaleDateString('es-PE', { day: '2-digit', month: 'long', year: 'numeric' });
            var fechaCorta = new Date().toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric' });
            var usuario = getUsuario();

            // === CABECERA DEGRADADA ===
            doc.setFillColor(0, 151, 216);
            doc.rect(0, 0, pageW, 32, 'F');
            // Franja oscura lateral izquierda
            doc.setFillColor(0, 100, 160);
            doc.rect(0, 0, 6, 32, 'F');

            // Titulo
            doc.setTextColor(255, 255, 255);
            doc.setFontSize(18);
            doc.setFont('helvetica', 'bold');
            doc.text('REPORTE GENERAL DE RUTEO DE CAMPO', 14, 13);
            doc.setFontSize(9);
            doc.setFont('helvetica', 'normal');
            doc.text('Fecha de generacion: ' + fecha, 14, 21);
            doc.text('Responsable: ' + (usuario.displayName || usuario.username || 'Sistema'), 14, 27);

            // Estadisticas en esquina derecha
            doc.setFontSize(9);
            doc.text('Total registros: ' + registros.length, pageW - 14, 13, { align: 'right' });

            var tramos = new Set(registros.map(function(raw) {
                var r = window.normalizarRegistroRuteo ? window.normalizarRegistroRuteo(raw) : raw;
                return r.tramo;
            }).filter(Boolean));
            doc.text('Tramos activos: ' + tramos.size, pageW - 14, 21, { align: 'right' });
            doc.text('Generado por: ' + (usuario.role === 'admin' ? 'Administrador' : 'Operario'), pageW - 14, 27, { align: 'right' });

            // === TABLA DE REGISTROS ===
            var cols = [
                { header: 'Fecha',       dataKey: 'fecha' },
                { header: 'Tramo',       dataKey: 'tramo' },
                { header: 'ID Consol',   dataKey: 'id_consol' },
                { header: 'Estructura',  dataKey: 'estructura' },
                { header: 'Tipo',        dataKey: 'tipo_estructura' },
                { header: 'Alt. (m)',    dataKey: 'altura' },
                { header: 'Codigo',      dataKey: 'codigo' },
                { header: 'Ubicacion',   dataKey: 'ubicacion' },
                { header: 'Mufa',        dataKey: 'mufa' },
                { header: 'Ret.',        dataKey: 'retencion' },
                { header: 'Susp.',       dataKey: 'suspension' },
                { header: 'Cruceta',     dataKey: 'cruceta' },
                { header: 'Heb.',        dataKey: 'hebillas' },
                { header: 'Fleje',       dataKey: 'fleje' },
                { header: 'Amort.',      dataKey: 'amortiguador' },
                { header: 'Brazo',       dataKey: 'brazo_extensor' },
                { header: 'Kit Ret.',    dataKey: 'kit_retenida' },
            ];

            var rows = registros.map(function(raw) {
                var r = window.normalizarRegistroRuteo ? window.normalizarRegistroRuteo(raw) : raw;
                var out = {};
                cols.forEach(function(c) { out[c.dataKey] = r[c.dataKey] || ''; });
                return out;
            });

            var autoTableFn = doc.autoTable || (window.jspdf && window.jspdf.autoTable) || (window.jsPDF && window.jsPDF.autoTable);
            var autoTableOptions = {
                startY: 36,
                columns: cols,
                body: rows,
                theme: 'grid',
                headStyles: {
                    fillColor: CORP_BLUE,
                    textColor: [255, 255, 255],
                    fontStyle: 'bold',
                    fontSize: 7,
                    halign: 'center',
                    cellPadding: { top: 3, bottom: 3, left: 2, right: 2 }
                },
                bodyStyles: {
                    fontSize: 6.5,
                    cellPadding: { top: 2, bottom: 2, left: 2, right: 2 },
                    textColor: [30, 30, 30]
                },
                alternateRowStyles: { fillColor: [239, 246, 255] },
                columnStyles: {
                    0: { cellWidth: 18 },
                    1: { cellWidth: 20, fontStyle: 'bold' },
                    2: { cellWidth: 18, fontStyle: 'bold' },
                    7: { cellWidth: 28 },
                },
                margin: { top: 36, bottom: 15, left: 14, right: 14 },
                didDrawPage: function(data) {
                    doc.setFontSize(7);
                    doc.setTextColor(148, 163, 184);
                    doc.setDrawColor(200, 200, 200);
                    doc.line(14, pageH - 10, pageW - 14, pageH - 10);
                    doc.text('Reporte de Ruteo de Campo - ' + fechaCorta + ' - Generado por: ' + (usuario.displayName || 'Sistema'), 14, pageH - 5);
                    var pageNum = (doc.internal && doc.internal.getCurrentPageInfo) ? doc.internal.getCurrentPageInfo().pageNumber : (data.pageNumber || 1);
                    doc.text('Pag. ' + pageNum, pageW - 14, pageH - 5, { align: 'right' });
                }
            };

            if (typeof doc.autoTable === 'function') {
                doc.autoTable(autoTableOptions);
            } else if (typeof autoTableFn === 'function') {
                autoTableFn(doc, autoTableOptions);
            }

            var nombre = 'Reporte_Ruteo_' + fechaCorta.replace(/\//g, '-') + '.pdf';
            var blob = doc.output('blob');
            window.downloadBlobRuteo(blob, nombre);

        } catch(err) {
            console.error('[PDF] Error al generar:', err);
            alert('Error generando PDF: ' + err.message);
        } finally {
            if (btnDl) {
                btnDl.disabled = false;
                var ts2 = btnDl.querySelector('.btn-dl-text');
                if (ts2) ts2.textContent = 'Descargar PDF';
            }
        }
    }

    // Adjuntar eventos como respaldo
    if (btnDl) btnDl.addEventListener('click', generarPDF);
    if (btnXl) btnXl.addEventListener('click', generarExcel);

    // -------------------------------------------------------
    // EXCEL con ExcelJS - imagenes incrustadas + formato premium
    // -------------------------------------------------------
    function generarExcel() {
        var registros = window._ruteoRegistros || [];
        if (!registros.length) { alert('No hay registros cargados para exportar.'); return; }

        if (!window.ExcelJS) {
            if (btnXl) {
                btnXl.disabled = true;
                var ts = btnXl.querySelector('.btn-xl-text');
                if (ts) ts.textContent = 'Cargando Excel...';
            }
            // Cargar exceljs dinamicamente
            var s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/exceljs@4.4.0/dist/exceljs.min.js';
            s.onload = function() {
                generarExcel();
            };
            s.onerror = function() {
                alert('No se pudo cargar la libreria Excel. Verifica tu conexion a internet.');
                if (btnXl) { btnXl.disabled = false; var ts2 = btnXl.querySelector('.btn-xl-text'); if (ts2) ts2.textContent = 'Descargar Excel'; }
            };
            document.head.appendChild(s);
            return;
        }

        if (btnXl) {
            btnXl.disabled = true;
            var ts = btnXl.querySelector('.btn-xl-text');
            if (ts) ts.textContent = 'Preparando imagenes (0%)...';
        }

        var usuario    = getUsuario();
        var fechaCorta = new Date().toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric' });
        var fechaLarga = new Date().toLocaleDateString('es-PE', { day: '2-digit', month: 'long',    year: 'numeric' });

        var regs = registros.map(function(raw) {
            return window.normalizarRegistroRuteo ? window.normalizarRegistroRuteo(raw) : raw;
        });
        var tramosUnicos = new Set(regs.map(function(r) { return r.tramo; }).filter(Boolean)).size;

        function cargarLote(desde, allResults) {
            if (!allResults) allResults = [];
            var hasta = Math.min(desde + 5, regs.length);
            var lotePromises = [];
            for (var k = desde; k < hasta; k++) {
                var r = regs[k];
                var f1 = r.foto_1;
                var f2 = r.foto_2;
                var p1 = (window.fetchImageDataUrl && f1) ? window.fetchImageDataUrl(f1, 4000).catch(function() { return null; }) : Promise.resolve(null);
                var p2 = (window.fetchImageDataUrl && f2) ? window.fetchImageDataUrl(f2, 4000).catch(function() { return null; }) : Promise.resolve(null);
                lotePromises.push(Promise.all([p1, p2]).catch(function() { return [null, null]; }));
            }
            return Promise.all(lotePromises).then(function(resultados) {
                resultados.forEach(function(par) { allResults.push(par || [null, null]); });
                if (btnXl) {
                    var pct = Math.round((allResults.length / regs.length) * 100);
                    var ts = btnXl.querySelector('.btn-xl-text');
                    if (ts) ts.textContent = 'Imagenes ' + pct + '%...';
                }
                if (hasta < regs.length) {
                    return cargarLote(hasta, allResults);
                } else {
                    return allResults;
                }
            }).catch(function(err) {
                console.warn('[Excel] Error en lote imagenes, continuando sin imagenes:', err);
                while (allResults.length < regs.length) {
                    allResults.push([null, null]);
                }
                return allResults;
            });
        }

        cargarLote(0).then(function(allImgs) {
            try {
                if (btnXl) { var ts2 = btnXl.querySelector('.btn-xl-text'); if (ts2) ts2.textContent = 'Generando Excel...'; }

                var EX = window.ExcelJS;
                var wb = new EX.Workbook();
                wb.creator = usuario.displayName || 'Aplicativo Ruteo';
                wb.created = new Date();

                // HOJA 1 - PORTADA
                var wsInfo = wb.addWorksheet('Informacion', { pageSetup: { paperSize: 9, orientation: 'portrait' } });
                wsInfo.getColumn('A').width = 26;
                wsInfo.getColumn('B').width = 40;
                wsInfo.mergeCells('A1:B1');
                var tc = wsInfo.getCell('A1');
                tc.value = 'REPORTE DE RUTEO DE CAMPO';
                tc.font  = { bold: true, size: 18, color: { argb: 'FFFFFFFF' }, name: 'Calibri' };
                tc.fill  = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF0097D8' } };
                tc.alignment = { horizontal: 'center', vertical: 'middle' };
                wsInfo.getRow(1).height = 42;
                wsInfo.mergeCells('A2:B2');
                var sc = wsInfo.getCell('A2');
                sc.value = 'Aplicativo de Ruteo de Campo - Sistema de Captura de Campo';
                sc.font  = { size: 11, color: { argb: 'FFC8EBFF' }, name: 'Calibri' };
                sc.fill  = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF005080' } };
                sc.alignment = { horizontal: 'center', vertical: 'middle' };
                wsInfo.getRow(2).height = 22;
                wsInfo.getRow(3).height = 10;
                var infoRows = [
                    ['Fecha de Generacion', fechaLarga],
                    ['Responsable', usuario.displayName || usuario.username || 'Sistema'],
                    ['Rol', usuario.role === 'admin' ? 'Administrador' : 'Operario de Campo'],
                    ['Total de Registros', registros.length],
                    ['Tramos Activos', tramosUnicos],
                    ['Sistema', 'Aplicativo de Ruteo de Campo'],
                ];
                infoRows.forEach(function(rd, i) {
                    var n = i + 4;
                    wsInfo.getRow(n).height = 22;
                    var ca = wsInfo.getCell('A' + n);
                    ca.value = rd[0]; ca.font = { bold: true, color: { argb: 'FF005080' }, name: 'Calibri', size: 11 };
                    ca.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFEFF6FF' } };
                    ca.alignment = { vertical: 'middle', horizontal: 'left', indent: 1 };
                    ca.border = { top:{style:'thin',color:{argb:'FFDBEAFE'}}, left:{style:'thin',color:{argb:'FFDBEAFE'}}, bottom:{style:'thin',color:{argb:'FFDBEAFE'}}, right:{style:'thin',color:{argb:'FFDBEAFE'}} };
                    var cb = wsInfo.getCell('B' + n);
                    cb.value = rd[1]; cb.font = { name: 'Calibri', size: 11, color: { argb: 'FF0F172A' } };
                    cb.alignment = { vertical: 'middle', horizontal: 'left', indent: 1 };
                    cb.border = { top:{style:'thin',color:{argb:'FFDBEAFE'}}, left:{style:'thin',color:{argb:'FFDBEAFE'}}, bottom:{style:'thin',color:{argb:'FFDBEAFE'}}, right:{style:'thin',color:{argb:'FFDBEAFE'}} };
                });

                // HOJA 2 - REGISTROS CON IMAGENES
                var ws = wb.addWorksheet('Registros de Campo', {
                    pageSetup: { paperSize: 9, orientation: 'landscape', fitToPage: true, fitToWidth: 1 },
                    views: [{ state: 'frozen', ySplit: 1 }]
                });
                var COL_FOTO1 = 19;
                var COL_FOTO2 = 20;
                ws.columns = [
                    { header: 'Fecha',        key: 'fecha',          width: 14 },
                    { header: 'Tramo',        key: 'tramo',          width: 18 },
                    { header: 'ID Consol',    key: 'id_consol',      width: 16 },
                    { header: 'Estructura',   key: 'estructura',     width: 18 },
                    { header: 'Tipo',         key: 'tipo',           width: 16 },
                    { header: 'Alt. (m)',     key: 'altura',         width: 10 },
                    { header: 'Codigo',       key: 'codigo',         width: 16 },
                    { header: 'Ubicacion',    key: 'ubicacion',      width: 28 },
                    { header: 'Mufa',         key: 'mufa',           width: 8  },
                    { header: 'Ret.',         key: 'retencion',      width: 8  },
                    { header: 'Susp.',        key: 'suspension',     width: 8  },
                    { header: 'Cruceta',      key: 'cruceta',        width: 8  },
                    { header: 'Heb.',         key: 'hebillas',       width: 8  },
                    { header: 'Fleje',        key: 'fleje',          width: 8  },
                    { header: 'Amort.',       key: 'amortiguador',   width: 8  },
                    { header: 'Br. Ext.',     key: 'brazo_extensor', width: 8  },
                    { header: 'Kit Ret.',     key: 'kit_retenida',   width: 8  },
                    { header: 'Observacion',  key: 'observacion',    width: 32 },
                    { header: 'Foto 1',       key: 'foto1',          width: 28 },
                    { header: 'Foto 2',       key: 'foto2',          width: 28 },
                ];
                var headerRow = ws.getRow(1);
                headerRow.height = 36;
                headerRow.eachCell(function(cell) {
                    cell.fill      = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF0097D8' } };
                    cell.font      = { bold: true, color: { argb: 'FFFFFFFF' }, size: 11, name: 'Calibri' };
                    cell.alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };
                    cell.border    = { top:{style:'medium',color:{argb:'FF005080'}}, left:{style:'thin',color:{argb:'FF0097D8'}}, bottom:{style:'medium',color:{argb:'FF005080'}}, right:{style:'thin',color:{argb:'FF0097D8'}} };
                });
                var tramoCol = 2;
                var idCol = 3;
                [tramoCol, idCol].forEach(function(c) {
                    var cell = ws.getCell(1, c);
                    cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF005080' } };
                    cell.font = { bold: true, color: { argb: 'FFFFFFFF' }, size: 11, name: 'Calibri' };
                });
                var IMG_ROW_HEIGHT = 150;
                regs.forEach(function(r, i) {
                    var rowNum    = i + 2;
                    var fillColor = (i % 2 === 0) ? 'FFFFFFFF' : 'FFEFF6FF';
                    var row = ws.addRow({
                        fecha:r.fecha||'', tramo:r.tramo||'', id_consol:r.id_consol||'',
                        estructura:r.estructura||'', tipo:r.tipo_estructura||'', altura:r.altura||'',
                        codigo:r.codigo||'', ubicacion:r.ubicacion||'', mufa:r.mufa||'0',
                        retencion:r.retencion||'0', suspension:r.suspension||'0', cruceta:r.cruceta||'0',
                        hebillas:r.hebillas||'0', fleje:r.fleje||'0', amortiguador:r.amortiguador||'0',
                        brazo_extensor:r.brazo_extensor||'0', kit_retenida:r.kit_retenida||'0',
                        observacion:r.observacion||'', foto1:'', foto2:''
                    });
                    row.height = IMG_ROW_HEIGHT;
                    row.eachCell({ includeEmpty: true }, function(cell, colNumber) {
                        cell.fill      = { type: 'pattern', pattern: 'solid', fgColor: { argb: fillColor } };
                        cell.font      = { name: 'Calibri', size: 9, color: { argb: 'FF0F172A' } };
                        cell.alignment = { vertical: 'middle', wrapText: true, horizontal: colNumber <= 3 ? 'left' : 'center' };
                        cell.border    = { top:{style:'thin',color:{argb:'FFDBEAFE'}}, left:{style:'thin',color:{argb:'FFDBEAFE'}}, bottom:{style:'thin',color:{argb:'FFDBEAFE'}}, right:{style:'thin',color:{argb:'FFDBEAFE'}} };
                        if (colNumber === 2) { cell.font = { name: 'Calibri', size: 10, bold: true, color: { argb: 'FF0097D8' } }; }
                        else if (colNumber === 3) { cell.font = { name: 'Calibri', size: 10, bold: true, color: { argb: 'FF005080' } }; }
                        if (colNumber === COL_FOTO1 && r.foto_1) {
                            cell.value = { text: 'Ver Foto 1', hyperlink: r.foto_1 };
                            cell.font = { name: 'Calibri', size: 9, color: { argb: 'FF2563EB' }, underline: true, bold: true };
                            cell.alignment = { vertical: 'middle', horizontal: 'center', wrapText: true };
                        }
                        if (colNumber === COL_FOTO2 && r.foto_2) {
                            cell.value = { text: 'Ver Foto 2', hyperlink: r.foto_2 };
                            cell.font = { name: 'Calibri', size: 9, color: { argb: 'FF2563EB' }, underline: true, bold: true };
                            cell.alignment = { vertical: 'middle', horizontal: 'center', wrapText: true };
                        }
                    });
                    var imgPair = allImgs[i] || [null, null];
                    function addImgCell(dataUrl, colNum) {
                        if (!dataUrl) return;
                        try {
                            var base64 = dataUrl.split(',')[1]; if (!base64) return;
                            var mime = dataUrl.substring(5, dataUrl.indexOf(';'));
                            var ext  = mime.split('/')[1];
                            if (ext === 'jpg') ext = 'jpeg';
                            if (ext !== 'jpeg' && ext !== 'png') ext = 'jpeg';
                            var imgId = wb.addImage({ base64: base64, extension: ext });
                            ws.addImage(imgId, {
                                tl: { col: colNum - 1, row: rowNum - 1 },
                                ext: { width: 200, height: 130 },
                                editAs: 'oneCell'
                            });
                        } catch(e) { console.warn('[Excel] imagen incrustada fila ' + rowNum + ': ' + e.message); }
                    }
                    addImgCell(imgPair[0], COL_FOTO1);
                    addImgCell(imgPair[1], COL_FOTO2);
                });
                var totRowNum = regs.length + 2;
                ws.addRow({});
                ws.getRow(totRowNum).height = 26;
                ws.getCell(totRowNum, 1).value = 'TOTALES';
                ws.getCell(totRowNum, 2).value = tramosUnicos + ' tramos';
                ws.getCell(totRowNum, 3).value = registros.length + ' registros';
                ws.getRow(totRowNum).eachCell({ includeEmpty: true }, function(cell) {
                    cell.fill      = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF0F172A' } };
                    cell.font      = { bold: true, color: { argb: 'FFFFFFFF' }, name: 'Calibri', size: 10 };
                    cell.alignment = { vertical: 'middle', horizontal: 'center' };
                    cell.border    = { top:{style:'medium',color:{argb:'FF0097D8'}}, left:{style:'thin',color:{argb:'FF263346'}}, bottom:{style:'medium',color:{argb:'FF0F172A'}}, right:{style:'thin',color:{argb:'FF263346'}} };
                });
                ws.getCell(totRowNum, 1).alignment = { vertical: 'middle', horizontal: 'left', indent: 1 };
                ws.autoFilter = { from: { row: 1, column: 1 }, to: { row: 1, column: 20 } };

                wb.xlsx.writeBuffer().then(function(buffer) {
                    var blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                    window.downloadBlobRuteo(blob, 'Reporte_Ruteo_' + fechaCorta.replace(/\//g, '-') + '.xlsx');
                    if (btnXl) { btnXl.disabled = false; var ts3 = btnXl.querySelector('.btn-xl-text'); if (ts3) ts3.textContent = 'Descargar Excel'; }
                }).catch(function(e) {
                    console.error('[Excel] Error buffer:', e); alert('Error generando Excel: ' + e.message);
                    if (btnXl) { btnXl.disabled = false; var ts4 = btnXl.querySelector('.btn-xl-text'); if (ts4) ts4.textContent = 'Descargar Excel'; }
                });
            } catch(err) {
                console.error('[Excel] Error interno:', err);
                alert('Error al generar Excel: ' + err.message);
                if (btnXl) { btnXl.disabled = false; var ts5 = btnXl.querySelector('.btn-xl-text'); if (ts5) ts5.textContent = 'Descargar Excel'; }
            }
        }).catch(function(e) {
            console.error('[Excel] Error imagenes:', e);
            alert('Error al procesar imagenes para Excel: ' + e.message);
            if (btnXl) { btnXl.disabled = false; var ts6 = btnXl.querySelector('.btn-xl-text'); if (ts6) ts6.textContent = 'Descargar Excel'; }
        });
    }


// ============================================================
// PDF INDIVIDUAL - con portada de datos del responsable
// ============================================================
window.generarDocumentoPDF = function(idx) {
    if (!window.jspdf) { alert('Libreria PDF cargando, intenta de nuevo.'); return; }
    var raw = window._ruteoRegistros[idx];
    if (!raw) return;
    var r = window.normalizarRegistroRuteo ? window.normalizarRegistroRuteo(raw) : raw;

    var usuario = (window.wpRuteoAjax && window.wpRuteoAjax.user) ? window.wpRuteoAjax.user : {};
    var responsable = usuario.displayName || usuario.username || 'Operario';
    var rolLabel    = (usuario.role === 'admin') ? 'Administrador' : 'Operario de Campo';

    var f1 = r.foto_1;
    var f2 = r.foto_2;

    Promise.all([
        window.fetchImageDataUrl(f1),
        window.fetchImageDataUrl(f2)
    ]).then(function(imgs) {
        var doc = new window.jspdf.jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
        var pageW = doc.internal.pageSize.getWidth();
        var pageH = doc.internal.pageSize.getHeight();
        var fechaGen = new Date().toLocaleDateString('es-PE', { day: '2-digit', month: 'long', year: 'numeric' });
        var fechaCorta = new Date().toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric' });

        // =====================================================
        // PAGINA 1 - PORTADA CON DATOS DEL RESPONSABLE
        // =====================================================

        // Fondo superior azul
        doc.setFillColor(0, 151, 216);
        doc.rect(0, 0, pageW, 80, 'F');

        // Franja oscura superior
        doc.setFillColor(0, 90, 140);
        doc.rect(0, 0, pageW, 12, 'F');

        // Linea decorativa izquierda
        doc.setFillColor(131, 202, 22);
        doc.rect(0, 12, 5, 68, 'F');

        // Texto en franja superior
        doc.setTextColor(200, 235, 255);
        doc.setFontSize(7.5);
        doc.setFont('helvetica', 'normal');
        doc.text('APLICATIVO DE RUTEO DE CAMPO  -  FICHA TECNICA', pageW / 2, 8, { align: 'center' });

        // Titulo principal
        doc.setTextColor(255, 255, 255);
        doc.setFontSize(22);
        doc.setFont('helvetica', 'bold');
        doc.text('FICHA TECNICA DE', 14, 35);
        doc.text('RUTEO EN CAMPO', 14, 46);

        // Subtitulo
        doc.setFontSize(10);
        doc.setFont('helvetica', 'normal');
        doc.setTextColor(200, 235, 255);
        doc.text('Inspeccion y captura de datos estructurales en campo', 14, 56);

        // ID del registro en esquina
        doc.setFontSize(28);
        doc.setFont('helvetica', 'bold');
        doc.setTextColor(255, 255, 255);
        doc.text(r.id_consol || '-', pageW - 14, 50, { align: 'right' });
        doc.setFontSize(8);
        doc.setFont('helvetica', 'normal');
        doc.setTextColor(200, 235, 255);
        doc.text('ID CONSOL', pageW - 14, 56, { align: 'right' });

        // Fondo blanco del cuerpo
        doc.setFillColor(248, 250, 252);
        doc.rect(0, 80, pageW, pageH - 80, 'F');

        // === BLOQUE DE DATOS DEL RESPONSABLE ===
        var y = 92;

        // Tarjeta izquierda - Responsable
        doc.setFillColor(255, 255, 255);
        doc.setDrawColor(226, 232, 240);
        doc.roundedRect(14, y - 6, 84, 36, 2, 2, 'FD');

        doc.setFontSize(7);
        doc.setFont('helvetica', 'bold');
        doc.setTextColor(0, 151, 216);
        doc.text('RESPONSABLE DEL REGISTRO', 22, y);

        doc.setFontSize(12);
        doc.setFont('helvetica', 'bold');
        doc.setTextColor(15, 23, 42);
        doc.text(responsable, 22, y + 9);

        doc.setFontSize(8);
        doc.setFont('helvetica', 'normal');
        doc.setTextColor(100, 116, 139);
        doc.text(rolLabel, 22, y + 16);

        doc.setFontSize(8);
        doc.setTextColor(60, 80, 100);
        doc.text('Usuario: ' + (usuario.username || '-'), 22, y + 23);

        // Tarjeta derecha - Datos del tramo
        doc.setFillColor(255, 255, 255);
        doc.roundedRect(104, y - 6, 92, 36, 2, 2, 'FD');

        doc.setFontSize(7);
        doc.setFont('helvetica', 'bold');
        doc.setTextColor(0, 151, 216);
        doc.text('DATOS DE REGISTRO', 112, y);

        doc.setFontSize(10);
        doc.setFont('helvetica', 'bold');
        doc.setTextColor(15, 23, 42);
        doc.text('Tramo: ' + (r.tramo || '-'), 112, y + 9);

        doc.setFontSize(8);
        doc.setFont('helvetica', 'normal');
        doc.setTextColor(60, 80, 100);
        doc.text('Fecha generacion: ' + fechaGen, 112, y + 17);
        doc.text('Estructura: ' + (r.estructura || '-'), 112, y + 24);

        y += 42;

        // === SEPARADOR ===
        doc.setDrawColor(226, 232, 240);
        doc.setLineWidth(0.5);
        doc.line(14, y, pageW - 14, y);
        y += 8;

        // === TABLA RESUMEN EN PORTADA ===
        doc.setFontSize(9);
        doc.setFont('helvetica', 'bold');
        doc.setTextColor(0, 151, 216);
        doc.text('Resumen del Registro', 14, y);
        y += 5;

        var resumenData = [
            ['Tipo de Estructura', r.tipo_estructura || '-', 'Altura', (r.altura || '-') + ' m'],
            ['Codigo', r.codigo || '-', 'Ubicacion', r.ubicacion || '-'],
            ['Mufa', r.mufa || '0', 'Herraje Retencion', r.retencion || '0'],
            ['Herraje Suspension', r.suspension || '0', 'Cruceta', r.cruceta || '0'],
            ['Hebillas', r.hebillas || '0', 'Fleje de Acero', r.fleje || '0'],
            ['Amortiguador', r.amortiguador || '0', 'Brazo Extensor', r.brazo_extensor || '0'],
            ['Kit de Retenida', r.kit_retenida || '0', 'Observacion', r.observacion || '-'],
        ];

        doc.autoTable({
            startY: y,
            body: resumenData,
            theme: 'grid',
            styles: {
                fontSize: 8,
                cellPadding: { top: 3, bottom: 3, left: 4, right: 4 },
                textColor: [30, 30, 30]
            },
            columnStyles: {
                0: { fontStyle: 'bold', fillColor: [239, 246, 255], cellWidth: 48, textColor: [0, 80, 130] },
                1: { cellWidth: 45 },
                2: { fontStyle: 'bold', fillColor: [239, 246, 255], cellWidth: 48, textColor: [0, 80, 130] },
                3: { cellWidth: 'auto' }
            },
            margin: { left: 14, right: 14 }
        });

        // Pie de pagina 1
        doc.setFontSize(7);
        doc.setTextColor(165, 172, 184);
        doc.setDrawColor(226, 232, 240);
        doc.line(14, pageH - 10, pageW - 14, pageH - 10);
        doc.text('Aplicativo de Ruteo de Campo  -  ' + fechaCorta, 14, pageH - 5);
        doc.text('Pagina 1 de 2', pageW - 14, pageH - 5, { align: 'right' });

        // =====================================================
        // PAGINA 2 - FOTOS + TABLA DETALLADA
        // =====================================================
        doc.addPage();
        doc.setFillColor(248, 250, 252);
        doc.rect(0, 0, pageW, pageH, 'F');

        // Cabecera pagina 2
        doc.setFillColor(0, 151, 216);
        doc.rect(0, 0, pageW, 14, 'F');
        doc.setFillColor(131, 202, 22);
        doc.rect(0, 0, 5, 14, 'F');
        doc.setTextColor(255, 255, 255);
        doc.setFontSize(8);
        doc.setFont('helvetica', 'bold');
        doc.text('EVIDENCIA FOTOGRAFICA  -  ID: ' + (r.id_consol || '-') + '  -  Tramo: ' + (r.tramo || '-'), 14, 9.5);

        var y2 = 20;
        var imgW = 84;
        var imgH = 96;

        // Marco foto 1
        doc.setFillColor(255, 255, 255);
        doc.setDrawColor(226, 232, 240);
        doc.roundedRect(12, y2 - 4, imgW + 4, imgH + 16, 2, 2, 'FD');
        doc.setFontSize(7.5);
        doc.setFont('helvetica', 'bold');
        doc.setTextColor(0, 151, 216);
        doc.text('FOTOGRAFIA 1', 14 + imgW / 2, y2 + 2, { align: 'center' });

        function getImgType(dataUrl) {
            if (!dataUrl || typeof dataUrl !== 'string') return 'JPEG';
            if (dataUrl.indexOf('data:image/') === 0) {
                var parts = dataUrl.substring(11).split(';');
                var mimeSub = parts[0] ? parts[0].toUpperCase() : 'JPEG';
                return (mimeSub === 'JPG') ? 'JPEG' : mimeSub;
            }
            return 'JPEG';
        }

        if (imgs[0]) {
            var t1 = getImgType(imgs[0]);
            try { doc.addImage(imgs[0], t1, 14, y2 + 5, imgW, imgH); } catch (e) {
                doc.setFillColor(226, 232, 240); doc.rect(14, y2 + 5, imgW, imgH, 'F');
                doc.setTextColor(100); doc.setFontSize(9); doc.text('Imagen no disponible', 14 + imgW/2, y2 + 5 + imgH/2, { align: 'center' });
            }
        } else {
            doc.setFillColor(226, 232, 240); doc.rect(14, y2 + 5, imgW, imgH, 'F');
            doc.setTextColor(100); doc.setFontSize(9); doc.text('Sin foto', 14 + imgW/2, y2 + 5 + imgH/2, { align: 'center' });
        }
        doc.setFontSize(7); doc.setFont('helvetica', 'normal'); doc.setTextColor(100, 116, 139);
        doc.text('Vista de la estructura en campo', 14 + imgW / 2, y2 + 5 + imgH + 6, { align: 'center' });

        // Marco foto 2
        var x2 = pageW / 2 + 2;
        doc.setFillColor(255, 255, 255);
        doc.roundedRect(x2 - 2, y2 - 4, imgW + 4, imgH + 16, 2, 2, 'FD');
        doc.setFontSize(7.5);
        doc.setFont('helvetica', 'bold');
        doc.setTextColor(0, 151, 216);
        doc.text('FOTOGRAFIA 2', x2 + imgW / 2, y2 + 2, { align: 'center' });

        if (imgs[1]) {
            var t2 = getImgType(imgs[1]);
            try { doc.addImage(imgs[1], t2, x2, y2 + 5, imgW, imgH); } catch (e) {
                doc.setFillColor(226, 232, 240); doc.rect(x2, y2 + 5, imgW, imgH, 'F');
                doc.setTextColor(100); doc.setFontSize(9); doc.text('Imagen no disponible', x2 + imgW/2, y2 + 5 + imgH/2, { align: 'center' });
            }
        } else {
            doc.setFillColor(226, 232, 240); doc.rect(x2, y2 + 5, imgW, imgH, 'F');
            doc.setTextColor(100); doc.setFontSize(9); doc.text('Sin foto', x2 + imgW/2, y2 + 5 + imgH/2, { align: 'center' });
        }
        doc.setFontSize(7); doc.setFont('helvetica', 'normal'); doc.setTextColor(100, 116, 139);
        doc.text('Vista complementaria', x2 + imgW / 2, y2 + 5 + imgH + 6, { align: 'center' });

        var y3 = y2 + imgH + 22;

        // Subtitulo tabla
        doc.setFontSize(9);
        doc.setFont('helvetica', 'bold');
        doc.setTextColor(0, 151, 216);
        doc.text('Detalle Completo del Registro', 14, y3);

        var tableData = [
            ['Tramo', r.tramo || '-'],
            ['ID Consol', r.id_consol || '-'],
            ['Estructura', r.estructura || '-'],
            ['Tipo de Estructura', r.tipo_estructura || '-'],
            ['Altura de Estructura', (r.altura || '-') + ' m'],
            ['Ubicacion / Coordenadas', r.ubicacion || '-'],
            ['Codigo Estructura', r.codigo || '-'],
            ['Mufa', r.mufa || '0'],
            ['Herraje de Retencion', r.retencion || '0'],
            ['Herraje de Suspension', r.suspension || '0'],
            ['Cruceta', r.cruceta || '0'],
            ['Hebillas', r.hebillas || '0'],
            ['Fleje de Acero', r.fleje || '0'],
            ['Amortiguador', r.amortiguador || '0'],
            ['Brazo Extensor', r.brazo_extensor || '0'],
            ['Kit de Retenida', r.kit_retenida || '0'],
            ['Observacion', r.observacion || '-']
        ];

        doc.autoTable({
            startY: y3 + 4,
            body: tableData,
            theme: 'grid',
            styles: {
                fontSize: 8.5,
                cellPadding: { top: 3, bottom: 3, left: 5, right: 5 },
                textColor: [30, 30, 30]
            },
            columnStyles: {
                0: { fontStyle: 'bold', fillColor: [239, 246, 255], cellWidth: 68, textColor: [0, 80, 130] },
                1: { cellWidth: 'auto' }
            },
            margin: { left: 14, right: 14 },
            alternateRowStyles: { fillColor: [248, 250, 252] }
        });

        // Pie de pagina 2
        doc.setFontSize(7);
        doc.setTextColor(165, 172, 184);
        doc.setDrawColor(226, 232, 240);
        doc.line(14, pageH - 10, pageW - 14, pageH - 10);
        doc.text('Aplicativo de Ruteo de Campo  -  ' + fechaCorta + '  -  Responsable: ' + responsable, 14, pageH - 5);
        doc.text('Pagina 2 de 2', pageW - 14, pageH - 5, { align: 'right' });

        doc.save('Registro_' + (r.id_consol || 'Ruteo') + '_' + fechaCorta.replace(/\//g, '-') + '.pdf');

    }).catch(function(e) {
        console.error(e);
        alert('Error generando PDF');
    });
};

// ============================================================
// Helper base64 para Word
// ============================================================
function dataUrlToUint8Array(dataUrl) {
    if (!dataUrl) return null;
    var arr = dataUrl.split(',');
    if (arr.length < 2) return null;
    var bstr = atob(arr[1]), n = bstr.length, u8arr = new Uint8Array(n);
    while (n--) { u8arr[n] = bstr.charCodeAt(n); }
    return u8arr;
}

// ============================================================
// Google Docs / Drive Integration Completed
// ============================================================
</script>
