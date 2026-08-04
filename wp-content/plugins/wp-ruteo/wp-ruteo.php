<?php
/**
 * Plugin Name: Aplicacion de Ruteo
 * Description: Plugin para recopilar datos y fotos en campo, consumo de materiales y gestion de usuarios.
 * Version: 2.0.0
 * Author: Antigravity
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class WPRuteoApp {

    // URL DEL WEBHOOK DE GOOGLE SHEETS
    public $webhook_url = 'https://script.google.com/macros/s/AKfycbwA3yeXPpl2vNYy9E4nu-LyNc-4FyzA7D6w-MxaiwrKzhWsyRh00Kb5v4WXqJy_Yci4Xg/exec';

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
        add_action( 'wp_ajax_ruteo_update_profile', array( $this, 'handle_ajax_update_profile' ) );
        add_action( 'wp_ajax_ruteo_negativa_guardar', array( $this, 'handle_ajax_negativa_guardar' ) );
        add_action( 'wp_ajax_nopriv_ruteo_negativa_guardar', array( $this, 'handle_ajax_negativa_guardar' ) );
        add_action( 'wp_ajax_ruteo_negativa_listar', array( $this, 'handle_ajax_negativa_listar' ) );
        add_action( 'wp_ajax_nopriv_ruteo_negativa_listar', array( $this, 'handle_ajax_negativa_listar' ) );
        
        // Logo del sistema (solo Admin)
        add_action( 'wp_ajax_ruteo_update_site_logo', array( $this, 'handle_ajax_update_site_logo' ) );

        // Consumo de Materiales AJAX Endpoints
        add_action( 'wp_ajax_ruteo_save_materiales', array( $this, 'handle_ajax_save_materiales' ) );
        add_action( 'wp_ajax_nopriv_ruteo_save_materiales', array( $this, 'handle_ajax_save_materiales' ) );
        add_action( 'wp_ajax_ruteo_get_materiales', array( $this, 'handle_ajax_get_materiales' ) );
        add_action( 'wp_ajax_nopriv_ruteo_get_materiales', array( $this, 'handle_ajax_get_materiales' ) );
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
        wp_enqueue_style( 'wp-ruteo-style', plugin_dir_url( __FILE__ ) . 'assets/css/style.css', array(), '2.0.0' );
        wp_enqueue_script( 'wp-ruteo-app', plugin_dir_url( __FILE__ ) . 'assets/js/app.js', array( 'jquery' ), '2.0.0', true );

        $current_user = wp_get_current_user();
        $is_logged_in = is_user_logged_in();
        $user_role    = 'guest';
        $is_admin     = false;

        $phone       = '';
        $pm_assigned = '';
        $avatar      = '';
        $avatar      = get_user_meta( $current_user->ID, 'ruteo_avatar', true );
        $negativa_rol = get_user_meta( $current_user->ID, 'ruteo_negativa_rol', true );

        if ( $is_logged_in ) {
            $phone       = get_user_meta( $current_user->ID, 'ruteo_phone', true );
            $pm_assigned = get_user_meta( $current_user->ID, 'ruteo_pm_assigned', true );
            $avatar      = get_user_meta( $current_user->ID, 'ruteo_avatar', true );

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
            'ajaxurl'  => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'ruteo_submit_nonce' ),
            'webhook'  => $this->webhook_url,
            'siteLogo' => get_option( 'ruteo_site_logo', '' ),
            'user'    => array(
                'id'          => $is_logged_in ? $current_user->ID : 0,
                'isLoggedIn'  => $is_logged_in,
                'username'    => $is_logged_in ? $current_user->user_login : '',
                'isAdmin'     => $is_admin,
                'negativaRol' => $negativa_rol ?: '',
                'displayName' => $is_logged_in ? $current_user->display_name : 'Invitado',
                'email'       => $is_logged_in ? $current_user->user_email : '',
                'phone'       => $phone,
                'pmAssigned'  => $pm_assigned,
                'avatar'      => $avatar,
                'role'        => $user_role,
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

    public function handle_ajax_get_registros() {
        check_ajax_referer( 'ruteo_submit_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array(
                'message' => 'Acceso denegado. Debes iniciar sesion para consultar registros.'
            ) );
            return;
        }

        set_time_limit( 35 );
        nocache_headers();
        header( 'Cache-Control: no-cache, no-store, must-revalidate, max-age=0' );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        $target_url = add_query_arg( '_ts', microtime( true ), $this->webhook_url );

        $response = wp_remote_get( $target_url, array(
            'timeout'     => 30,
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
                    wp_send_json_success( $json );
                    return;
                }
            }
        }

        wp_send_json_error( array( 'message' => 'No se pudo conectar con el servidor de registros.' ) );
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

        $response = wp_remote_get( $url, array(
            'timeout'   => 30,
            'sslverify' => false,
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

        if ( $this->webhook_url ) {
            wp_remote_post( $this->webhook_url, array(
                'body'    => json_encode( $data ),
                'headers' => array( 'Content-Type' => 'application/json' ),
                'timeout' => 15
            ) );
        }

        wp_send_json_success( array(
            'message' => 'Datos de ruteo guardados correctamente.',
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

        wp_set_current_user( $user->ID );
        wp_set_auth_cookie( $user->ID, $remember );

        $is_admin = in_array( 'administrator', (array) $user->roles, true ) || in_array( 'ruteo_admin', (array) $user->roles, true );
        $role     = $is_admin ? 'admin' : ( in_array( 'ruteo_worker', (array) $user->roles, true ) ? 'worker' : 'user' );

        $phone        = get_user_meta( $user->ID, 'ruteo_phone', true );
        $pm_assigned  = get_user_meta( $user->ID, 'ruteo_pm_assigned', true );
        $avatar       = get_user_meta( $user->ID, 'ruteo_avatar', true );
        $negativa_rol = get_user_meta( $user->ID, 'ruteo_negativa_rol', true );

        wp_send_json_success( array(
            'message' => 'Inicio de sesion exitoso.',
            'user'    => array(
                'id'          => $user->ID,
                'username'    => $user->user_login,
                'displayName' => $user->display_name,
                'email'       => $user->user_email,
                'phone'       => $phone,
                'pmAssigned'  => $pm_assigned,
                'avatar'      => $avatar,
                'role'        => $role,
                'isAdmin'     => $is_admin,
                'negativaRol' => $negativa_rol ?: '',
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
                'phone'       => get_user_meta( $u->ID, 'ruteo_phone', true ),
                'pmAssigned'  => get_user_meta( $u->ID, 'ruteo_pm_assigned', true ),
                'avatar'      => get_user_meta( $u->ID, 'ruteo_avatar', true ),
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
            wp_send_json_error( array( 'message' => 'Acceso denegado. Permiso requerido.' ) );
            return;
        }

        $username     = isset( $_POST['username'] ) ? sanitize_user( wp_unslash( $_POST['username'] ) ) : '';
        $email        = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
        $password     = isset( $_POST['password'] ) ? $_POST['password'] : '';
        $display_name = isset( $_POST['display_name'] ) ? sanitize_text_field( wp_unslash( $_POST['display_name'] ) ) : '';
        $role         = isset( $_POST['role'] ) ? sanitize_text_field( wp_unslash( $_POST['role'] ) ) : 'worker';
        $phone        = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
        $pm_assigned  = isset( $_POST['pm_assigned'] ) ? sanitize_text_field( wp_unslash( $_POST['pm_assigned'] ) ) : '';

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

        if ( ! empty( $phone ) ) {
            update_user_meta( $user_id, 'ruteo_phone', $phone );
        }
        if ( ! empty( $pm_assigned ) ) {
            update_user_meta( $user_id, 'ruteo_pm_assigned', $pm_assigned );
        }

        if ( ! empty( $_FILES['avatar']['tmp_name'] ) ) {
            $tmp_file = $_FILES['avatar']['tmp_name'];
            $type     = mime_content_type($tmp_file);
            $content  = file_get_contents($tmp_file);
            $base64   = 'data:' . $type . ';base64,' . base64_encode($content);
            update_user_meta( $user_id, 'ruteo_avatar', $base64 );
            @unlink($tmp_file);
        }

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

        if ( ! empty( $display_name ) ) {
            wp_update_user( array( 'ID' => $user_id, 'display_name' => $display_name ) );
        }
        update_user_meta( $user_id, 'ruteo_phone', $phone );
        update_user_meta( $user_id, 'ruteo_pm_assigned', $pm_assigned );

        if ( ! empty( $_FILES['avatar']['tmp_name'] ) ) {
            $tmp_file = $_FILES['avatar']['tmp_name'];
            $type     = mime_content_type($tmp_file);
            $content  = file_get_contents($tmp_file);
            $base64   = 'data:' . $type . ';base64,' . base64_encode($content);
            update_user_meta( $user_id, 'ruteo_avatar', $base64 );
            @unlink($tmp_file);
        }

        wp_send_json_success( array( 'message' => 'Perfil actualizado correctamente.' ) );
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
        update_option( 'wp_ruteo_materiales_store', $materiales );

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

        if ( $etapa === 'tecnico' ) {
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
                'firma_tecnico_user'          => $current_user->display_name,
                'firma_tecnico_fecha'         => $now,
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

            $wpdb->insert( $table, $campos );
            $id = $wpdb->insert_id;

        } elseif ( $etapa === 'supervisor' ) {
            $wpdb->update( $table, array(
                'acciones_correctivas'      => sanitize_textarea_field( wp_unslash( $_POST['acciones_correctivas'] ?? '' ) ),
                'acuerdo_inseguro'          => sanitize_text_field( wp_unslash( $_POST['acuerdo_inseguro'] ?? '' ) ),
                'firma_sup_operativo_user'  => $current_user->display_name,
                'firma_sup_operativo_fecha' => $now,
                'estado'                    => 'pendiente_seguridad',
            ), array( 'id' => $id ) );

        } elseif ( $etapa === 'seguridad' ) {
            $wpdb->update( $table, array(
                'firma_sup_seguridad_user'  => $current_user->display_name,
                'firma_sup_seguridad_fecha' => $now,
                'estado'                    => 'pendiente_hse',
            ), array( 'id' => $id ) );

        } elseif ( $etapa === 'hse' ) {
            $wpdb->update( $table, array(
                'firma_hse_user'  => $current_user->display_name,
                'firma_hse_fecha' => $now,
                'estado'          => 'completado',
            ), array( 'id' => $id ) );
        }

        $registro = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id ), ARRAY_A );

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
        global $wpdb;
        $table     = $wpdb->prefix . 'ruteo_negativas';
        $registros = $wpdb->get_results( "SELECT * FROM $table ORDER BY id DESC", ARRAY_A );
        wp_send_json_success( array( 'registros' => $registros ) );
    }
}

function ruteo_crear_tabla_negativas() {
    global $wpdb;
    $table = $wpdb->prefix . 'ruteo_negativas';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table (
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
        foto1_url LONGTEXT,
        foto2_url LONGTEXT,
        acciones_correctivas TEXT,
        acuerdo_inseguro VARCHAR(5),
        firma_tecnico_user VARCHAR(150), firma_tecnico_fecha DATETIME,
        firma_sup_operativo_user VARCHAR(150), firma_sup_operativo_fecha DATETIME,
        firma_sup_seguridad_user VARCHAR(150), firma_sup_seguridad_fecha DATETIME,
        firma_hse_user VARCHAR(150), firma_hse_fecha DATETIME,
        estado VARCHAR(30) DEFAULT 'pendiente_tecnico',
        creado_por VARCHAR(150),
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}
register_activation_hook( __FILE__, 'ruteo_crear_tabla_negativas' );

new WPRuteoApp();