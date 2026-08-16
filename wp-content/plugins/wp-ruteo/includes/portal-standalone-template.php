<?php
/**
 * Template Name: Portal Standalone Template
 * Description: Carga el Portal de Ruteo O&M a pantalla completa en la portada del sitio evitando el tema por defecto de WordPress.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Cargar scripts y estilos obligatorios de Ruteo
Wp_Ruteo::get_instance()->enqueue_assets();

?><!DOCTYPE html>
<html <?php language_attributes(); ?> data-theme="light">
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo esc_html( get_bloginfo( 'name' ) ); ?> | Portal de Operaciones y Mantenimiento</title>
    <?php wp_head(); ?>
    <style>
        html, body {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            min-height: 100vh !important;
            background-color: var(--bg-main, #f8fafc) !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif;
            overflow-x: hidden;
        }
        /* Ocultar elementos sobrantes del tema de WordPress si existieran */
        #page, .site-header, .site-footer, header:not(.ruteo-top-header), footer:not(.ruteo-footer), .entry-header, .entry-content > h1 {
            display: none !important;
        }
        .ruteo-standalone-wrapper {
            width: 100%;
            min-height: 100vh;
            display: block;
        }
    </style>
</head>
<body <?php body_class( 'ruteo-portal-standalone' ); ?>>

<div class="ruteo-standalone-wrapper">
    <?php echo Wp_Ruteo::get_instance()->render_portal(); ?>
</div>

<?php wp_footer(); ?>
</body>
</html>
