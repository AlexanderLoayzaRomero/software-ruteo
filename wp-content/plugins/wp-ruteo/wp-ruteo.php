<?php
/**
 * Plugin Name: Aplicacion de Ruteo
 * Description: Plugin para recopilar datos y fotos en campo, conectandose con Google Sheets.
 * Version: 1.1
 * Author: Antigravity
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class WPRuteoApp {

    // URL DEL WEBHOOK DE GOOGLE SHEETS
    public $webhook_url = 'https://script.google.com/macros/s/AKfycbxfaGZvLuPw9eeOtCjcZ6H4BKbr3xlC0YZteFZIMeoP0dj1WzuAVNvluO1xaSYwQrwY5g/exec';

    public function __construct() {
        $this->register_roles();
        add_shortcode( 'formulario_ruteo', array( $this, 'render_form' ) );
        add_shortcode( 'portal_ruteo', array( $this, 'render_portal' ) );
        add_shortcode( 'login_ruteo', array( $this, 'render_login' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_ajax_ruteo_submit', array( $this, 'handle_ajax_submit' ) );
        add_action( 'wp_ajax_nopriv_ruteo_submit', array( $this, 'handle_ajax_submit' ) );
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
    }

    public function register_roles() {
        add_role( 'ruteo_admin', 'Administrador Ruteo', array(
            'read'                  => true,
            'ruteo_admin_access'    => true,
            'ruteo_worker_access'   => true,
        ) );
        add_role( 'ruteo_worker', 'Operario Ruteo', array(
            'read'                  => true,
            'ruteo_worker_access'   => true,
        ) );
    }

    public function enqueue_assets() {
        if ( wp_script_is( 'wp-ruteo-app', 'enqueued' ) ) {
            return;
        }
        wp_enqueue_style( 'wp-ruteo-style', plugin_dir_url( __FILE__ ) . 'assets/css/style.css', array(), '1.6.0' );
        wp_enqueue_script( 'wp-ruteo-app', plugin_dir_url( __FILE__ ) . 'assets/js/app.js', array( 'jquery' ), '1.6.0', true );

        $current_user = wp_get_current_user();
        $is_logged_in = is_user_logged_in();
        $user_role    = 'guest';
        $is_admin     = false;

        if ( $is_logged_in ) {
            if ( in_array( 'administrator', (array) $current_user->roles, true ) || in_array( 'ruteo_admin', (array) $current_user->roles, true ) ) {
                $user_role = 'admin';
                $is_admin  = true;
            } elseif ( in_array( 'ruteo_worker', (array) $current_user->roles, true ) ) {
                $user_role = 'worker';
            } else {
                $user_role = 'user';
            }
        }

        wp_localize_script( 'wp-ruteo-app', 'wpRuteoAjax', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'ruteo_submit_nonce' ),
            'webhook' => $this->webhook_url,
            'user'    => array(
                'isLoggedIn'  => $is_logged_in,
                'username'    => $is_logged_in ? $current_user->user_login : '',
                'displayName' => $is_logged_in ? $current_user->display_name : 'Invitado',
                'email'       => $is_logged_in ? $current_user->user_email : '',
                'role'        => $user_role,
                'isAdmin'     => $is_admin,
            ),
        ) );
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

    /**
     * Proxy PHP -> Google Apps Script (doGet)
     * El browser no puede llamar directamente a GAS por restricciones CORS/redirect.
     * WordPress hace la llamada server-side y retorna el JSON limpio.
     */
    public function handle_ajax_get_registros() {
        check_ajax_referer( 'ruteo_submit_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array(
                'message' => 'Acceso denegado. Debes iniciar sesion para consultar registros.'
            ) );
            return;
        }

        set_time_limit( 25 );

        $body = '';

        // Metodo 1: wp_remote_get (estandar WordPress)
        $response = wp_remote_get( $this->webhook_url, array(
            'timeout'     => 20,
            'redirection' => 5,
            'httpversion' => '1.1',
            'sslverify'   => false,
            'user-agent'  => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        ) );

        if ( ! is_wp_error( $response ) ) {
            $code = wp_remote_retrieve_response_code( $response );
            $body = wp_remote_retrieve_body( $response );
            if ( $code === 200 && ! empty( $body ) ) {
                $json = json_decode( $body, true );
                if ( json_last_error() === JSON_ERROR_NONE ) {
                    wp_send_json_success( $json );
                    return;
                }
            }
        } else {
            error_log( '[Ruteo] wp_remote_get fallo: ' . $response->get_error_message() );
        }

        // Metodo 2: file_get_contents con stream context (alternativa si wp_remote_get falla en Docker)
        if ( empty( $body ) && ini_get( 'allow_url_fopen' ) ) {
            try {
                $opts = array(
                    'http' => array(
                        'method'          => 'GET',
                        'timeout'         => 15,
                        'ignore_errors'   => true,
                        'user_agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    ),
                    'ssl' => array(
                        'verify_peer'      => false,
                        'verify_peer_name' => false,
                    ),
                );
                $context = stream_context_create( $opts );
                $body = @file_get_contents( $this->webhook_url, false, $context );
                if ( ! empty( $body ) ) {
                    $json = json_decode( $body, true );
                    if ( json_last_error() === JSON_ERROR_NONE ) {
                        wp_send_json_success( $json );
                        return;
                    }
                }
            } catch ( Exception $e ) {
                error_log( '[Ruteo] file_get_contents fallo: ' . $e->getMessage() );
            }
        }

        // Metodo 3: cURL directo (fallback para entornos donde los metodos de WordPress fallan)
        if ( empty( $body ) && function_exists( 'curl_init' ) ) {
            try {
                $ch = curl_init();
                curl_setopt_array( $ch, array(
                    CURLOPT_URL            => $this->webhook_url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT        => 15,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS      => 5,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    CURLOPT_HTTPGET        => true,
                ) );
                $body = curl_exec( $ch );
                $httpCode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
                curl_close( $ch );
                if ( $httpCode === 200 && ! empty( $body ) ) {
                    $json = json_decode( $body, true );
                    if ( json_last_error() === JSON_ERROR_NONE ) {
                        wp_send_json_success( $json );
                        return;
                    }
                }
            } catch ( Exception $e ) {
                error_log( '[Ruteo] cURL fallo: ' . $e->getMessage() );
            }
        }

        // Ambos metodos fallaron
        $error_msg = 'No se pudo conectar con Google Sheets.';
        if ( is_wp_error( $response ) ) {
            $error_msg .= ' (wp_remote_get: ' . $response->get_error_message() . ')';
        } elseif ( empty( $body ) ) {
            $error_msg .= ' (servidor no respondio)';
        } else {
            $json = json_decode( $body, true );
            if ( json_last_error() !== JSON_ERROR_NONE ) {
                $error_msg .= ' (respuesta invalida: ' . substr( $body, 0, 150 ) . ')';
            } else {
                $error_msg .= ' (error desconocido)';
            }
        }

        wp_send_json_error( array( 'message' => $error_msg ) );
    }

    /**
     * Proxy de imagen: descarga imagen de URL externa y retorna base64.
     * Permite embeber fotos de WordPress media en PDFs generados en el browser.
     */
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
        
        // --- FIX PARA IMAGENES LOCALES EN DOCKER ---
        $upload_dir = wp_upload_dir();
        $base_url = $upload_dir['baseurl'];
        $base_dir = $upload_dir['basedir'];
        
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
        // -------------------------------------------
        $parsed_host = parse_url( $url, PHP_URL_HOST );
        $home_host   = parse_url( home_url(), PHP_URL_HOST );
        $ok          = false;
        if ( $parsed_host ) {
            if ( $parsed_host === $home_host || strpos( $parsed_host, 'google.com' ) !== false || strpos( $parsed_host, 'googleusercontent.com' ) !== false || strpos( $parsed_host, 'gstatic.com' ) !== false ) {
                $ok = true;
            }
        }
        if ( ! $ok ) {
            wp_send_json_error( array( 'message' => 'Dominio no permitido' ) );
            return;
        }

        // Intentar primero con thumbnail (mas rapido y confiable)
        $driveMatch = array();
        if ( preg_match( '/[?&]id=([a-zA-Z0-9_-]+)/', $url, $driveMatch ) ) {
            $fileId = $driveMatch[1];
            $thumbUrl = 'https://drive.google.com/thumbnail?id=' . $fileId . '&sz=s800';
            $response = wp_remote_get( $thumbUrl, array(
                'timeout'   => 20,
                'sslverify' => false,
                'redirection' => 3,
            ) );
            if ( ! is_wp_error( $response ) ) {
                $code = wp_remote_retrieve_response_code( $response );
                $body = wp_remote_retrieve_body( $response );
                $type = wp_remote_retrieve_header( $response, 'content-type' );
                if ( $code === 200 && strpos( $type, 'image' ) !== false && strlen( $body ) > 1000 ) {
                    $type = strtok( $type, ';' );
                    wp_send_json_success( array(
                        'dataUrl' => 'data:' . $type . ';base64,' . base64_encode( $body ),
                    ) );
                    return;
                }
            }
        }

        // Fallback: descarga directa
        $response = wp_remote_get( $url, array(
            'timeout'   => 30,
            'sslverify' => false,
            'redirection' => 5,
        ) );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( array( 'message' => $response->get_error_message() ) );
            return;
        }

        $body = wp_remote_retrieve_body( $response );
        $code = wp_remote_retrieve_response_code( $response );
        $type = wp_remote_retrieve_header( $response, 'content-type' );

        if ( $code !== 200 ) {
            wp_send_json_error( array( 'message' => 'HTTP ' . $code . ' al descargar imagen' ) );
            return;
        }

        if ( strpos( $type, 'image' ) === false || strlen( $body ) < 100 ) {
            wp_send_json_error( array( 'message' => 'No es una imagen valida, type: ' . $type . ', size: ' . strlen($body) ) );
            return;
        }

        $type = strtok( $type, ';' );

        wp_send_json_success( array(
            'dataUrl' => 'data:' . $type . ';base64,' . base64_encode( $body ),
        ) );
    }

    /**
     * Proxy para subir un documento generado a Google Drive
     */
    public function handle_upload_document() {
        check_ajax_referer( 'ruteo_submit_nonce', 'nonce' );
        $json = file_get_contents('php://input');
        error_log("Upload Document JSON length: " . strlen($json));
        if (empty($json)) {
            wp_send_json_error( array( 'message' => 'El cuerpo de la solicitud JSON esta vacio (php://input).' ) );
            return;
        }
        
        $response = wp_remote_post( $this->webhook_url, array(
            'body'    => $json,
            'headers' => array( 'Content-Type' => 'application/json' ),
            'timeout' => 45 // Mayor tiempo para documentos
        ) );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( array( 'message' => 'Error al conectar con Google Sheets.' ) );
            return;
        }

        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
        
        if ($result === null) {
            wp_send_json_error( array( 'message' => 'Respuesta no valida de Google: ' . substr(strip_tags($body), 0, 150) ) );
            return;
        }
        
        if (isset($result['status']) && $result['status'] === 'error') {
            wp_send_json_error( array( 'message' => $result['message'] ) );
            return;
        }

        wp_send_json_success( $result );
    }

    public function handle_ajax_submit() {
        check_ajax_referer( 'ruteo_submit_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array(
                'message' => 'Acceso denegado. Debes iniciar sesion para enviar datos de ruteo.'
            ) );
            return;
        }

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

        // Procesar fotos a Base64 sin guardarlas en WordPress
        $photo_data = array();
        foreach ( array( 'foto1', 'foto2' ) as $file_key ) {
            if ( ! empty( $_FILES[$file_key]['tmp_name'] ) ) {
                $tmp_file = $_FILES[$file_key]['tmp_name'];
                
                $type = mime_content_type($tmp_file);
                $content = file_get_contents($tmp_file);
                
                // Formato data URL base64
                $base64 = 'data:' . $type . ';base64,' . base64_encode($content);
                $photo_data[] = $base64;
                
                @unlink($tmp_file);
            } else {
                $photo_data[] = '';
            }
        }

        $data['foto1_base64'] = $photo_data[0];
        $data['foto2_base64'] = $photo_data[1];

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

        wp_send_json_success( array(
            'message' => 'Datos enviados correctamente.',
            'time'    => current_time( 'mysql' ),
        ) );
    }

    public function handle_ajax_login() {
        check_ajax_referer( 'ruteo_submit_nonce', 'nonce' );

        $username = isset( $_POST['username'] ) ? sanitize_user( wp_unslash( $_POST['username'] ) ) : '';
        $password = isset( $_POST['password'] ) ? $_POST['password'] : '';
        $remember = ! empty( $_POST['remember'] );

        if ( empty( $username ) || empty( $password ) ) {
            wp_send_json_error( array( 'message' => 'Usuario y clave son requeridos.' ) );
            return;
        }

        $creds = array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => $remember,
        );

        $user = wp_signon( $creds, is_ssl() );

        if ( is_wp_error( $user ) ) {
            wp_send_json_error( array( 'message' => 'Credenciales invalidas. Revisa usuario y clave.' ) );
            return;
        }

        $is_admin = in_array( 'administrator', (array) $user->roles, true ) || in_array( 'ruteo_admin', (array) $user->roles, true );
        $role     = $is_admin ? 'admin' : ( in_array( 'ruteo_worker', (array) $user->roles, true ) ? 'worker' : 'user' );

        wp_send_json_success( array(
            'message' => 'Inicio de sesion exitoso.',
            'user'    => array(
                'username'    => $user->user_login,
                'displayName' => $user->display_name,
                'email'       => $user->user_email,
                'role'        => $role,
                'isAdmin'     => $is_admin,
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
            'role__in' => array( 'ruteo_admin', 'ruteo_worker', 'administrator' ),
            'orderby'  => 'registered',
            'order'    => 'DESC',
        ) );

        $user_list = array();
        foreach ( $wp_users as $u ) {
            $roles      = (array) $u->roles;
            $role_label = 'Worker';
            if ( in_array( 'administrator', $roles, true ) || in_array( 'ruteo_admin', $roles, true ) ) {
                $role_label = 'Admin';
            }
            $user_list[] = array(
                'id'          => $u->ID,
                'username'    => $u->user_login,
                'displayName' => $u->display_name,
                'email'       => $u->user_email,
                'role'        => $role_label,
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
            wp_send_json_error( array( 'message' => 'Acceso denegado. Se requieren permisos de Administrador.' ) );
            return;
        }

        $username     = isset( $_POST['username'] ) ? sanitize_user( wp_unslash( $_POST['username'] ) ) : '';
        $email        = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
        $password     = isset( $_POST['password'] ) ? $_POST['password'] : '';
        $display_name = isset( $_POST['display_name'] ) ? sanitize_text_field( wp_unslash( $_POST['display_name'] ) ) : '';
        $role         = isset( $_POST['role'] ) ? sanitize_text_field( wp_unslash( $_POST['role'] ) ) : 'worker';

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

        $wp_role = ( $role === 'admin' || $role === 'ruteo_admin' ) ? 'ruteo_admin' : 'ruteo_worker';

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

        wp_send_json_success( array(
            'message' => 'Usuario creado exitosamente.',
            'user_id' => $user_id,
        ) );
    }

    public function handle_ajax_delete_user() {
        check_ajax_referer( 'ruteo_submit_nonce', 'nonce' );

        $current_user = wp_get_current_user();
        $is_admin     = in_array( 'administrator', (array) $current_user->roles, true ) || in_array( 'ruteo_admin', (array) $current_user->roles, true );

        if ( ! $is_admin ) {
            wp_send_json_error( array( 'message' => 'Acceso denegado. Se requieren permisos de Administrador.' ) );
            return;
        }

        $user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;

        if ( $user_id === 0 ) {
            wp_send_json_error( array( 'message' => 'ID de usuario invalido.' ) );
            return;
        }

        if ( $user_id === $current_user->ID ) {
            wp_send_json_error( array( 'message' => 'No puedes eliminar tu propia cuenta mientras estas logueado.' ) );
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
}

new WPRuteoApp();
