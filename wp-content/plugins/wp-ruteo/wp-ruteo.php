<?php
/**
 * Plugin Name: Software O&M
 * Description: Software O&M - Plugin para recopilar datos y fotos en campo, consumo de materiales y gestion de usuarios.
 * Version: 2.0.0
 * Author: Antigravity
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class WPRuteoApp {

    // URL DEL WEBHOOK DE GOOGLE SHEETS
    public $webhook_url = 'https://script.google.com/macros/s/AKfycbwOkeyflnS0fHr2mtmuo8HPKMfeSda6Yjmq7unarGIQ_sExZ0Mdl1BS2mDYZNAf4NcwOA/exec';
    private $assets_enqueued = false;

    public function __construct() {
        $this->register_roles();
        add_shortcode( 'formulario_ruteo', array( $this, 'render_form' ) );
        add_shortcode( 'portal_ruteo', array( $this, 'render_portal' ) );
        add_shortcode( 'login_ruteo', array( $this, 'render_login' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue_assets' ) );
        add_action( 'wp_ajax_ruteo_submit', array( $this, 'handle_ajax_submit' ) );
        add_action( 'wp_ajax_nopriv_ruteo_submit', array( $this, 'handle_ajax_submit' ) );
        add_action( 'wp_ajax_ruteo_update_registro', array( $this, 'handle_ajax_update_registro' ) );
        add_action( 'wp_ajax_nopriv_ruteo_update_registro', array( $this, 'handle_ajax_update_registro' ) );
        // Proxy GET: evita CORS del browser al llamar directamente a Google
        add_action( 'wp_ajax_ruteo_get_registros', array( $this, 'handle_ajax_get_registros' ) );
        add_action( 'wp_ajax_nopriv_ruteo_get_registros', array( $this, 'handle_ajax_get_registros' ) );
        // Proxy de imagen: devuelve imagen externa como base64 para PDF
        add_action( 'wp_ajax_ruteo_proxy_image', array( $this, 'handle_proxy_image' ) );
        add_action( 'wp_ajax_nopriv_ruteo_proxy_image', array( $this, 'handle_proxy_image' ) );
        // Proxy para subir documentos a Drive
        add_action( 'wp_ajax_ruteo_upload_document', array( $this, 'handle_upload_document' ) );
        add_action( 'wp_ajax_nopriv_ruteo_upload_document', array( $this, 'handle_upload_document' ) );

        // Auth & User Management AJAX Endpoints
        add_action( 'wp_ajax_ruteo_login', array( $this, 'handle_ajax_login' ) );
        add_action( 'wp_ajax_nopriv_ruteo_login', array( $this, 'handle_ajax_login' ) );
        add_action( 'wp_ajax_ruteo_logout', array( $this, 'handle_ajax_logout' ) );
        add_action( 'wp_ajax_nopriv_ruteo_logout', array( $this, 'handle_ajax_logout' ) );
        add_action( 'wp_ajax_ruteo_get_users', array( $this, 'handle_ajax_get_users' ) );
        add_action( 'wp_ajax_ruteo_create_user', array( $this, 'handle_ajax_create_user' ) );
        add_action( 'wp_ajax_ruteo_delete_user', array( $this, 'handle_ajax_delete_user' ) );
        add_action( 'wp_ajax_ruteo_update_profile', array( $this, 'handle_ajax_update_profile' ) );
        add_action( 'wp_ajax_ruteo_negativa_guardar', array( $this, 'handle_ajax_negativa_guardar' ) );
        add_action( 'wp_ajax_nopriv_ruteo_negativa_guardar', array( $this, 'handle_ajax_negativa_guardar' ) );
        add_action( 'wp_ajax_ruteo_negativa_listar', array( $this, 'handle_ajax_negativa_listar' ) );
        add_action( 'wp_ajax_nopriv_ruteo_negativa_listar', array( $this, 'handle_ajax_negativa_listar' ) );
        add_action( 'wp_ajax_ruteo_sync_negativas_sheets', array( $this, 'handle_ajax_sync_negativas_sheets' ) );
        add_action( 'wp_ajax_nopriv_ruteo_sync_negativas_sheets', array( $this, 'handle_ajax_sync_negativas_sheets' ) );
        
        // Logo del sistema (solo Admin)
        add_action( 'wp_ajax_ruteo_update_site_logo', array( $this, 'handle_ajax_update_site_logo' ) );

        // Consumo de Materiales AJAX Endpoints
        add_action( 'wp_ajax_ruteo_save_materiales', array( $this, 'handle_ajax_save_materiales' ) );
        add_action( 'wp_ajax_nopriv_ruteo_save_materiales', array( $this, 'handle_ajax_save_materiales' ) );
        add_action( 'wp_ajax_ruteo_get_materiales', array( $this, 'handle_ajax_get_materiales' ) );
        add_action( 'wp_ajax_nopriv_ruteo_get_materiales', array( $this, 'handle_ajax_get_materiales' ) );
        add_action( 'wp_ajax_ruteo_update_material', array( $this, 'handle_ajax_update_material' ) );
        add_action( 'wp_ajax_nopriv_ruteo_update_material', array( $this, 'handle_ajax_update_material' ) );

        add_action( 'plugins_loaded', array( $this, 'maybe_upgrade_negativas_table' ) );

        // Clientes AJAX Endpoints
        add_action( 'wp_ajax_ruteo_get_clientes', array( $this, 'handle_ajax_get_clientes' ) );
        add_action( 'wp_ajax_nopriv_ruteo_get_clientes', array( $this, 'handle_ajax_get_clientes' ) );
        add_action( 'wp_ajax_ruteo_save_cliente', array( $this, 'handle_ajax_save_cliente' ) );
        add_action( 'wp_ajax_ruteo_delete_cliente', array( $this, 'handle_ajax_delete_cliente' ) );

        // Audit Logs AJAX Endpoints
        add_action( 'wp_ajax_ruteo_get_logs', array( $this, 'handle_ajax_get_logs' ) );
        add_action( 'wp_ajax_nopriv_ruteo_get_logs', array( $this, 'handle_ajax_get_logs' ) );

        // Image Base64 Proxy Endpoint
        add_action( 'wp_ajax_ruteo_get_image_base64', array( $this, 'handle_ajax_get_image_base64' ) );
        

        
    }

    public function register_roles() {
        add_role( 'ruteo_admin', 'Administrador O&M', array(
            'read'                  => true,
            'ruteo_admin_access'    => true,
            'ruteo_worker_access'   => true,
        ) );
        add_role( 'ruteo_sup_operativo', 'Supervisor Operativo O&M', array(
            'read'                       => true,
            'ruteo_worker_access'        => true,
            'ruteo_sup_operativo_access' => true,
        ) );
        add_role( 'ruteo_sup_hse', 'Supervisor HSE O&M', array(
            'read'                  => true,
            'ruteo_worker_access'   => true,
            'ruteo_sup_hse_access'  => true,
        ) );
        add_role( 'ruteo_worker', 'Operario O&M', array(
            'read'                  => true,
            'ruteo_worker_access'   => true,
        ) );

        // Si los roles ya existian en la base de datos (instalacion previa), add_role() no
        // actualiza el nombre visible. Forzamos la actualizacion del label aqui.
        global $wp_roles;
        if ( ! isset( $wp_roles ) ) {
            $wp_roles = new WP_Roles();
        }
        $nombres_roles = array(
            'ruteo_admin'         => 'Administrador O&M',
            'ruteo_sup_operativo' => 'Supervisor Operativo O&M',
            'ruteo_sup_hse'       => 'Supervisor HSE O&M',
            'ruteo_worker'        => 'Operario O&M',
        );
        foreach ( $nombres_roles as $rol_id => $nombre ) {
            if ( isset( $wp_roles->roles[ $rol_id ] ) && $wp_roles->roles[ $rol_id ]['name'] !== $nombre ) {
                $wp_roles->roles[ $rol_id ]['name'] = $nombre;
                $wp_roles->role_names[ $rol_id ]    = $nombre;
                update_option( $wp_roles->role_key, $wp_roles->roles );
            }
        }
    }

    public static function activar_cuentas_prueba() {
        $cuentas = array(
            array(
                'user'         => 'admingeneral',
                'pass'         => defined( 'RUTEO_PASS_ADMIN' ) ? RUTEO_PASS_ADMIN : wp_generate_password( 16 ),
                'name'         => 'Administrador General O&M',
                'email'        => 'admin@software-om.org.pe',
                'role'         => 'ruteo_admin',
                'negativa_rol' => 'admin_general',
                'position'     => 'Administrador General O&M',
                'signer_caps'  => array( 'firmante_ejecutor', 'firmante_operativo', 'firmante_hse' ),
            ),
            array(
                'user'         => 'tecnico1',
                'pass'         => defined( 'RUTEO_PASS_TECNICO' ) ? RUTEO_PASS_TECNICO : wp_generate_password( 16 ),
                'name'         => 'Juan Perez (Tecnico)',
                'email'        => 'tecnico1@ruteo.org.pe',
                'role'         => 'ruteo_worker',
                'negativa_rol' => 'tecnico',
                'position'     => 'Tecnico de Campo O&M',
                'signer_caps'  => array( 'firmante_ejecutor' ),
            ),
            array(
                'user'         => 'supervisor1',
                'pass'         => defined( 'RUTEO_PASS_SUPERVISOR' ) ? RUTEO_PASS_SUPERVISOR : wp_generate_password( 16 ),
                'name'         => 'Carlos Mendoza (Supervisor Op.)',
                'email'        => 'supervisor1@ruteo.org.pe',
                'role'         => 'ruteo_sup_operativo',
                'negativa_rol' => 'supervisor_operativo',
                'position'     => 'Supervisor Operativo de Campo',
                'signer_caps'  => array( 'firmante_operativo' ),
            ),
            array(
                'user'         => 'seguridad1',
                'pass'         => defined( 'RUTEO_PASS_SEGURIDAD' ) ? RUTEO_PASS_SEGURIDAD : wp_generate_password( 16 ),
                'name'         => 'Roberto Silva (Supervisor Seg.)',
                'email'        => 'seguridad1@ruteo.org.pe',
                'role'         => 'ruteo_sup_hse',
                'negativa_rol' => 'supervisor_seguridad',
                'position'     => 'Supervisor de Seguridad SST',
                'signer_caps'  => array( 'firmante_hse' ),
            ),
            array(
                'user'         => 'hse1',
                'pass'         => defined( 'RUTEO_PASS_HSE' ) ? RUTEO_PASS_HSE : wp_generate_password( 16 ),
                'name'         => 'Maria Fernandez (Area HSE)',
                'email'        => 'hse1@ruteo.org.pe',
                'role'         => 'ruteo_sup_hse',
                'negativa_rol' => 'hse',
                'position'     => 'Lider de Area HSE',
                'signer_caps'  => array( 'firmante_hse' ),
            ),
        );

        foreach ( $cuentas as $c ) {
            $user_id = username_exists( $c['user'] );
            if ( ! $user_id ) {
                $user_id = wp_create_user( $c['user'], $c['pass'], $c['email'] );
                if ( ! is_wp_error( $user_id ) ) {
                    $u = new WP_User( $user_id );
                    $u->set_role( isset( $c['role'] ) ? $c['role'] : 'ruteo_worker' );
                    wp_update_user( array( 'ID' => $user_id, 'display_name' => $c['name'] ) );
                }
            } else {
                $u = new WP_User( $user_id );
                if ( isset( $c['role'] ) ) {
                    $u->set_role( $c['role'] );
                }
            }
            if ( $user_id && ! is_wp_error( $user_id ) ) {
                update_user_meta( $user_id, 'ruteo_negativa_rol', $c['negativa_rol'] );
                if ( isset( $c['position'] ) ) {
                    update_user_meta( $user_id, 'ruteo_position', $c['position'] );
                }
                if ( isset( $c['signer_caps'] ) ) {
                    update_user_meta( $user_id, 'ruteo_signer_caps', $c['signer_caps'] );
                }
            }
        }
    }

    public function enqueue_assets() {
        if ( $this->assets_enqueued ) {
            return;
        }
        $this->assets_enqueued = true;
        $css_ver = filemtime( plugin_dir_path( __FILE__ ) . 'assets/css/style.css' );
        $js_ver  = filemtime( plugin_dir_path( __FILE__ ) . 'assets/js/app.js' );
        wp_enqueue_style( 'wp-ruteo-style', plugin_dir_url( __FILE__ ) . 'assets/css/style.css', array(), $css_ver );
        wp_enqueue_script( 'jspdf-cdn', 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js', array(), '2.5.1', true );
        wp_enqueue_script( 'jspdf-autotable-cdn', 'https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js', array( 'jspdf-cdn' ), '3.5.28', true );
        wp_enqueue_script( 'xlsx-cdn', 'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js', array(), '0.18.5', true );
        wp_enqueue_script( 'wp-ruteo-app', plugin_dir_url( __FILE__ ) . 'assets/js/app.js', array( 'jquery', 'jspdf-cdn', 'jspdf-autotable-cdn', 'xlsx-cdn' ), $js_ver, true );

        $current_user = wp_get_current_user();
        $is_logged_in = is_user_logged_in();
        $user_role    = 'guest';
        $is_admin     = false;

        $phone        = '';
        $pm_assigned   = '';
        $avatar       = '';
        $firma_img    = '';
        $position     = '';
        $signer_caps  = array();
        $negativa_rol = '';

        if ( $is_logged_in ) {
            $phone        = get_user_meta( $current_user->ID, 'ruteo_phone', true );
            $pm_assigned  = get_user_meta( $current_user->ID, 'ruteo_pm_assigned', true );
            $avatar       = get_user_meta( $current_user->ID, 'ruteo_avatar', true );
            $firma_img    = get_user_meta( $current_user->ID, 'ruteo_firma_img', true );
            $position     = get_user_meta( $current_user->ID, 'ruteo_position', true );
            $negativa_rol = get_user_meta( $current_user->ID, 'ruteo_negativa_rol', true );
            $signer_caps  = get_user_meta( $current_user->ID, 'ruteo_signer_caps', true );
            if ( ! is_array( $signer_caps ) ) {
                $signer_caps = array();
            }

            if ( in_array( 'administrator', (array) $current_user->roles, true ) || in_array( 'ruteo_admin', (array) $current_user->roles, true ) ) {
                $user_role = 'admin';
                $is_admin  = true;
            } elseif ( in_array( 'ruteo_sup_operativo', (array) $current_user->roles, true ) ) {
                $user_role = 'sup_operativo';
            } elseif ( in_array( 'ruteo_sup_hse', (array) $current_user->roles, true ) ) {
                $user_role = 'sup_hse';
            } elseif ( in_array( 'ruteo_worker', (array) $current_user->roles, true ) ) {
                $user_role = 'worker';
            } else {
                $user_role = 'user';
            }
        }

        $clientes_list = get_option( 'ruteo_clientes_list', array() );
        if ( empty( $clientes_list ) || ! is_array( $clientes_list ) ) {
            $clientes_list = array(
                array(
                    'id'     => 'CLI-CYMTEL',
                    'nombre' => 'CYMTEL',
                    'ruc'    => '20512345678',
                    'logo'   => '',
                )
            );
            update_option( 'ruteo_clientes_list', $clientes_list, false );
        }

        wp_localize_script( 'wp-ruteo-app', 'wpRuteoAjax', array(
            'ajaxurl'   => admin_url( 'admin-ajax.php' ),
            'nonce'     => wp_create_nonce( 'ruteo_submit_nonce' ),
            'siteLogo'  => get_option( 'ruteo_site_logo', '' ),
            'clientes'  => $clientes_list,
            'userCount' => count( get_users( array( 'fields' => 'ID' ) ) ),
            'user'    => array(
                'id'          => $is_logged_in ? $current_user->ID : 0,
                'isLoggedIn'  => $is_logged_in,
                'username'    => $is_logged_in ? $current_user->user_login : '',
                'isAdmin'     => $is_admin,
                'negativaRol' => $negativa_rol ?: '',
                'displayName' => $is_logged_in ? html_entity_decode( $current_user->display_name, ENT_QUOTES, 'UTF-8' ) : 'Invitado',
                'email'       => $is_logged_in ? $current_user->user_email : '',
                'phone'       => $phone,
                'pmAssigned'  => $pm_assigned,
                'avatar'      => $avatar,
                'firma'       => $firma_img,
                'role'        => $user_role,
                'position'    => $position ?: '',
                'signerCaps'  => $signer_caps,
            ),
        ) );
    }

    public function maybe_enqueue_assets() {
        if ( ! is_singular() ) {
            return;
        }
        global $post;
        if ( ! $post ) {
            return;
        }
        $shortcodes = array( 'formulario_ruteo', 'portal_ruteo', 'login_ruteo' );
        foreach ( $shortcodes as $sc ) {
            if ( has_shortcode( $post->post_content, $sc ) ) {
                $this->enqueue_assets();
                return;
            }
        }
    }

    public function render_form() {
        $this->enqueue_assets();
        ob_start();
        include plugin_dir_path( __FILE__ ) . 'includes/form-template.php';
        return ob_get_clean();
    }

    public function render_portal() {
        $this->enqueue_assets();
        global $wp_ruteo_webhook_url;
        $wp_ruteo_webhook_url = $this->webhook_url;
        ob_start();
        include plugin_dir_path( __FILE__ ) . 'includes/portal-template.php';
        return ob_get_clean();
    }

    public function render_login() {
        $this->enqueue_assets();
        ob_start();
        include plugin_dir_path( __FILE__ ) . 'includes/login-template.php';
        return ob_get_clean();
    }

    public function handle_ajax_get_registros() {
        check_ajax_referer( 'ruteo_submit_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array(
                'message' => 'Acceso denegado. Debes iniciar sesion para consultar registros.'
            ) );
            return;
        }

        set_time_limit( 45 );
        nocache_headers();
        header( 'Cache-Control: no-cache, no-store, must-revalidate, max-age=0' );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        $target_url = add_query_arg( '_ts', microtime( true ), $this->webhook_url );

        $response = wp_remote_get( $target_url, array(
            'timeout'     => 35,
            'redirection' => 5,
            'httpversion' => '1.1',
            'sslverify'   => false,
            'user-agent'  => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        ) );

        if ( ! is_wp_error( $response ) ) {
            $code = wp_remote_retrieve_response_code( $response );
            $body = wp_remote_retrieve_body( $response );
            if ( $code === 200 && ! empty( $body ) ) {
                $json = json_decode( $body, true );
                if ( json_last_error() === JSON_ERROR_NONE ) {
                    update_option( 'ruteo_cache_registros', $json, false );
                    wp_send_json_success( $json );
                    return;
                }
            }
        }

        // Fallback a registros cacheados si falla la conexion con Google Sheets
        $cached = get_option( 'ruteo_cache_registros' );
        if ( ! empty( $cached ) && is_array( $cached ) ) {
            wp_send_json_success( $cached );
            return;
        }

        wp_send_json_error( array( 'message' => 'No se pudo conectar con Google Sheets. Reintente en unos segundos.' ) );
    }

    public function handle_proxy_image() {
        check_ajax_referer( 'ruteo_submit_nonce', 'nonce' );

        $raw = isset( $_GET['url'] ) ? $_GET['url'] : '';
        if ( empty( $raw ) ) {
            wp_send_json_error( array( 'message' => 'URL requerida' ) );
            return;
        }

        $url = esc_url_raw( $raw );
        if ( empty( $url ) ) {
            wp_send_json_error( array( 'message' => 'URL invalida' ) );
            return;
        }

        $upload_dir = wp_upload_dir();
        $base_url   = $upload_dir['baseurl'];
        $base_dir   = $upload_dir['basedir'];

        if ( strpos( $url, $base_url ) === 0 ) {
            $file_path = str_replace( $base_url, $base_dir, $url );
            if ( file_exists( $file_path ) ) {
                $type = mime_content_type( $file_path );
                $body = file_get_contents( $file_path );
                wp_send_json_success( array(
                    'dataUrl' => 'data:' . $type . ';base64,' . base64_encode( $body ),
                ) );
                return;
            }
        }

        $host = wp_parse_url( $url, PHP_URL_HOST );
        $dominios_permitidos = array( 'drive.google.com', 'lh3.googleusercontent.com', 'docs.google.com' );
        if ( ! $host || ! in_array( $host, $dominios_permitidos, true ) ) {
            wp_send_json_error( array( 'message' => 'Dominio no permitido.' ) );
            return;
        }

        $response = wp_remote_get( $url, array(
            'timeout'   => 30,
            'sslverify' => true,
        ) );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( array( 'message' => $response->get_error_message() ) );
            return;
        }

        $body = wp_remote_retrieve_body( $response );
        $code = wp_remote_retrieve_response_code( $response );
        $type = wp_remote_retrieve_header( $response, 'content-type' );

        if ( $code !== 200 || strpos( $type, 'image' ) === false ) {
            wp_send_json_error( array( 'message' => 'Imagen invalida' ) );
            return;
        }

        $type = strtok( $type, ';' );
        wp_send_json_success( array(
            'dataUrl' => 'data:' . $type . ';base64,' . base64_encode( $body ),
        ) );
    }

    public function handle_upload_document() {
        check_ajax_referer( 'ruteo_submit_nonce', 'nonce' );
        $json = file_get_contents('php://input');
        if (empty($json)) {
            wp_send_json_error( array( 'message' => 'Solicitud vacia.' ) );
            return;
        }

        $response = wp_remote_post( $this->webhook_url, array(
            'body'    => $json,
            'headers' => array( 'Content-Type' => 'application/json' ),
            'timeout' => 45
        ) );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( array( 'message' => 'Error de conexion.' ) );
            return;
        }

        $body   = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);

        if ($result === null) {
            wp_send_json_error( array( 'message' => 'Respuesta invalida.' ) );
            return;
        }

        wp_send_json_success( $result );
    }

    public function handle_ajax_submit() {
        check_ajax_referer( 'ruteo_submit_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => 'Acceso denegado. Inicia sesion.' ) );
            return;
        }

        $data = array();
        $fields = array(
            'tramo', 'id_consol', 'estructura', 'tipo_estructura', 'altura_estructura',
            'ubicacion', 'codigo', 'mufa', 'retencion', 'suspension', 'cruceta',
            'hebillas', 'fleje', 'amortiguador', 'brazo_extensor', 'kit_retenida', 'observacion'
        );

        foreach ( $fields as $field ) {
            $data[$field] = isset( $_POST[$field] ) ? sanitize_text_field( wp_unslash( $_POST[$field] ) ) : '';
        }

        $photo_data = array();
        foreach ( array( 'foto1', 'foto2' ) as $file_key ) {
            if ( ! empty( $_FILES[$file_key]['tmp_name'] ) ) {
                $tmp_file = $_FILES[$file_key]['tmp_name'];
                $type = mime_content_type($tmp_file);
                $content = file_get_contents($tmp_file);
                $photo_data[] = 'data:' . $type . ';base64,' . base64_encode($content);
                @unlink($tmp_file);
            } else {
                $photo_data[] = '';
            }
        }

        $data['foto1_base64'] = $photo_data[0];
        $data['foto2_base64'] = $photo_data[1];
        $data['creado_por'] = wp_get_current_user()->display_name;

        if ( $this->webhook_url ) {
            wp_remote_post( $this->webhook_url, array(
                'body'    => json_encode( $data ),
                'headers' => array( 'Content-Type' => 'application/json' ),
                'timeout' => 15
            ) );
        }

        self::registrar_log( 'Registro de Campo', 'Nuevo registro de campo guardado para tramo: ' . $data['tramo'] . ' (Estructura ' . $data['codigo'] . ')' );

        wp_send_json_success( array(
            'message' => 'Datos de ruteo guardados correctamente.',
            'time'    => current_time( 'mysql' ),
        ) );
    }

    public function handle_ajax_update_registro() {
        check_ajax_referer( 'ruteo_submit_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => 'Acceso denegado. Debe iniciar sesion.' ) );
            return;
        }

        $current_user = wp_get_current_user();
        $is_admin = in_array( 'administrator', (array) $current_user->roles, true ) || in_array( 'ruteo_admin', (array) $current_user->roles, true );
        $is_sup_operativo = in_array( 'ruteo_sup_operativo', (array) $current_user->roles, true );
        $creado_por = isset( $_POST['creado_por'] ) ? sanitize_text_field( wp_unslash( $_POST['creado_por'] ) ) : '';

        if ( ! $is_admin && ! $is_sup_operativo && $creado_por !== $current_user->display_name ) {
            wp_send_json_error( array( 'message' => 'No tienes permiso para editar este registro.' ) );
            return;
        }

        $fields = array(
            'rowIndex', 'tramo', 'id_consol', 'estructura', 'tipo_estructura', 'altura',
            'ubicacion', 'codigo', 'mufa', 'retencion', 'suspension', 'cruceta',
            'observacion'
        );

        $data = array();
        foreach ( $fields as $field ) {
            $data[$field] = isset( $_POST[$field] ) ? sanitize_text_field( wp_unslash( $_POST[$field] ) ) : '';
        }

        $store = get_option( 'wp_ruteo_registros_store', array() );
        if ( is_array( $store ) ) {
            $updated = false;
            foreach ( $store as &$item ) {
                $targetId = ! empty( $data['id_consol'] ) ? $data['id_consol'] : $data['codigo'];
                $rowId = isset( $item['id_consol'] ) && ! empty( $item['id_consol'] ) ? $item['id_consol'] : ( isset( $item['codigo'] ) ? $item['codigo'] : '' );
                if ( ! empty( $targetId ) && (string)$rowId === (string)$targetId ) {
                    foreach ( $data as $k => $v ) {
                        $item[$k] = $v;
                    }
                    $updated = true;
                    break;
                }
            }
            if ( $updated ) {
                update_option( 'wp_ruteo_registros_store', $store, false );
            }
        }

        if ( $this->webhook_url ) {
            $payload = array(
                'action_type' => 'update_registro',
                'record'      => $data,
                'registro'    => $data,
            );
            $response = wp_remote_post( $this->webhook_url, array(
                'body'    => json_encode( $payload ),
                'headers' => array( 'Content-Type' => 'text/plain;charset=utf-8' ),
                'timeout' => 15
            ) );
            
            $gas_body = '';
            if ( is_wp_error( $response ) ) {
                wp_send_json_error( array( 'message' => 'Error de conexión con Google Sheets: ' . $response->get_error_message() ) );
                return;
            } else {
                $gas_body = wp_remote_retrieve_body( $response );
                $gas_json = json_decode( $gas_body, true );
                if ( $gas_json && isset( $gas_json['status'] ) && $gas_json['status'] === 'error' ) {
                    wp_send_json_error( array( 'message' => 'Error de Google Sheets: ' . $gas_json['message'] ) );
                    return;
                }
            }
        }

        self::registrar_log( 'Registro de Campo', 'Edicion de Registro de Ruteo en tramo: ' . $data['tramo'] . ' (ID: ' . $data['id_consol'] . ' / Codigo: ' . $data['codigo'] . ')' );

        wp_send_json_success( array(
            'message'  => 'Registro de ruteo actualizado correctamente.',
            'registro' => $data,
            'time'     => current_time( 'mysql' ),
        ) );
    }

    public function handle_ajax_login() {
        check_ajax_referer( 'ruteo_submit_nonce', 'nonce' );

        $raw_input = isset( $_POST['username'] ) ? trim( wp_unslash( $_POST['username'] ) ) : '';
        $password  = isset( $_POST['password'] ) ? $_POST['password'] : '';
        $remember  = ! empty( $_POST['remember'] );

        if ( empty( $raw_input ) || empty( $password ) ) {
            wp_send_json_error( array( 'message' => 'Usuario y clave son requeridos.' ) );
            return;
        }

        $ip           = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
        $intentos_key = 'ruteo_login_intentos_' . md5( $ip . '|' . $raw_input );
        $intentos     = (int) get_transient( $intentos_key );

        if ( $intentos >= 5 ) {
            wp_send_json_error( array( 'message' => 'Demasiados intentos fallidos. Intenta de nuevo en 10 minutos.' ) );
            return;
        }

        $username = $raw_input;
        if ( is_email( $raw_input ) ) {
            $user_by_email = get_user_by( 'email', $raw_input );
            if ( $user_by_email ) {
                $username = $user_by_email->user_login;
            }
        } else {
            $username = sanitize_user( $raw_input );
        }

        $creds = array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => $remember,
        );

        $user = wp_signon( $creds, is_ssl() );

        if ( is_wp_error( $user ) ) {
            set_transient( $intentos_key, $intentos + 1, 10 * MINUTE_IN_SECONDS );
            wp_send_json_error( array( 'message' => 'Credenciales invalidas. Revisa usuario o correo y clave.' ) );
            return;
        }

        delete_transient( $intentos_key );

        wp_set_current_user( $user->ID );
        wp_set_auth_cookie( $user->ID, $remember );

        $is_admin = in_array( 'administrator', (array) $user->roles, true ) || in_array( 'ruteo_admin', (array) $user->roles, true );
        $role     = $is_admin ? 'admin' : ( in_array( 'ruteo_worker', (array) $user->roles, true ) ? 'worker' : 'user' );

        $phone        = get_user_meta( $user->ID, 'ruteo_phone', true );
        $pm_assigned  = get_user_meta( $user->ID, 'ruteo_pm_assigned', true );
        $avatar       = get_user_meta( $user->ID, 'ruteo_avatar', true );
        $firma_img    = get_user_meta( $user->ID, 'ruteo_firma_img', true );
        $negativa_rol = get_user_meta( $user->ID, 'ruteo_negativa_rol', true );
        $position     = get_user_meta( $user->ID, 'ruteo_position', true );
        $signer_caps  = get_user_meta( $user->ID, 'ruteo_signer_caps', true );
        if ( ! is_array( $signer_caps ) ) {
            $signer_caps = array();
        }

        self::registrar_log( 'Inicio de Sesion', 'El usuario ' . $user->display_name . ' (' . $user->user_login . ') inicio sesion en el aplicativo.' );

        wp_send_json_success( array(
            'message' => 'Inicio de sesion exitoso.',
            'user'    => array(
                'id'          => $user->ID,
                'username'    => $user->user_login,
                'displayName' => html_entity_decode( $user->display_name, ENT_QUOTES, 'UTF-8' ),
                'email'       => $user->user_email,
                'phone'       => $phone,
                'pmAssigned'  => $pm_assigned,
                'avatar'      => $avatar,
                'firma'       => $firma_img,
                'role'        => $role,
                'isAdmin'     => $is_admin,
                'negativaRol' => $negativa_rol ?: '',
                'position'    => $position ?: '',
                'signerCaps'  => $signer_caps,
            ),
        ) );
    }

    public function handle_ajax_logout() {
        check_ajax_referer( 'ruteo_submit_nonce', 'nonce' );
        wp_logout();
        wp_send_json_success( array( 'message' => 'Sesion cerrada correctamente.' ) );
    }

    public function handle_ajax_get_users() {
        check_ajax_referer( 'ruteo_submit_nonce', 'nonce' );

        $current_user = wp_get_current_user();
        $is_admin     = in_array( 'administrator', (array) $current_user->roles, true ) || in_array( 'ruteo_admin', (array) $current_user->roles, true );

        if ( ! $is_admin ) {
            wp_send_json_error( array( 'message' => 'Acceso denegado. Se requieren permisos de Administrador.' ) );
            return;
        }

        $wp_users = get_users( array(
            'role__in' => array( 'ruteo_admin', 'ruteo_sup_operativo', 'ruteo_sup_hse', 'ruteo_worker', 'administrator' ),
            'orderby'  => 'registered',
            'order'    => 'DESC',
        ) );

        $user_list = array();
        foreach ( $wp_users as $u ) {
            $roles      = (array) $u->roles;
            $role_label = 'Operario';
            $role_key   = 'ruteo_worker';

            if ( in_array( 'administrator', $roles, true ) || in_array( 'ruteo_admin', $roles, true ) ) {
                $role_label = 'Admin General';
                $role_key   = 'ruteo_admin';
            } elseif ( in_array( 'ruteo_sup_operativo', $roles, true ) ) {
                $role_label = 'Supervisor Operativo';
                $role_key   = 'ruteo_sup_operativo';
            } elseif ( in_array( 'ruteo_sup_hse', $roles, true ) ) {
                $role_label = 'Supervisor HSE';
                $role_key   = 'ruteo_sup_hse';
            }

            $signer_caps = get_user_meta( $u->ID, 'ruteo_signer_caps', true );
            if ( ! is_array( $signer_caps ) ) {
                $signer_caps = array();
            }

            $user_list[] = array(
                'id'          => $u->ID,
                'username'    => $u->user_login,
                'displayName' => html_entity_decode( $u->display_name, ENT_QUOTES, 'UTF-8' ),
                'email'       => $u->user_email,
                'phone'       => get_user_meta( $u->ID, 'ruteo_phone', true ),
                'pmAssigned'  => get_user_meta( $u->ID, 'ruteo_pm_assigned', true ),
                'avatar'      => get_user_meta( $u->ID, 'ruteo_avatar', true ),
                'role'        => $role_label,
                'roleKey'     => $role_key,
                'signerCaps'  => $signer_caps,
                'negativaRol' => get_user_meta( $u->ID, 'ruteo_negativa_rol', true ) ?: '',
                'position'    => get_user_meta( $u->ID, 'ruteo_position', true ) ?: '',
                'registered'  => $u->user_registered,
            );
        }

        wp_send_json_success( array( 'users' => $user_list ) );
    }

    public function handle_ajax_create_user() {
        check_ajax_referer( 'ruteo_submit_nonce', 'nonce' );

        $current_user = wp_get_current_user();
        $is_admin     = in_array( 'administrator', (array) $current_user->roles, true ) || in_array( 'ruteo_admin', (array) $current_user->roles, true );

        if ( ! $is_admin ) {
            wp_send_json_error( array( 'message' => 'Acceso denegado. Permiso requerido.' ) );
            return;
        }

        $edit_id      = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
        $username     = isset( $_POST['username'] ) ? sanitize_user( wp_unslash( $_POST['username'] ) ) : '';
        $email        = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
        $password     = isset( $_POST['password'] ) ? $_POST['password'] : '';
        $display_name = isset( $_POST['display_name'] ) ? sanitize_text_field( wp_unslash( $_POST['display_name'] ) ) : '';
        $role         = isset( $_POST['role'] ) ? sanitize_text_field( wp_unslash( $_POST['role'] ) ) : 'ruteo_worker';
        $phone        = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
        $pm_assigned  = isset( $_POST['pm_assigned'] ) ? sanitize_text_field( wp_unslash( $_POST['pm_assigned'] ) ) : '';
        $negativa_rol = isset( $_POST['negativa_rol'] ) ? sanitize_text_field( wp_unslash( $_POST['negativa_rol'] ) ) : '';
        $position     = isset( $_POST['position'] ) ? sanitize_text_field( wp_unslash( $_POST['position'] ) ) : '';

        $raw_caps = isset( $_POST['signer_caps'] ) ? $_POST['signer_caps'] : array();
        if ( is_string( $raw_caps ) ) {
            $signer_caps = array_filter( array_map( 'trim', explode( ',', $raw_caps ) ) );
        } elseif ( is_array( $raw_caps ) ) {
            $signer_caps = array_map( 'sanitize_text_field', $raw_caps );
        } else {
            $signer_caps = array();
        }

        $wp_role = 'ruteo_worker';
        if ( $role === 'admin' || $role === 'ruteo_admin' ) {
            $wp_role = 'ruteo_admin';
        } elseif ( $role === 'sup_operativo' || $role === 'ruteo_sup_operativo' ) {
            $wp_role = 'ruteo_sup_operativo';
        } elseif ( $role === 'sup_hse' || $role === 'ruteo_sup_hse' ) {
            $wp_role = 'ruteo_sup_hse';
        }

        if ( $edit_id > 0 ) {
            $user_data = array(
                'ID'           => $edit_id,
                'display_name' => ! empty( $display_name ) ? $display_name : $username,
            );
            if ( ! empty( $email ) ) {
                $user_data['user_email'] = $email;
            }
            if ( ! empty( $password ) ) {
                $user_data['user_pass'] = $password;
            }
            wp_update_user( $user_data );

            $u = new WP_User( $edit_id );
            $u->set_role( $wp_role );

            update_user_meta( $edit_id, 'ruteo_phone', $phone );
            update_user_meta( $edit_id, 'ruteo_pm_assigned', $pm_assigned );
            update_user_meta( $edit_id, 'ruteo_negativa_rol', $negativa_rol );
            update_user_meta( $edit_id, 'ruteo_position', $position );
            update_user_meta( $edit_id, 'ruteo_signer_caps', $signer_caps );

            if ( ! empty( $_FILES['avatar']['tmp_name'] ) ) {
                $tmp_file = $_FILES['avatar']['tmp_name'];
                $type     = mime_content_type( $tmp_file );
                $content  = file_get_contents( $tmp_file );
                $base64   = 'data:' . $type . ';base64,' . base64_encode( $content );
                update_user_meta( $edit_id, 'ruteo_avatar', $base64 );
                @unlink( $tmp_file );
            }

            self::registrar_log( 'Usuario Actualizado', 'Se actualizaron rol (' . $wp_role . '), cargo y permisos de firma del usuario ID #' . $edit_id );

            wp_send_json_success( array(
                'message' => 'Usuario actualizado correctamente.',
                'user_id' => $edit_id,
            ) );
            return;
        }

        if ( empty( $username ) || empty( $password ) || empty( $email ) ) {
            wp_send_json_error( array( 'message' => 'Usuario, correo y clave son obligatorios.' ) );
            return;
        }

        if ( username_exists( $username ) ) {
            wp_send_json_error( array( 'message' => 'El nombre de usuario ya existe.' ) );
            return;
        }

        if ( email_exists( $email ) ) {
            wp_send_json_error( array( 'message' => 'El correo electronico ya esta registrado.' ) );
            return;
        }

        $user_id = wp_insert_user( array(
            'user_login'   => $username,
            'user_pass'    => $password,
            'user_email'   => $email,
            'display_name' => ! empty( $display_name ) ? $display_name : $username,
            'role'         => $wp_role,
        ) );

        if ( is_wp_error( $user_id ) ) {
            wp_send_json_error( array( 'message' => $user_id->get_error_message() ) );
            return;
        }

        update_user_meta( $user_id, 'ruteo_phone', $phone );
        update_user_meta( $user_id, 'ruteo_pm_assigned', $pm_assigned );
        update_user_meta( $user_id, 'ruteo_negativa_rol', $negativa_rol );
        update_user_meta( $user_id, 'ruteo_position', $position );
        update_user_meta( $user_id, 'ruteo_signer_caps', $signer_caps );

        if ( ! empty( $_FILES['avatar']['tmp_name'] ) ) {
            $tmp_file = $_FILES['avatar']['tmp_name'];
            $type     = mime_content_type( $tmp_file );
            $content  = file_get_contents( $tmp_file );
            $base64   = 'data:' . $type . ';base64,' . base64_encode( $content );
            update_user_meta( $user_id, 'ruteo_avatar', $base64 );
            @unlink( $tmp_file );
        }

        self::registrar_log( 'Usuario Creado', 'Se creo la cuenta de usuario ' . $username . ' con rol ' . $wp_role . ' y permisos de firmante' );

        wp_send_json_success( array(
            'message' => 'Usuario creado exitosamente con perfil ampliado.',
            'user_id' => $user_id,
        ) );
    }

    public function handle_ajax_delete_user() {
        check_ajax_referer( 'ruteo_submit_nonce', 'nonce' );

        $current_user = wp_get_current_user();
        $is_admin     = in_array( 'administrator', (array) $current_user->roles, true ) || in_array( 'ruteo_admin', (array) $current_user->roles, true );

        if ( ! $is_admin ) {
            wp_send_json_error( array( 'message' => 'Acceso denegado.' ) );
            return;
        }

        $user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
        if ( $user_id === 0 || $user_id === $current_user->ID ) {
            wp_send_json_error( array( 'message' => 'Accion no permitida.' ) );
            return;
        }

        require_once ABSPATH . 'wp-admin/includes/user.php';
        $deleted = wp_delete_user( $user_id );

        if ( $deleted ) {
            wp_send_json_success( array( 'message' => 'Usuario eliminado correctamente.' ) );
        } else {
            wp_send_json_error( array( 'message' => 'No se pudo eliminar el usuario.' ) );
        }
    }

    public function handle_ajax_update_profile() {
        check_ajax_referer( 'ruteo_submit_nonce', 'nonce' );
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => 'Debes iniciar sesion.' ) );
            return;
        }

        $user_id      = get_current_user_id();
        $display_name = isset( $_POST['display_name'] ) ? sanitize_text_field( wp_unslash( $_POST['display_name'] ) ) : '';
        $phone        = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
        $pm_assigned  = isset( $_POST['pm_assigned'] ) ? sanitize_text_field( wp_unslash( $_POST['pm_assigned'] ) ) : '';
        $position     = isset( $_POST['position'] ) ? sanitize_text_field( wp_unslash( $_POST['position'] ) ) : '';

        if ( ! empty( $display_name ) ) {
            wp_update_user( array( 'ID' => $user_id, 'display_name' => $display_name ) );
        }
        update_user_meta( $user_id, 'ruteo_phone', $phone );
        update_user_meta( $user_id, 'ruteo_pm_assigned', $pm_assigned );
        update_user_meta( $user_id, 'ruteo_position', $position );

        if ( ! empty( $_FILES['avatar']['tmp_name'] ) ) {
            $tmp_file = $_FILES['avatar']['tmp_name'];
            $type     = mime_content_type($tmp_file);
            $content  = file_get_contents($tmp_file);
            $base64   = 'data:' . $type . ';base64,' . base64_encode($content);
            update_user_meta( $user_id, 'ruteo_avatar', $base64 );
            @unlink($tmp_file);
        }

        $firma_actual = get_user_meta( $user_id, 'ruteo_firma_img', true );

        if ( ! empty( $_FILES['firma']['tmp_name'] ) ) {
            $tmp_file = $_FILES['firma']['tmp_name'];
            $type     = mime_content_type($tmp_file);
            $content  = file_get_contents($tmp_file);
            $firma_actual = 'data:' . $type . ';base64,' . base64_encode($content);
            update_user_meta( $user_id, 'ruteo_firma_img', $firma_actual );
            @unlink($tmp_file);
        } elseif ( isset( $_POST['firma_remove'] ) && $_POST['firma_remove'] === '1' ) {
            delete_user_meta( $user_id, 'ruteo_firma_img' );
            $firma_actual = '';
        }

        wp_send_json_success( array( 'message' => 'Perfil actualizado correctamente.', 'firma' => $firma_actual ) );
    }
    
    public function handle_ajax_update_site_logo() {
        check_ajax_referer( 'ruteo_submit_nonce', 'nonce' );

        $current_user = wp_get_current_user();
        $is_admin     = in_array( 'administrator', (array) $current_user->roles, true ) || in_array( 'ruteo_admin', (array) $current_user->roles, true );

        if ( ! $is_admin ) {
            wp_send_json_error( array( 'message' => 'Solo un Administrador puede actualizar el logo del sistema.' ) );
            return;
        }

        if ( empty( $_FILES['logo']['tmp_name'] ) ) {
            wp_send_json_error( array( 'message' => 'No se recibio ningun archivo de imagen.' ) );
            return;
        }

        $tmp_file = $_FILES['logo']['tmp_name'];
        $type     = mime_content_type( $tmp_file );

        $tipos_permitidos = array( 'image/png', 'image/jpeg', 'image/svg+xml', 'image/webp' );
        if ( ! in_array( $type, $tipos_permitidos, true ) ) {
            wp_send_json_error( array( 'message' => 'Formato no valido. Usa PNG, JPG, WEBP o SVG.' ) );
            return;
        }

        if ( filesize( $tmp_file ) > 2 * 1024 * 1024 ) {
            wp_send_json_error( array( 'message' => 'La imagen es muy pesada. Maximo 2MB.' ) );
            return;
        }

        $content = file_get_contents( $tmp_file );
        $base64  = 'data:' . $type . ';base64,' . base64_encode( $content );
        @unlink( $tmp_file );

        update_option( 'ruteo_site_logo', $base64, false );

        wp_send_json_success( array(
            'message' => 'Logo actualizado correctamente.',
            'logo'    => $base64,
        ) );
    }

    public function handle_ajax_save_materiales() {
        check_ajax_referer( 'ruteo_submit_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => 'Acceso denegado. Debes iniciar sesion.' ) );
            return;
        }

        $incidencia  = isset( $_POST['incidencia'] ) ? sanitize_text_field( wp_unslash( $_POST['incidencia'] ) ) : '';
        $crq         = isset( $_POST['crq'] ) ? sanitize_text_field( wp_unslash( $_POST['crq'] ) ) : '';
        $descripcion = isset( $_POST['descripcion'] ) ? sanitize_text_field( wp_unslash( $_POST['descripcion'] ) ) : '';
        $tramo       = isset( $_POST['tramo'] ) ? sanitize_text_field( wp_unslash( $_POST['tramo'] ) ) : '';
        $fecha       = isset( $_POST['fecha'] ) ? sanitize_text_field( wp_unslash( $_POST['fecha'] ) ) : current_time('Y-m-d');
        $almacen_pm  = isset( $_POST['almacen_pm'] ) ? sanitize_text_field( wp_unslash( $_POST['almacen_pm'] ) ) : '';
        $raw_items   = isset( $_POST['items'] ) ? wp_unslash( $_POST['items'] ) : '[]';

        if ( empty( $incidencia ) || empty( $almacen_pm ) ) {
            wp_send_json_error( array( 'message' => 'Incidencia y Almacen PM son campos obligatorios.' ) );
            return;
        }

        $items = json_decode( $raw_items, true );
        if ( ! is_array($items) || empty($items) ) {
            wp_send_json_error( array( 'message' => 'Debe agregar al menos un material utilizado.' ) );
            return;
        }

        $new_report = array(
            'id'          => 'MAT-' . time(),
            'incidencia'  => $incidencia,
            'crq'         => $crq,
            'descripcion' => $descripcion,
            'tramo'       => $tramo,
            'fecha'       => $fecha,
            'almacen_pm'  => $almacen_pm,
            'items'       => $items,
            'user'        => wp_get_current_user()->display_name,
            'created_at'  => current_time( 'mysql' ),
        );

        $materiales = get_option( 'wp_ruteo_materiales_store', array() );
        if ( ! is_array( $materiales ) ) {
            $materiales = array();
        }

        array_unshift( $materiales, $new_report );
        update_option( 'wp_ruteo_materiales_store', $materiales, false );

        self::registrar_log( 'Consumo Materiales', 'Se registro reporte de materiales para incidencia ' . $incidencia . ' (' . $almacen_pm . ')' );

        // Enviar copia a Google Apps Script
        if ( $this->webhook_url ) {
            $payload = array(
                'action_type' => 'save_materiales',
                'report'      => $new_report,
            );
            wp_remote_post( $this->webhook_url, array(
                'body'    => json_encode( $payload ),
                'headers' => array( 'Content-Type' => 'application/json' ),
                'timeout' => 15
            ) );
        }

        wp_send_json_success( array(
            'message' => 'Reporte de consumo de materiales registrado con exito.',
            'report'  => $new_report
        ) );
    }

    public function handle_ajax_get_materiales() {
        check_ajax_referer( 'ruteo_submit_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => 'Acceso denegado.' ) );
            return;
        }

        $materiales = get_option( 'wp_ruteo_materiales_store', array() );
        if ( ! is_array( $materiales ) ) {
            $materiales = array();
        }

        wp_send_json_success( array(
            'materiales' => $materiales,
            'total'      => count($materiales)
        ) );
    }

    public function handle_ajax_update_material() {
        check_ajax_referer( 'ruteo_submit_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => 'Acceso denegado. Debes iniciar sesion.' ) );
            return;
        }

        $id          = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
        $incidencia  = isset( $_POST['incidencia'] ) ? sanitize_text_field( wp_unslash( $_POST['incidencia'] ) ) : '';
        $crq         = isset( $_POST['crq'] ) ? sanitize_text_field( wp_unslash( $_POST['crq'] ) ) : '';
        $descripcion = isset( $_POST['descripcion'] ) ? sanitize_text_field( wp_unslash( $_POST['descripcion'] ) ) : '';
        $tramo       = isset( $_POST['tramo'] ) ? sanitize_text_field( wp_unslash( $_POST['tramo'] ) ) : '';
        $fecha       = isset( $_POST['fecha'] ) ? sanitize_text_field( wp_unslash( $_POST['fecha'] ) ) : '';
        $almacen_pm  = isset( $_POST['almacen_pm'] ) ? sanitize_text_field( wp_unslash( $_POST['almacen_pm'] ) ) : '';

        if ( empty( $id ) ) {
            wp_send_json_error( array( 'message' => 'ID de reporte no proporcionado.' ) );
            return;
        }

        $materiales = get_option( 'wp_ruteo_materiales_store', array() );
        if ( ! is_array( $materiales ) ) {
            $materiales = array();
        }

        $updated = false;
        foreach ( $materiales as &$mat ) {
            if ( isset( $mat['id'] ) && $mat['id'] === $id ) {
                $mat['incidencia']  = $incidencia;
                $mat['crq']         = $crq;
                $mat['descripcion'] = $descripcion;
                $mat['tramo']       = $tramo;
                if ( ! empty( $fecha ) ) $mat['fecha'] = $fecha;
                if ( ! empty( $almacen_pm ) ) $mat['almacen_pm'] = $almacen_pm;
                $updated = true;
                break;
            }
        }

        if ( $updated ) {
            update_option( 'wp_ruteo_materiales_store', $materiales, false );
            self::registrar_log( 'Consumo Materiales', 'Edicion de reporte de consumo de materiales ' . $id . ' (Incidencia ' . $incidencia . ')' );
            wp_send_json_success( array( 'message' => 'Reporte de materiales actualizado correctamente.' ) );
        } else {
            wp_send_json_error( array( 'message' => 'No se encontro el reporte de materiales.' ) );
        }
    }

    public function handle_ajax_get_clientes() {
        check_ajax_referer( 'ruteo_submit_nonce', 'nonce' );
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => 'Acceso denegado.' ) );
            return;
        }
        $clientes = get_option( 'ruteo_clientes_list', array() );
        if ( empty( $clientes ) || ! is_array( $clientes ) ) {
            $clientes = array(
                array(
                    'id'     => 'CLI-CYMTEL',
                    'nombre' => 'CYMTEL',
                    'ruc'    => '20512345678',
                    'logo'   => '',
                )
            );
            update_option( 'ruteo_clientes_list', $clientes, false );
        }
        wp_send_json_success( array( 'clientes' => $clientes ) );
    }

    public function handle_ajax_save_cliente() {
        check_ajax_referer( 'ruteo_submit_nonce', 'nonce' );
        $current_user = wp_get_current_user();
        $is_admin     = in_array( 'administrator', (array) $current_user->roles, true ) || in_array( 'ruteo_admin', (array) $current_user->roles, true );

        if ( ! $is_admin ) {
            wp_send_json_error( array( 'message' => 'Solo administradores pueden gestionar clientes.' ) );
            return;
        }

        $id        = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
        $nombre    = isset( $_POST['nombre'] ) ? sanitize_text_field( wp_unslash( $_POST['nombre'] ) ) : '';
        $ruc       = isset( $_POST['ruc'] ) ? sanitize_text_field( wp_unslash( $_POST['ruc'] ) ) : '';
        $direccion = isset( $_POST['direccion'] ) ? sanitize_text_field( wp_unslash( $_POST['direccion'] ) ) : '';
        $contacto  = isset( $_POST['contacto'] ) ? sanitize_text_field( wp_unslash( $_POST['contacto'] ) ) : '';
        $logo      = isset( $_POST['logo_base64'] ) ? $_POST['logo_base64'] : '';

        if ( empty( $nombre ) ) {
            wp_send_json_error( array( 'message' => 'El nombre del cliente es obligatorio.' ) );
            return;
        }

        if ( ! empty( $_FILES['logo']['tmp_name'] ) ) {
            $tmp     = $_FILES['logo']['tmp_name'];
            $type    = mime_content_type( $tmp );
            $content = file_get_contents( $tmp );
            $logo    = 'data:' . $type . ';base64,' . base64_encode( $content );
            @unlink( $tmp );
        }

        $clientes = get_option( 'ruteo_clientes_list', array() );
        if ( ! is_array( $clientes ) ) {
            $clientes = array();
        }

        if ( ! empty( $id ) ) {
            foreach ( $clientes as &$c ) {
                if ( isset( $c['id'] ) && $c['id'] === $id ) {
                    $c['nombre']    = $nombre;
                    $c['ruc']       = $ruc;
                    $c['direccion'] = $direccion;
                    $c['contacto']  = $contacto;
                    if ( ! empty( $logo ) ) {
                        $c['logo'] = $logo;
                    }
                    break;
                }
            }
        } else {
            $new_id = 'CLI-' . time();
            $clientes[] = array(
                'id'        => $new_id,
                'nombre'    => $nombre,
                'ruc'       => $ruc,
                'direccion' => $direccion,
                'contacto'  => $contacto,
                'logo'      => $logo,
            );
        }

        update_option( 'ruteo_clientes_list', $clientes, false );
        wp_send_json_success( array(
            'message'  => 'Cliente guardado con exito.',
            'clientes' => $clientes
        ) );
    }

    public function handle_ajax_delete_cliente() {
        check_ajax_referer( 'ruteo_submit_nonce', 'nonce' );
        $current_user = wp_get_current_user();
        $is_admin     = in_array( 'administrator', (array) $current_user->roles, true ) || in_array( 'ruteo_admin', (array) $current_user->roles, true );

        if ( ! $is_admin ) {
            wp_send_json_error( array( 'message' => 'Acceso denegado.' ) );
            return;
        }

        $id = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
        if ( empty( $id ) ) {
            wp_send_json_error( array( 'message' => 'ID invalido.' ) );
            return;
        }

        $clientes = get_option( 'ruteo_clientes_list', array() );
        if ( is_array( $clientes ) ) {
            $clientes = array_values( array_filter( $clientes, function( $c ) use ( $id ) {
                return isset( $c['id'] ) && $c['id'] !== $id;
            } ) );
            update_option( 'ruteo_clientes_list', $clientes );
        }

        wp_send_json_success( array(
            'message'  => 'Cliente eliminado correctamente.',
            'clientes' => $clientes
        ) );
    }

    public function handle_ajax_negativa_guardar() {
        check_ajax_referer( 'ruteo_submit_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => 'Acceso denegado. Debes iniciar sesion.' ) );
            return;
        }
        

        global $wpdb;
        $table = $wpdb->prefix . 'ruteo_negativas';
        $current_user = wp_get_current_user();
        $now = current_time( 'mysql' );

        $id    = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
        $etapa = isset( $_POST['etapa'] ) ? sanitize_text_field( wp_unslash( $_POST['etapa'] ) ) : '';

        $is_admin = in_array( 'administrator', (array) $current_user->roles, true ) || in_array( 'ruteo_admin', (array) $current_user->roles, true );
        $negativa_rol = get_user_meta( $current_user->ID, 'ruteo_negativa_rol', true );

        $rol_requerido = array(
            'tecnico'    => 'tecnico',
            'supervisor' => 'supervisor_operativo',
            'seguridad'  => 'supervisor_seguridad',
            'hse'        => 'hse',
        );

        if ( ! $is_admin && ( ! isset( $rol_requerido[ $etapa ] ) || $negativa_rol !== $rol_requerido[ $etapa ] ) ) {
            wp_send_json_error( array( 'message' => 'No tienes permiso para firmar esta etapa.' ) );
            return;
        }

        if ( ! in_array( $etapa, array( 'tecnico', 'supervisor', 'seguridad', 'hse' ), true ) ) {
            wp_send_json_error( array( 'message' => 'Etapa invalida.' ) );
            return;
        }

        $cliente_nombre = sanitize_text_field( wp_unslash( $_POST['cliente_nombre'] ?? 'CYMTEL' ) );
        $cliente_logo   = isset( $_POST['cliente_logo'] ) ? $_POST['cliente_logo'] : '';

        // Firma digital guardada por el usuario en su Perfil; se "congela" en el registro al firmar.
        $firma_img_firmante = get_user_meta( $current_user->ID, 'ruteo_firma_img', true );

        if ( $etapa === 'tecnico' ) {
            // --- Validación obligatoria: no se puede crear el registro con datos incompletos ---
            $campos_requeridos = array(
                'proceso'                     => 'Proceso',
                'cm_localidad'                => 'CM / Localidad',
                'contratista'                 => 'Contratista',
                'lugar_trabajo'               => 'Lugar del trabajo',
                'fecha'                       => 'Fecha',
                'hora_inicio'                 => 'Hora de inicio',
                'hora_fin'                    => 'Hora de fin',
                'supervisor_operativo_nombre' => 'Nombre del Supervisor Operativo',
                'trabajador_reportante'       => 'Trabajador Reportante',
                'razones_negativa'            => 'Razones para la negativa',
            );
            $faltantes = array();
            foreach ( $campos_requeridos as $campo => $etiqueta ) {
                $valor = isset( $_POST[ $campo ] ) ? trim( wp_unslash( $_POST[ $campo ] ) ) : '';
                if ( $valor === '' ) {
                    $faltantes[] = $etiqueta;
                }
            }
            if ( empty( $_FILES['foto1']['tmp_name'] ) || empty( $_FILES['foto2']['tmp_name'] ) ) {
                $faltantes[] = 'las 2 fotos de evidencia';
            }
            if ( empty( $firma_img_firmante ) ) {
                $faltantes[] = 'tu firma digital (súbela en tu Perfil antes de firmar)';
            }
            if ( ! empty( $faltantes ) ) {
                wp_send_json_error( array( 'message' => 'No se puede guardar: faltan datos obligatorios -> ' . implode( ', ', $faltantes ) . '.' ) );
                return;
            }

            $campos = array(
                'proceso'                     => sanitize_text_field( wp_unslash( $_POST['proceso'] ?? '' ) ),
                'cm_localidad'                => sanitize_text_field( wp_unslash( $_POST['cm_localidad'] ?? '' ) ),
                'contratista'                 => sanitize_text_field( wp_unslash( $_POST['contratista'] ?? '' ) ),
                'sub_contratista'             => sanitize_text_field( wp_unslash( $_POST['sub_contratista'] ?? '' ) ),
                'relacionado_a'               => sanitize_text_field( wp_unslash( $_POST['relacionado_a'] ?? '' ) ),
                'lugar_trabajo'               => sanitize_text_field( wp_unslash( $_POST['lugar_trabajo'] ?? '' ) ),
                'fecha'                       => sanitize_text_field( wp_unslash( $_POST['fecha'] ?? '' ) ),
                'hora_inicio'                 => sanitize_text_field( wp_unslash( $_POST['hora_inicio'] ?? '' ) ),
                'hora_fin'                    => sanitize_text_field( wp_unslash( $_POST['hora_fin'] ?? '' ) ),
                'total_horas'                 => sanitize_text_field( wp_unslash( $_POST['total_horas'] ?? '' ) ),
                'supervisor_operativo_nombre' => sanitize_text_field( wp_unslash( $_POST['supervisor_operativo_nombre'] ?? '' ) ),
                'trabajador_reportante'       => sanitize_text_field( wp_unslash( $_POST['trabajador_reportante'] ?? '' ) ),
                'razones_negativa'            => sanitize_textarea_field( wp_unslash( $_POST['razones_negativa'] ?? '' ) ),
                'cliente_nombre'              => $cliente_nombre,
                'cliente_logo'                => $cliente_logo,
                'firma_tecnico_user'          => ! empty( $_POST['trabajador_reportante'] ) ? sanitize_text_field( wp_unslash( $_POST['trabajador_reportante'] ) ) : $current_user->display_name,
                'firma_tecnico_fecha'         => $now,
                'firma_tecnico_img'           => $firma_img_firmante,
                'estado'                      => 'pendiente_supervisor',
                'creado_por'                  => $current_user->display_name,
            );

            foreach ( array( 'foto1', 'foto2' ) as $i => $file_key ) {
                if ( ! empty( $_FILES[ $file_key ]['tmp_name'] ) ) {
                    $tmp     = $_FILES[ $file_key ]['tmp_name'];
                    $type    = mime_content_type( $tmp );
                    $content = file_get_contents( $tmp );
                    $campos[ 'foto' . ( $i + 1 ) . '_url' ] = 'data:' . $type . ';base64,' . base64_encode( $content );
                    @unlink( $tmp );
                }
            }

            if ( $id > 0 ) {
                $wpdb->update( $table, $campos, array( 'id' => $id ) );
            } else {
                $inserted = $wpdb->insert( $table, $campos );
                if ( false === $inserted ) {
                    wp_send_json_error( array( 'message' => 'Error al guardar en base de datos: ' . $wpdb->last_error ) );
                    return;
                }
                $id = $wpdb->insert_id;
            }

        } elseif ( $etapa === 'supervisor' ) {
            $registro_actual = $wpdb->get_row( $wpdb->prepare( "SELECT estado FROM $table WHERE id = %d", $id ), ARRAY_A );
            if ( ! $registro_actual ) {
                wp_send_json_error( array( 'message' => 'El registro no existe.' ) );
                return;
            }
            if ( $registro_actual['estado'] !== 'pendiente_supervisor' ) {
                wp_send_json_error( array( 'message' => 'Este registro no esta en la etapa de Supervisor.' ) );
                return;
            }
            // --- Validación obligatoria: Medidas Correctivas, Satisface Negativa y Reinicio de Labores ---
            $faltantes = array();
            $medidas    = isset( $_POST['medidas_correctivas'] ) ? trim( wp_unslash( $_POST['medidas_correctivas'] ) ) : '';
            $satisface  = isset( $_POST['satisface_negativa'] ) ? sanitize_text_field( wp_unslash( $_POST['satisface_negativa'] ) ) : '';
            $reinicia   = isset( $_POST['reinicia_labores'] ) ? sanitize_text_field( wp_unslash( $_POST['reinicia_labores'] ) ) : '';
            if ( $medidas === '' ) {
                $faltantes[] = 'Medidas Correctivas Aplicadas';
            }
            if ( ! in_array( $satisface, array( 'SI', 'NO' ), true ) ) {
                $faltantes[] = 'Satisface Negativa al Trabajo (SI / NO)';
            }
            if ( ! in_array( $reinicia, array( 'SI', 'NO' ), true ) ) {
                $faltantes[] = 'Se reinician las labores (SI / NO)';
            }
            if ( empty( $id ) ) {
                $faltantes[] = 'un registro válido (id no recibido)';
            }
            if ( empty( $firma_img_firmante ) ) {
                $faltantes[] = 'tu firma digital (súbela en tu Perfil antes de firmar)';
            }
            if ( ! empty( $faltantes ) ) {
                wp_send_json_error( array( 'message' => 'No se puede guardar: faltan datos obligatorios -> ' . implode( ', ', $faltantes ) . '.' ) );
                return;
            }

            $update_ok = $wpdb->update( $table, array(
                'proceso'                     => sanitize_text_field( wp_unslash( $_POST['proceso'] ?? '' ) ),
                'cm_localidad'                => sanitize_text_field( wp_unslash( $_POST['cm_localidad'] ?? '' ) ),
                'contratista'                 => sanitize_text_field( wp_unslash( $_POST['contratista'] ?? '' ) ),
                'sub_contratista'             => sanitize_text_field( wp_unslash( $_POST['sub_contratista'] ?? '' ) ),
                'relacionado_a'               => sanitize_text_field( wp_unslash( $_POST['relacionado_a'] ?? '' ) ),
                'lugar_trabajo'               => sanitize_text_field( wp_unslash( $_POST['lugar_trabajo'] ?? '' ) ),
                'fecha'                       => sanitize_text_field( wp_unslash( $_POST['fecha'] ?? '' ) ),
                'hora_inicio'                 => sanitize_text_field( wp_unslash( $_POST['hora_inicio'] ?? '' ) ),
                'hora_fin'                    => sanitize_text_field( wp_unslash( $_POST['hora_fin'] ?? '' ) ),
                'total_horas'                 => sanitize_text_field( wp_unslash( $_POST['total_horas'] ?? '' ) ),
                'supervisor_operativo_nombre' => sanitize_text_field( wp_unslash( $_POST['supervisor_operativo_nombre'] ?? '' ) ),
                'trabajador_reportante'       => sanitize_text_field( wp_unslash( $_POST['trabajador_reportante'] ?? '' ) ),
                'razones_negativa'            => sanitize_textarea_field( wp_unslash( $_POST['razones_negativa'] ?? '' ) ),
                'medidas_correctivas'         => sanitize_textarea_field( wp_unslash( $_POST['medidas_correctivas'] ?? '' ) ),
                'satisface_negativa'          => $satisface,
                'reinicia_labores'            => $reinicia,
                'fecha_reinicio'              => sanitize_text_field( wp_unslash( $_POST['fecha_reinicio'] ?? '' ) ) ?: null,
                'hora_reinicio'               => sanitize_text_field( wp_unslash( $_POST['hora_reinicio'] ?? '' ) ),
                'cliente_nombre'              => $cliente_nombre,
                'cliente_logo'                => $cliente_logo,
                'firma_sup_operativo_user'    => $current_user->display_name,
                'firma_sup_operativo_fecha'   => $now,
                'firma_sup_operativo_img'     => $firma_img_firmante,
                'estado'                      => 'pendiente_seguridad',
            ), array( 'id' => $id ) );
            if ( false === $update_ok ) {
                wp_send_json_error( array( 'message' => 'Error al guardar en base de datos: ' . $wpdb->last_error ) );
                return;
            }

        } elseif ( $etapa === 'seguridad' ) {
            $registro_actual = $wpdb->get_row( $wpdb->prepare( "SELECT estado FROM $table WHERE id = %d", $id ), ARRAY_A );
            if ( ! $registro_actual ) {
                wp_send_json_error( array( 'message' => 'El registro no existe.' ) );
                return;
            }
            if ( $registro_actual['estado'] !== 'pendiente_seguridad' ) {
                wp_send_json_error( array( 'message' => 'Este registro no esta en la etapa de Seguridad.' ) );
                return;
            }

            // --- Esta etapa es solo de verificacion y firma: las observaciones son opcionales, no relleno obligatorio. ---
            // El "nombre" ya no se pide por formulario: se toma automaticamente del usuario logueado.
            $faltantes    = array();
            $seg_observ   = isset( $_POST['observaciones_seguridad'] ) ? trim( wp_unslash( $_POST['observaciones_seguridad'] ) ) : '';
            if ( empty( $id ) ) {
                $faltantes[] = 'un registro válido (id no recibido)';
            }
            if ( empty( $firma_img_firmante ) ) {
                $faltantes[] = 'tu firma digital (súbela en tu Perfil antes de firmar)';
            }
            if ( ! empty( $faltantes ) ) {
                wp_send_json_error( array( 'message' => 'No se puede guardar: faltan datos obligatorios -> ' . implode( ', ', $faltantes ) . '.' ) );
                return;
            }
            $update_ok = $wpdb->update( $table, array(
                'supervisor_seguridad_nombre' => $current_user->display_name,
                'observaciones_seguridad'     => sanitize_textarea_field( $seg_observ ),
                'firma_sup_seguridad_user'    => $current_user->display_name,
                'firma_sup_seguridad_fecha'   => $now,
                'firma_sup_seguridad_img'     => $firma_img_firmante,
                'estado'                      => 'pendiente_hse',
            ), array( 'id' => $id ) );
            if ( false === $update_ok ) {
                wp_send_json_error( array( 'message' => 'Error al guardar en base de datos: ' . $wpdb->last_error ) );
                return;
            }

        } elseif ( $etapa === 'hse' ) {
            $registro_actual = $wpdb->get_row( $wpdb->prepare( "SELECT estado FROM $table WHERE id = %d", $id ), ARRAY_A );
            if ( ! $registro_actual ) {
                wp_send_json_error( array( 'message' => 'El registro no existe.' ) );
                return;
            }
            if ( $registro_actual['estado'] !== 'pendiente_hse' ) {
                wp_send_json_error( array( 'message' => 'Este registro no esta en la etapa de HSE.' ) );
                return;
            }
            // --- Esta etapa es el Visto Bueno final: el dictamen es un comentario opcional, no relleno obligatorio. ---
            // El "nombre" ya no se pide por formulario: se toma automaticamente del usuario logueado.
            $faltantes   = array();
            $hse_dictam  = isset( $_POST['dictamen_hse'] ) ? trim( wp_unslash( $_POST['dictamen_hse'] ) ) : '';
            if ( empty( $id ) ) {
                $faltantes[] = 'un registro válido (id no recibido)';
            }
            if ( empty( $firma_img_firmante ) ) {
                $faltantes[] = 'tu firma digital (súbela en tu Perfil antes de firmar)';
            }
            if ( ! empty( $faltantes ) ) {
                wp_send_json_error( array( 'message' => 'No se puede guardar: faltan datos obligatorios -> ' . implode( ', ', $faltantes ) . '.' ) );
                return;
            }
            $update_ok = $wpdb->update( $table, array(
                'hse_nombre'      => $current_user->display_name,
                'dictamen_hse'    => sanitize_textarea_field( $hse_dictam ),
                'firma_hse_user'  => $current_user->display_name,
                'firma_hse_fecha' => $now,
                'firma_hse_img'   => $firma_img_firmante,
                'estado'          => 'completado',
            ), array( 'id' => $id ) );
            if ( false === $update_ok ) {
                wp_send_json_error( array( 'message' => 'Error al guardar en base de datos: ' . $wpdb->last_error ) );
                return;
            }
        }

        $registro = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id ), ARRAY_A );

        if ( $registro ) {
            $store = get_option( 'wp_ruteo_negativas_store', array() );
            if ( ! is_array( $store ) ) $store = array();
            $found = false;
            foreach ( $store as &$item ) {
                if ( isset( $item['id'] ) && (int) $item['id'] === (int) $id ) {
                    $item = $registro;
                    $found = true;
                    break;
                }
            }
            if ( ! $found ) {
                array_unshift( $store, $registro );
            }
            update_option( 'wp_ruteo_negativas_store', $store, false );

            if ( $this->webhook_url ) {
                $webhook_resp = wp_remote_post( $this->webhook_url, array(
                    'body'    => json_encode( array(
                        'action_type'   => 'upload_document',
                        'document_type' => 'negativa_hse_re_neg_01',
                        'negativa'      => $registro,
                        'save_drive'    => true
                    ) ),
                    'headers' => array( 'Content-Type' => 'application/json' ),
                    'timeout' => 20
                ) );
                if ( ! is_wp_error( $webhook_resp ) ) {
                    $b = wp_remote_retrieve_body( $webhook_resp );
                    self::registrar_log('Webhook Negativa', 'Respuesta GAS: ' . $b);
                } else {
                    self::registrar_log('Webhook Negativa', 'Error de WP: ' . $webhook_resp->get_error_message());
                }
            }
        }

        self::registrar_log( 'Negativa al Trabajo', 'Firma y actualizacion de etapa ' . strtoupper($etapa) . ' para registro ID #' . $id );

        wp_send_json_success( array(
            'message'  => 'Etapa guardada correctamente.',
            'registro' => $registro,
        ) );
    }

    public function handle_ajax_negativa_listar() {
        check_ajax_referer( 'ruteo_submit_nonce', 'nonce' );
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => 'Acceso denegado.' ) );
            return;
        }

        set_time_limit( 30 );
        nocache_headers();
        header( 'Cache-Control: no-cache, no-store, must-revalidate, max-age=0' );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        // Leer 100% directamente desde Google Sheets (pestaña Negativas) como en Ruteo
        if ( $this->webhook_url ) {
            $target_url = add_query_arg( array(
                'action' => 'get_negativas',
                '_ts'    => microtime( true )
            ), $this->webhook_url );

            $response = wp_remote_get( $target_url, array(
                'timeout'     => 25,
                'redirection' => 5,
                'sslverify'   => false,
                'user-agent'  => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ) );

            if ( ! is_wp_error( $response ) ) {
                $code = wp_remote_retrieve_response_code( $response );
                $body = wp_remote_retrieve_body( $response );
                if ( $code === 200 && ! empty( $body ) ) {
                    $json = json_decode( $body, true );
                    if ( isset( $json['status'] ) && $json['status'] === 'success' && isset( $json['negativas'] ) ) {
                        update_option( 'ruteo_cache_negativas', $json['negativas'], false );
                        wp_send_json_success( array( 'registros' => $json['negativas'] ) );
                        return;
                    }
                }
            }
        }

        // Fallback a registros cacheados si falla la conexión con Google Sheets
        $cached = get_option( 'ruteo_cache_negativas', array() );
        wp_send_json_success( array( 'registros' => is_array( $cached ) ? $cached : array() ) );
    }

    public function handle_ajax_sync_negativas_sheets() {
        check_ajax_referer( 'ruteo_submit_nonce', 'nonce' );
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => 'Acceso denegado.' ) );
            return;
        }
        global $wpdb;
        $table = $wpdb->prefix . 'ruteo_negativas';
        $registros = $wpdb->get_results( "SELECT * FROM $table ORDER BY id ASC", ARRAY_A );

        if ( empty( $registros ) ) {
            wp_send_json_error( array( 'message' => 'No hay negativas registradas en la base de datos para sincronizar.' ) );
            return;
        }

        if ( ! $this->webhook_url ) {
            wp_send_json_error( array( 'message' => 'URL de Webhook no configurada.' ) );
            return;
        }

        $response = wp_remote_post( $this->webhook_url, array(
            'body'    => json_encode( array(
                'action_type' => 'sync_all_negativas',
                'negativas'   => $registros
            ) ),
            'headers' => array( 'Content-Type' => 'text/plain;charset=utf-8' ),
            'timeout' => 25
        ) );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( array( 'message' => 'Error de conexión con Google Sheets: ' . $response->get_error_message() ) );
            return;
        }

        self::registrar_log( 'Sincronizacion Negativas', 'Se sincronizaron ' . count( $registros ) . ' negativas a Google Sheets' );
        wp_send_json_success( array( 'message' => 'Se sincronizaron ' . count( $registros ) . ' negativas en Google Sheets exitosamente.' ) );
    }

    public static function registrar_log( $accion, $detalles = '' ) {
        $logs = get_option( 'ruteo_audit_logs', array() );
        if ( ! is_array( $logs ) ) {
            $logs = array();
        }

        $current_user = wp_get_current_user();
        $user_name = $current_user->exists() ? $current_user->display_name : 'Sistema';
        $user_pm   = $current_user->exists() ? get_user_meta( $current_user->ID, 'ruteo_pm_assigned', true ) : '';

        $entry = array(
            'fecha'    => current_time( 'mysql' ),
            'usuario'  => $user_name,
            'pm'       => $user_pm ?: '-',
            'accion'   => $accion,
            'detalles' => $detalles,
        );

        array_unshift( $logs, $entry );

        if ( count( $logs ) > 500 ) {
            $logs = array_slice( $logs, 0, 500 );
        }

        update_option( 'ruteo_audit_logs', $logs, false );
        
        $log_line = "[" . current_time('mysql') . "] [" . $user_name . "] [PM: " . ($user_pm ?: '-') . "] " . $accion . ": " . $detalles . PHP_EOL;
        @file_put_contents( plugin_dir_path( __FILE__ ) . 'ruteo-debug.log', $log_line, FILE_APPEND );
    }

    public function handle_ajax_get_logs() {
        check_ajax_referer( 'ruteo_submit_nonce', 'nonce' );
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => 'Acceso denegado.' ) );
            return;
        }
        $logs = get_option( 'ruteo_audit_logs', array() );
        if ( empty( $logs ) || ! is_array( $logs ) ) {
            $logs = array(
                array(
                    'fecha'    => current_time( 'mysql' ),
                    'usuario'  => 'Administrador General O&M',
                    'pm'       => 'PM-PRINCIPAL',
                    'accion'   => 'Sistema Inicializado',
                    'detalles' => 'Modulos y registro de auditoria cargados correctamente.',
                )
            );
            update_option( 'ruteo_audit_logs', $logs );
        } else {
            // Mapear PM a registros existentes que no lo tengan guardado
            $all_users = get_users();
            $user_pm_map = array();
            foreach ( $all_users as $u ) {
                $pm = get_user_meta( $u->ID, 'ruteo_pm_assigned', true );
                $user_pm_map[ strtolower( trim( $u->display_name ) ) ] = $pm ?: '-';
            }
            $clean_logs = array();
            foreach ( $logs as $l ) {
                if ( isset( $l['accion'] ) && $l['accion'] === 'Debug GAS Update' ) {
                    continue;
                }
                if ( empty( $l['pm'] ) || $l['pm'] === '-' ) {
                    $uname_key = strtolower( trim( $l['usuario'] ?? '' ) );
                    if ( isset( $user_pm_map[ $uname_key ] ) ) {
                        $l['pm'] = $user_pm_map[ $uname_key ];
                    } else {
                        $l['pm'] = '-';
                    }
                }
                $clean_logs[] = $l;
            }
            $logs = $clean_logs;
        }
        wp_send_json_success( array( 'logs' => $logs ) );
    }

    public function handle_ajax_get_image_base64() {
        check_ajax_referer( 'ruteo_submit_nonce', 'nonce' );
        $url = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';
        if ( empty( $url ) ) {
            wp_send_json_error( array( 'message' => 'URL vacia.' ) );
            return;
        }

        if ( preg_match( '/\/d\/([a-zA-Z0-9_-]+)/', $url, $matches ) || preg_match( '/[?&]id=([a-zA-Z0-9_-]+)/', $url, $matches ) ) {
            $file_id = $matches[1];
            $url     = 'https://drive.google.com/uc?export=view&id=' . $file_id;
        }

        $host = wp_parse_url( $url, PHP_URL_HOST );
        $dominios_permitidos = array( 'drive.google.com', 'lh3.googleusercontent.com', 'docs.google.com' );
        if ( ! $host || ! in_array( $host, $dominios_permitidos, true ) ) {
            wp_send_json_error( array( 'message' => 'Dominio no permitido.' ) );
            return;
        }

        $response = wp_remote_get( $url, array(
            'timeout'     => 15,
            'redirection' => 5,
            'sslverify'   => true,
        ) );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( array( 'message' => 'Error al obtener la imagen.' ) );
            return;
        }

        $code = wp_remote_retrieve_response_code( $response );
        if ( $code !== 200 ) {
            wp_send_json_error( array( 'message' => 'Respuesta HTTP ' . $code ) );
            return;
        }

        $body = wp_remote_retrieve_body( $response );
        $type = wp_remote_retrieve_header( $response, 'content-type' );
        if ( empty( $type ) || strpos( $type, 'image' ) === false ) {
            $type = 'image/jpeg';
        }

        $base64 = 'data:' . $type . ';base64,' . base64_encode( $body );
        wp_send_json_success( array( 'base64' => $base64 ) );
    }

    /**
     * Verifica si la tabla wp_ruteo_negativas necesita columnas nuevas
     * (p.ej. las de firma digital) y las agrega automaticamente via dbDelta,
     * sin necesidad de reactivar el plugin ni ejecutarlo en cada carga de pagina.
     */
    public function maybe_upgrade_negativas_table() {
        $version_actual = '2.3.0';
        if ( get_option( 'ruteo_negativas_db_version' ) === $version_actual ) {
            return;
        }
        ruteo_crear_tabla_negativas();
        update_option( 'ruteo_negativas_db_version', $version_actual );
    }
}

function ruteo_crear_tabla_negativas() {
    global $wpdb;
    $table = $wpdb->prefix . 'ruteo_negativas';
    $charset_collate = $wpdb->get_charset_collate();

    // IMPORTANTE: dbDelta() requiere "CREATE TABLE" SIN "IF NOT EXISTS" y (idealmente)
    // una columna por linea, o no detecta correctamente el nombre de la tabla ni las
    // columnas faltantes existentes, y nunca llega a agregar las columnas nuevas via ALTER.
    $sql = "CREATE TABLE $table (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        proceso VARCHAR(255),
        cm_localidad VARCHAR(100),
        contratista VARCHAR(150),
        sub_contratista VARCHAR(150),
        relacionado_a VARCHAR(10),
        lugar_trabajo VARCHAR(150),
        fecha DATE,
        hora_inicio VARCHAR(20),
        hora_fin VARCHAR(20),
        total_horas VARCHAR(30),
        supervisor_operativo_nombre VARCHAR(150),
        trabajador_reportante VARCHAR(150),
        razones_negativa TEXT,
        cliente_nombre VARCHAR(150),
        cliente_logo LONGTEXT,
        foto1_url LONGTEXT,
        foto2_url LONGTEXT,
        acciones_correctivas TEXT,
        acuerdo_inseguro VARCHAR(5),
        medidas_correctivas TEXT,
        satisface_negativa VARCHAR(5),
        reinicia_labores VARCHAR(5),
        fecha_reinicio DATE,
        hora_reinicio VARCHAR(20),
        supervisor_seguridad_nombre VARCHAR(150),
        observaciones_seguridad TEXT,
        hse_nombre VARCHAR(150),
        dictamen_hse TEXT,
        firma_tecnico_user VARCHAR(150),
        firma_tecnico_fecha DATETIME,
        firma_tecnico_img LONGTEXT,
        firma_sup_operativo_user VARCHAR(150),
        firma_sup_operativo_fecha DATETIME,
        firma_sup_operativo_img LONGTEXT,
        firma_sup_seguridad_user VARCHAR(150),
        firma_sup_seguridad_fecha DATETIME,
        firma_sup_seguridad_img LONGTEXT,
        firma_hse_user VARCHAR(150),
        firma_hse_fecha DATETIME,
        firma_hse_img LONGTEXT,
        estado VARCHAR(30) DEFAULT 'pendiente_tecnico',
        creado_por VARCHAR(150),
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}
register_activation_hook( __FILE__, 'ruteo_crear_tabla_negativas' );
register_activation_hook( __FILE__, array( 'WPRuteoApp', 'activar_cuentas_prueba' ) );

new WPRuteoApp();