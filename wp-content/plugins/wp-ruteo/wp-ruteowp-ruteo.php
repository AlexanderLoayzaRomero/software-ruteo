<?php
/**
 * Plugin Name: Aplicacion de Ruteo
 * Description: Plugin para recopilar datos y fotos en campo, conectandose con Google Sheets.
 * Version: 1.0
 * Author: Antigravity
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class WPRuteoApp {

    // URL DEL WEBHOOK DE GOOGLE SHEETS (Debes reemplazar esto con la URL que te de el script)
    private $webhook_url = 'https://script.google.com/a/macros/tecsup.edu.pe/s/AKfycbzsrT56wkT9yAUDqaE_e08_SvCiLIFrLRb-PizK36ZQtSS1S8sjWa5bMTvYqtPvULh0Pw/exec';

    public function __construct() {
        add_shortcode( 'formulario_ruteo', array( $this, 'render_form' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_ajax_ruteo_submit', array( $this, 'handle_ajax_submit' ) );
        add_action( 'wp_ajax_nopriv_ruteo_submit', array( $this, 'handle_ajax_submit' ) );
    }

    public function enqueue_assets() {
        global $post;
        if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'formulario_ruteo' ) ) {
            wp_enqueue_style( 'wp-ruteo-style', plugin_dir_url( __FILE__ ) . 'assets/css/style.css', array(), '1.0.0' );
            wp_enqueue_script( 'wp-ruteo-app', plugin_dir_url( __FILE__ ) . 'assets/js/app.js', array( 'jquery' ), '1.0.0', true );
            wp_localize_script( 'wp-ruteo-app', 'wpRuteoAjax', array(
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'ruteo_submit_nonce' )
            ) );
        }
    }

    public function render_form() {
        ob_start();
        include plugin_dir_path( __FILE__ ) . 'includes/form-template.php';
        return ob_get_clean();
    }

    public function handle_ajax_submit() {
        check_ajax_referer( 'ruteo_submit_nonce', 'nonce' );

        $data = array();
        // Recopilar campos de texto y selects
        $fields = array(
            'tramo', 'id_consol', 'estructura', 'tipo_estructura', 'altura_estructura',
            'ubicacion', 'codigo', 'mufa', 'retencion', 'suspension', 'cruceta',
            'hebillas', 'fleje', 'amortiguador', 'brazo_extensor', 'kit_retenida', 'observacion'
        );

        foreach ( $fields as $field ) {
            $data[$field] = isset( $_POST[$field] ) ? sanitize_text_field( wp_unslash( $_POST[$field] ) ) : '';
        }

        // Subir fotos
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/media.php' );

        $photo_urls = array();
        foreach ( array( 'foto1', 'foto2' ) as $file_key ) {
            if ( ! empty( $_FILES[$file_key]['name'] ) ) {
                $attachment_id = media_handle_upload( $file_key, 0 );
                if ( is_wp_error( $attachment_id ) ) {
                    wp_send_json_error( 'Error al subir la imagen: ' . $attachment_id->get_error_message() );
                } else {
                    $photo_urls[] = wp_get_attachment_url( $attachment_id );
                }
            } else {
                $photo_urls[] = '';
            }
        }

        $data['foto1_url'] = $photo_urls[0];
        $data['foto2_url'] = $photo_urls[1];

        // Enviar a Google Sheets
        if ( $this->webhook_url !== 'URL_DE_TU_WEBHOOK_AQUI' ) {
            $response = wp_remote_post( $this->webhook_url, array(
                'body'    => json_encode( $data ),
                'headers' => array( 'Content-Type' => 'application/json' ),
                'timeout' => 15
            ) );

            if ( is_wp_error( $response ) ) {
                wp_send_json_error( 'Error al conectar con Google Sheets.' );
            }
        }

        wp_send_json_success( 'Datos enviados correctamente.' );
    }
}

new WPRuteoApp();
