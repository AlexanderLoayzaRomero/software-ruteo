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
    public $webhook_url = 'https://script.google.com/macros/s/AKfycbzwhHt13NqHOWEMbtoQhD6TjNs4X-S1SzNySiPzJJgCdqE1oxqWA7l8i3ZI-2QoivjB/exec';
    private $assets_enqueued = false;

    public function __construct() {
        $this->register_roles();
        add_action( 'template_redirect', array( $this, 'fuerza_redireccion_portal' ) );
        add_filter( 'template_include', array( $this, 'cargar_plantilla_portal_directa' ), 999 );
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
        add_filter( 'auth_cookie_expiration', array( $this, 'extender_duracion_sesion' ), 10, 3 );
        add_action( 'wp_logout', array( $this, 'ruteo_redireccionar_logout' ) );
        add_action( 'wp_ajax_ruteo_login', array( $this, 'handle_ajax_login' ) );
        add_action( 'wp_ajax_nopriv_ruteo_login', array( $this, 'handle_ajax_login' ) );
        add_action( 'wp_ajax_ruteo_logout', array( $this, 'handle_ajax_logout' ) );
        add_action( 'wp_ajax_nopriv_ruteo_logout', array( $this, 'handle_ajax_logout' ) );
        add_action( 'wp_ajax_ruteo_recover_password', array( $this, 'handle_ajax_recover_password' ) );
        add_action( 'wp_ajax_nopriv_ruteo_recover_password', array( $this, 'handle_ajax_recover_password' ) );
        add_action( 'wp_ajax_ruteo_get_users', array( $this, 'handle_ajax_get_users' ) );
        add_action( 'wp_ajax_ruteo_create_user', array( $this, 'handle_ajax_create_user' ) );
        add_action( 'wp_ajax_ruteo_delete_user', array( $this, 'handle_ajax_delete_user' ) );
        add_action( 'wp_ajax_ruteo_update_profile', array( $this, 'handle_ajax_update_profile' ) );
        add_action( 'wp_ajax_ruteo_negativa_guardar', array( $this, 'handle_ajax_negativa_guardar' ) );
        add_action( 'wp_ajax_nopriv_ruteo_negativa_guardar', array( $this, 'handle_ajax_negativa_guardar' ) );
        add_action( 'wp_ajax_ruteo_negativa_listar', array( $this, 'handle_ajax_negativa_listar' ) );
        add_action( 'wp_ajax_ruteo_negativa_editar', array( $this, 'handle_ajax_negativa_editar' ) );
        add_action( 'wp_ajax_ruteo_negativa_eliminar', array( $this, 'handle_ajax_negativa_eliminar' ) );
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

        // Empresas AJAX Endpoints (multiempresa)
        add_action( 'wp_ajax_ruteo_get_empresas', array( $this, 'handle_ajax_get_empresas' ) );
        add_action( 'wp_ajax_ruteo_save_empresa', array( $this, 'handle_ajax_save_empresa' ) );
        add_action( 'wp_ajax_ruteo_delete_empresa', array( $this, 'handle_ajax_delete_empresa' ) );

        // Audit Logs AJAX Endpoints
        add_action( 'wp_ajax_ruteo_get_logs', array( $this, 'handle_ajax_get_logs' ) );
        add_action( 'wp_ajax_nopriv_ruteo_get_logs', array( $this, 'handle_ajax_get_logs' ) );

        // Image Base64 Proxy Endpoint
        add_action( 'wp_ajax_ruteo_get_image_base64', array( $this, 'handle_ajax_get_image_base64' ) );
        

        
    }

    /**
     * Devuelve el empresa_id del usuario actual, o 0 si es Administrador General
     * (que no esta atado a ninguna empresa especifica).
     */
    public static function get_empresa_id_usuario( $user_id = 0 ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }
        return (int) get_user_meta( $user_id, 'ruteo_empresa_id', true );
    }

    /**
     * Devuelve el nombre de una empresa a partir de su ID.
     * Se usa para mostrar el nombre de la empresa junto al rol del usuario
     * (ej: "Administrador (Alicorp)") sin tener que escribirlo a mano.
     */
    public static function get_empresa_nombre( $empresa_id ) {
        $empresa_id = absint( $empresa_id );
        if ( ! $empresa_id ) {
            return '';
        }

        global $wpdb;
        $table  = $wpdb->prefix . 'ruteo_empresas';
        $nombre = $wpdb->get_var( $wpdb->prepare( "SELECT nombre FROM $table WHERE id = %d", $empresa_id ) );

        return $nombre ? $nombre : '';
    }

    /**
     * Devuelve el logo (base64) de una empresa a partir de su ID.
     * Se usa para que el PDF de Negativa muestre el logo de la empresa
     * del usuario logueado (BCP, Alicorp, etc.) en vez del logo de un
     * "cliente" generico, ya que ahora el campo Cliente/Empresa Principal
     * del formulario de Negativa se autocompleta con la empresa del usuario.
     */
    public static function get_empresa_logo( $empresa_id ) {
        $empresa_id = absint( $empresa_id );
        if ( ! $empresa_id ) {
            return '';
        }

        global $wpdb;
        $table = $wpdb->prefix . 'ruteo_empresas';
        $logo  = $wpdb->get_var( $wpdb->prepare( "SELECT logo FROM $table WHERE id = %d", $empresa_id ) );

        return $logo ? $logo : '';
    }

    /**
     * Verifica si el usuario actual es Administrador General (ve todas las empresas).
     */
    public static function es_super_admin( $user = null ) {
        if ( ! $user ) {
            $user = wp_get_current_user();
        }
        return in_array( 'administrator', (array) $user->roles, true )
            || in_array( 'ruteo_super_admin', (array) $user->roles, true );
    }

    /**
 * Obtiene el ID de empresa del usuario actual.
 *
 * El Administrador General no pertenece a una empresa concreta.
 * Los demás usuarios deben tener ruteo_empresa_id.
 */
public static function get_user_empresa_id( $user_id = 0 ) {

    $user_id = $user_id ? absint( $user_id ) : get_current_user_id();

    if ( ! $user_id ) {
        return 0;
    }

    $user = get_userdata( $user_id );

    if ( ! $user ) {
        return 0;
    }

    // El Administrador General tiene acceso global.
    if ( self::es_super_admin( $user ) ) {
        return 0;
    }

    return absint( get_user_meta( $user_id, 'ruteo_empresa_id', true ) );
}

    
    /**
 * Comprueba si un usuario puede acceder a una empresa.
 *
 * El Administrador General puede acceder globalmente.
 * Los demás usuarios únicamente a su propia empresa.
 */
public static function user_can_access_empresa( $empresa_id, $user_id = 0 ) {

    $empresa_id = absint( $empresa_id );
    $user_id    = $user_id ? absint( $user_id ) : get_current_user_id();

    if ( ! $empresa_id || ! $user_id ) {
        return false;
    }

    $user = get_userdata( $user_id );

    if ( ! $user ) {
        return false;
    }

    // Administrador General: acceso global.
    if ( self::es_super_admin( $user ) ) {
        return true;
    }

    $user_empresa_id = self::get_user_empresa_id( $user_id );

    return $user_empresa_id === $empresa_id;
}

    public function register_roles() {
        add_role( 'ruteo_super_admin', 'Administrador General', array(
            'read'                  => true,
            'ruteo_super_admin_access' => true,
            'ruteo_admin_access'    => true,
            'ruteo_worker_access'   => true,
        ) );
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

    public function fuerza_redireccion_portal() {
        if ( is_admin() || wp_doing_ajax() ) {
            return;
        }

        $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '/';
        $current_path = rtrim( parse_url( $request_uri, PHP_URL_PATH ) ?: '/', '/' );

        // Si ya estamos en la portada o raiz del sitio (/), NO ejecutar redirecciones para prevenir ERR_TOO_MANY_REDIRECTS
        if ( empty( $current_path ) || $current_path === '' || $current_path === '/' ) {
            return;
        }

        // Si es una pagina 404 (no encontrada) y no estamos en la raiz, redirigir a la portada
        if ( is_404() ) {
            wp_safe_redirect( home_url( '/' ) );
            exit;
        }

        if ( is_front_page() || is_home() ) {
            $front_id = (int) get_option( 'page_on_front' );
            $portal_page = get_page_by_path( 'portal' );
            if ( ! $portal_page ) {
                $portal_page = get_page_by_path( 'portal-ruteo' );
            }
            if ( ! $portal_page ) {
                $portal_page = get_page_by_path( 'portal-de-ruteo' );
            }
            if ( $portal_page ) {
                if ( (int) $portal_page->ID === $front_id ) {
                    return; // Ya esta cargando el portal como portada, no redirigir a si mismo
                }
                $target_url = get_permalink( $portal_page->ID );
            } else {
                return;
            }
            
            if ( ! empty( $target_url ) && rtrim( $target_url, '/' ) !== rtrim( home_url( '/' ), '/' ) ) {
                wp_safe_redirect( $target_url );
                exit;
            }
        }
    }

    public function cargar_plantilla_portal_directa( $template ) {
        if ( is_admin() || wp_doing_ajax() ) {
            return $template;
        }

        // Forzar la plantilla del portal O&M en cualquier vista publica del sitio evitando el tema por defecto de WordPress
        $portal_file = plugin_dir_path( __FILE__ ) . 'includes/portal-standalone-template.php';
        if ( file_exists( $portal_file ) ) {
            return $portal_file;
        }

        return $template;
    }

    public static function activar_cuentas_prueba() {
        $cuentas = array(
            array(
                'user'         => 'admingeneral',
                'pass'         => defined( 'RUTEO_PASS_ADMIN' ) ? RUTEO_PASS_ADMIN : wp_generate_password( 16 ),
                'name'         => 'Administrador General O&M',
                'email'        => 'admin@software-om.org.pe',
                'role'         => 'ruteo_super_admin',
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
    wp_enqueue_script( 'exceljs-cdn', 'https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.4.0/exceljs.min.js', array(), '4.4.0', true );
    wp_enqueue_script( 'wp-ruteo-app', plugin_dir_url( __FILE__ ) . 'assets/js/app.js', array( 'jquery', 'jspdf-cdn', 'jspdf-autotable-cdn', 'xlsx-cdn', 'exceljs-cdn' ), $js_ver, true );

    $current_user = wp_get_current_user();
    $is_logged_in = is_user_logged_in();
    $user_role    = 'guest';
    $is_admin     = false;
    $is_super_admin  = false;

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

        $is_super_admin = self::es_super_admin( $current_user );
    }

    $clientes_list = get_option( 'ruteo_clientes_list', array() );
    if ( empty( $clientes_list ) || ! is_array( $clientes_list ) ) {
        $clientes_list = array(
            array(
                'id'         => 'CLI-CYMTEL',
                'nombre'     => 'CYMTEL',
                'ruc'        => '20512345678',
                'logo'       => '',
                'empresa_id' => 0,
            )
        );
        update_option( 'ruteo_clientes_list', $clientes_list, false );
    }

    $dash_empresa_id = $is_logged_in ? self::get_user_empresa_id( $current_user->ID ) : 0;

    if ( $is_logged_in && ! $is_super_admin ) {
        $clientes_list = array_values( array_filter( $clientes_list, function( $c ) use ( $dash_empresa_id ) {
            $c_empresa = isset( $c['empresa_id'] ) ? (int) $c['empresa_id'] : 0;
            return $c_empresa === 0 || $c_empresa === $dash_empresa_id;
        } ) );

        $user_count = count( get_users( array(
            'fields'     => 'ID',
            'meta_key'   => 'ruteo_empresa_id',
            'meta_value' => $dash_empresa_id,
        ) ) );
    } else {
        $user_count = count( get_users( array( 'fields' => 'ID' ) ) );
    }

    wp_localize_script( 'wp-ruteo-app', 'wpRuteoAjax', array(
        'ajaxurl'   => admin_url( 'admin-ajax.php' ),
        'nonce'     => wp_create_nonce( 'ruteo_submit_nonce' ),
        'siteLogo'  => get_option( 'ruteo_site_logo', '' ),
        'clientes'  => $clientes_list,
        'userCount' => $user_count,
        'user'    => array(
            'id'          => $is_logged_in ? $current_user->ID : 0,
            'isLoggedIn'  => $is_logged_in,
            'username'    => $is_logged_in ? $current_user->user_login : '',
            'isAdmin'      => $is_admin,
            'isSuperAdmin' => $is_super_admin,
            'empresaNombre' => ( $is_logged_in && ! $is_super_admin ) ? self::get_empresa_nombre( $dash_empresa_id ) : '',
            'empresaLogo'   => ( $is_logged_in && ! $is_super_admin ) ? self::get_empresa_logo( $dash_empresa_id ) : '',
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

        $current_user = wp_get_current_user();
        $empresa_id   = self::get_user_empresa_id( $current_user->ID );
        $args = array( '_ts' => microtime( true ) );
        if ( $empresa_id ) {
            $args['empresa'] = self::get_empresa_nombre( $empresa_id );
        }
        $target_url = add_query_arg( $args, $this->webhook_url );

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

        $empresa_id = self::get_user_empresa_id( get_current_user_id() );
        if ( $empresa_id ) {
            $data['empresa_nombre'] = self::get_empresa_nombre( $empresa_id );
        }

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
                wp_send_json_error( array( 'message' => 'Error de conexion con Google Sheets: ' . $response->get_error_message() ) );
                return;
            } else {
                $code = wp_remote_retrieve_response_code( $response );
                $gas_body = wp_remote_retrieve_body( $response );
                $gas_json = json_decode( $gas_body, true );
                if ( $code !== 200 ) {
                    wp_send_json_error( array( 'message' => 'Google Sheets respondio con codigo HTTP ' . $code ) );
                    return;
                }
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

    public function extender_duracion_sesion( $length, $user_id, $remember ) {
        // Mantener sesion activa por 30 dias (evita cierres de sesion automaticos)
        return 30 * DAY_IN_SECONDS;
    }

    public function handle_ajax_login() {
        if ( ! empty( $_POST['nonce'] ) ) {
            check_ajax_referer( 'ruteo_submit_nonce', 'nonce', false );
        }

        $raw_input = isset( $_POST['username'] ) ? trim( wp_unslash( $_POST['username'] ) ) : '';
        $password  = isset( $_POST['password'] ) ? $_POST['password'] : '';
        $remember  = isset( $_POST['remember'] ) ? ! empty( $_POST['remember'] ) : true;

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

        $secure_cookie = is_ssl() || ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' === strtolower( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) );

        $creds = array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => $remember,
        );

        $user = wp_signon( $creds, $secure_cookie );

        if ( is_wp_error( $user ) ) {
            set_transient( $intentos_key, $intentos + 1, 10 * MINUTE_IN_SECONDS );
            wp_send_json_error( array( 'message' => 'Credenciales invalidas. Revisa usuario o correo y clave.' ) );
            return;
        }

        delete_transient( $intentos_key );

        wp_set_current_user( $user->ID );
        wp_set_auth_cookie( $user->ID, $remember, $secure_cookie );

       $is_admin       = in_array( 'administrator', (array) $user->roles, true ) || in_array( 'ruteo_admin', (array) $user->roles, true );
        $is_super_admin = self::es_super_admin( $user );
        $role           = $is_admin ? 'admin' : ( in_array( 'ruteo_worker', (array) $user->roles, true ) ? 'worker' : 'user' );

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

        $login_empresa_id = self::get_user_empresa_id( $user->ID );
        $empresa_nombre    = ( ! $is_super_admin ) ? self::get_empresa_nombre( $login_empresa_id ) : '';
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
                'role'         => $role,
                'isAdmin'      => $is_admin,
                'isSuperAdmin' => $is_super_admin,
                'empresaNombre' => $empresa_nombre,
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

    public function ruteo_redireccionar_logout() {
        wp_safe_redirect( home_url( '/' ) );
        exit;
    }

    public function handle_ajax_get_users() {
    check_ajax_referer( 'ruteo_submit_nonce', 'nonce' );

    $current_user = wp_get_current_user();

    $is_super_admin   = self::es_super_admin( $current_user );
    $is_company_admin = in_array( 'ruteo_admin', (array) $current_user->roles, true );

    if ( ! $is_super_admin && ! $is_company_admin ) {
        wp_send_json_error(
            array(
                'message' => 'Acceso denegado. Se requieren permisos de Administrador.'
            )
        );
        return;
    }

    $empresa_id = $is_super_admin
        ? 0
        : self::get_user_empresa_id( $current_user->ID );

    if ( ! $is_super_admin && ! $empresa_id ) {
        wp_send_json_error(
            array(
                'message' => 'Tu usuario no tiene una empresa asociada.'
            )
        );
        return;
    }

    $user_query_args = array(
    'role__in' => array(
        'ruteo_super_admin',
        'ruteo_admin',
        'ruteo_sup_operativo',
        'ruteo_sup_hse',
        'ruteo_worker',
        'administrator',
    ),
    'orderby' => 'registered',
    'order'   => 'DESC',
);

    if ( ! $is_super_admin ) {
        $user_query_args['meta_key']   = 'ruteo_empresa_id';
        $user_query_args['meta_value'] = $empresa_id;
    }

    $wp_users = get_users( $user_query_args );

    $user_list = array();
    foreach ( $wp_users as $u ) {
        $roles      = (array) $u->roles;
        $role_label = 'Operario';
        $role_key   = 'ruteo_worker';

        if ( in_array( 'administrator', $roles, true ) || in_array( 'ruteo_super_admin', $roles, true ) ) {
           $role_label = 'Administrador General';
           $role_key   = 'ruteo_super_admin';

        } elseif ( in_array( 'ruteo_admin', $roles, true ) ) {

           $role_label = 'Administrador de Empresa';
           $role_key   = 'ruteo_admin';

        } elseif ( in_array( 'ruteo_sup_operativo', $roles, true ) ) {

           $role_label = 'Supervisor Operativo';
           $role_key   = 'ruteo_sup_operativo';

        } elseif ( in_array( 'ruteo_sup_hse', $roles, true ) ) {

           $role_label = 'Supervisor HSE';
           $role_key   = 'ruteo_sup_hse';

        } elseif ( in_array( 'ruteo_worker', $roles, true ) ) {

          $role_label = 'Operario';
          $role_key   = 'ruteo_worker';
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

    $is_super_admin   = self::es_super_admin( $current_user );
    $is_company_admin = in_array( 'ruteo_admin', (array) $current_user->roles, true );

    if ( ! $is_super_admin && ! $is_company_admin ) {
        wp_send_json_error( array( 'message' => 'Acceso denegado. Permiso requerido.' ) );
        return;
    }

    $input = self::extract_user_input();

    $signer_caps = self::parse_signer_caps( $input['raw_caps'] );
    $wp_role     = self::map_frontend_role_to_wp_role( $input['role'] );

    $empresa_id = $is_super_admin
        ? $input['requested_empresa_id']
        : self::get_user_empresa_id( $current_user->ID );

    if ( ! $empresa_id ) {
        wp_send_json_error( array( 'message' => 'No se pudo determinar la empresa asociada al usuario.' ), 400 );
        return;
    }

    if ( ! self::empresa_existe( $empresa_id ) ) {
        wp_send_json_error( array( 'message' => 'La empresa seleccionada no existe.' ), 400 );
        return;
    }

    if ( ! $is_super_admin && $wp_role === 'ruteo_admin' ) {
        wp_send_json_error( array( 'message' => 'No tienes permisos para crear otro Administrador de Empresa.' ), 403 );
        return;
    }

    if ( $input['edit_id'] > 0 ) {
        self::procesar_edicion_usuario( $input, $wp_role, $empresa_id, $signer_caps, $is_super_admin );
        return;
    }

    self::procesar_creacion_usuario( $input, $wp_role, $empresa_id, $signer_caps );
}


/**
 * Extrae y sanitiza todos los campos recibidos por $_POST.
 */
private static function extract_user_input() {

    return array(
        'edit_id'               => isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : 0,
        'username'              => isset( $_POST['username'] ) ? sanitize_user( wp_unslash( $_POST['username'] ) ) : '',
        'email'                 => isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '',
        'password'              => isset( $_POST['password'] ) ? $_POST['password'] : '',
        'display_name'          => isset( $_POST['display_name'] ) ? sanitize_text_field( wp_unslash( $_POST['display_name'] ) ) : '',
        'role'                  => isset( $_POST['role'] ) ? sanitize_text_field( wp_unslash( $_POST['role'] ) ) : 'ruteo_worker',
        'phone'                 => isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '',
        'pm_assigned'           => isset( $_POST['pm_assigned'] ) ? sanitize_text_field( wp_unslash( $_POST['pm_assigned'] ) ) : '',
        'negativa_rol'          => isset( $_POST['negativa_rol'] ) ? sanitize_text_field( wp_unslash( $_POST['negativa_rol'] ) ) : '',
        'position'              => isset( $_POST['position'] ) ? sanitize_text_field( wp_unslash( $_POST['position'] ) ) : '',
        'requested_empresa_id'  => isset( $_POST['empresa_id'] ) ? absint( $_POST['empresa_id'] ) : 0,
        'raw_caps'              => isset( $_POST['signer_caps'] ) ? $_POST['signer_caps'] : array(),
    );
}


/**
 * Normaliza signer_caps venga como string separado por comas o como array.
 */
private static function parse_signer_caps( $raw_caps ) {

    if ( is_string( $raw_caps ) ) {
        return array_filter( array_map( 'trim', explode( ',', $raw_caps ) ) );
    }

    if ( is_array( $raw_caps ) ) {
        return array_map( 'sanitize_text_field', $raw_caps );
    }

    return array();
}


/**
 * Convierte el rol enviado por el frontend al slug real de rol de WordPress.
 */
private static function map_frontend_role_to_wp_role( $role ) {

    if ( $role === 'admin' || $role === 'ruteo_admin' ) {
        return 'ruteo_admin';
    }

    if ( $role === 'sup_operativo' || $role === 'ruteo_sup_operativo' ) {
        return 'ruteo_sup_operativo';
    }

    if ( $role === 'sup_hse' || $role === 'ruteo_sup_hse' ) {
        return 'ruteo_sup_hse';
    }

    return 'ruteo_worker';
}


/**
 * Verifica que la empresa exista en la tabla ruteo_empresas.
 */
private static function empresa_existe( $empresa_id ) {

    global $wpdb;

    $tabla_empresas = $wpdb->prefix . 'ruteo_empresas';

    $existe = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id FROM {$tabla_empresas} WHERE id = %d LIMIT 1",
            $empresa_id
        )
    );

    return (bool) $existe;
}


/**
 * Guarda los meta campos adicionales que se repiten en creación y edición.
 */
private static function guardar_meta_adicional( $user_id, $input, $signer_caps ) {

    update_user_meta( $user_id, 'ruteo_phone', $input['phone'] );
    update_user_meta( $user_id, 'ruteo_pm_assigned', $input['pm_assigned'] );
    update_user_meta( $user_id, 'ruteo_negativa_rol', $input['negativa_rol'] );
    update_user_meta( $user_id, 'ruteo_position', $input['position'] );
    update_user_meta( $user_id, 'ruteo_signer_caps', $signer_caps );
}


/**
 * Procesa el avatar subido (si existe) y lo guarda como base64 en user_meta.
 * Antes estaba duplicado en la rama de edición y en la de creación.
 */
private static function procesar_avatar( $user_id, $empresa_id = 0 ) {

    if ( ! empty( $_FILES['avatar']['tmp_name'] ) ) {
        $tmp_file = $_FILES['avatar']['tmp_name'];
        $type     = mime_content_type( $tmp_file );
        $content  = file_get_contents( $tmp_file );

        $base64 = 'data:' . $type . ';base64,' . base64_encode( $content );

        update_user_meta( $user_id, 'ruteo_avatar', $base64 );

        @unlink( $tmp_file );
        return;
    }

    // Si no subieron foto a mano, usamos el logo de la empresa como avatar
    // por defecto (solo si el usuario todavia no tiene avatar propio).
    if ( $empresa_id ) {
        $avatar_actual = get_user_meta( $user_id, 'ruteo_avatar', true );
        if ( empty( $avatar_actual ) ) {
            $logo_empresa = self::get_empresa_logo( $empresa_id );
            if ( $logo_empresa ) {
                update_user_meta( $user_id, 'ruteo_avatar', $logo_empresa );
            }
        }
    }
}


/**
 * Rama de edición de usuario.
 */
private static function procesar_edicion_usuario( $input, $wp_role, $empresa_id, $signer_caps, $is_super_admin ) {

    $edit_id     = $input['edit_id'];
    $target_user = get_userdata( $edit_id );

    if ( ! $target_user ) {
        wp_send_json_error( array( 'message' => 'El usuario que intentas editar no existe.' ), 404 );
        return;
    }

    $target_empresa_id = self::get_user_empresa_id( $edit_id );

    if ( ! $is_super_admin && $target_empresa_id !== $empresa_id ) {
        wp_send_json_error( array( 'message' => 'No tienes permisos para modificar usuarios de otra empresa.' ), 403 );
        return;
    }

    if ( ! $is_super_admin && in_array( 'ruteo_admin', (array) $target_user->roles, true ) ) {
        wp_send_json_error( array( 'message' => 'No tienes permisos para modificar al Administrador de la empresa.' ), 403 );
        return;
    }

    if ( $wp_role === 'ruteo_super_admin' ) {
        wp_send_json_error( array( 'message' => 'No se puede asignar el rol Administrador General desde esta sección.' ), 403 );
        return;
    }

    $user_data = array(
        'ID'           => $edit_id,
        'display_name' => ! empty( $input['display_name'] ) ? $input['display_name'] : $input['username'],
    );

    if ( ! empty( $input['email'] ) ) {
        $user_data['user_email'] = $input['email'];
    }

    if ( ! empty( $input['password'] ) ) {
        $user_data['user_pass'] = $input['password'];
    }

    $updated_user = wp_update_user( $user_data );

    if ( is_wp_error( $updated_user ) ) {
        wp_send_json_error( array( 'message' => $updated_user->get_error_message() ) );
        return;
    }

    $u = new WP_User( $edit_id );
    $u->set_role( $wp_role );

    update_user_meta( $edit_id, 'ruteo_empresa_id', $target_empresa_id );

    self::guardar_meta_adicional( $edit_id, $input, $signer_caps );
    self::procesar_avatar( $edit_id );

    self::registrar_log(
        'Usuario Actualizado',
        'Se actualizaron rol (' . $wp_role . '), cargo, permisos y empresa del usuario ID #' . $edit_id . '. Empresa ID: ' . $target_empresa_id
    );

    wp_send_json_success(
        array(
            'message'    => 'Usuario actualizado correctamente.',
            'user_id'    => $edit_id,
            'empresa_id' => $target_empresa_id,
        )
    );
}


/**
 * Rama de creación de usuario.
 */
private static function procesar_creacion_usuario( $input, $wp_role, $empresa_id, $signer_caps ) {

    if ( empty( $input['username'] ) || empty( $input['password'] ) || empty( $input['email'] ) ) {
        wp_send_json_error( array( 'message' => 'Usuario, correo y clave son obligatorios.' ) );
        return;
    }

    if ( username_exists( $input['username'] ) ) {
        wp_send_json_error( array( 'message' => 'El nombre de usuario ya existe.' ) );
        return;
    }

    if ( email_exists( $input['email'] ) ) {
        wp_send_json_error( array( 'message' => 'El correo electronico ya esta registrado.' ) );
        return;
    }

    $user_id = wp_insert_user(
        array(
            'user_login'   => $input['username'],
            'user_pass'    => $input['password'],
            'user_email'   => $input['email'],
            'display_name' => ! empty( $input['display_name'] ) ? $input['display_name'] : $input['username'],
            'role'         => $wp_role,
        )
    );

    if ( is_wp_error( $user_id ) ) {
        wp_send_json_error( array( 'message' => $user_id->get_error_message() ) );
        return;
    }

    update_user_meta( $user_id, 'ruteo_empresa_id', $empresa_id );

    self::guardar_meta_adicional( $user_id, $input, $signer_caps );
    self::procesar_avatar( $user_id, $empresa_id );

    self::registrar_log(
        'Usuario Creado',
        'Se creo la cuenta de usuario ' . $input['username'] . ' con rol ' . $wp_role . ', empresa ID #' . $empresa_id . ' y permisos de firmante'
    );

    wp_send_json_success(
        array(
            'message'    => 'Usuario creado exitosamente con perfil ampliado.',
            'user_id'    => $user_id,
            'empresa_id' => $empresa_id,
        )
    );
}

   public function handle_ajax_delete_user() {
    check_ajax_referer( 'ruteo_submit_nonce', 'nonce' );

    $current_user = wp_get_current_user();

    $is_super_admin   = self::es_super_admin( $current_user );
    $is_company_admin = in_array( 'ruteo_admin', (array) $current_user->roles, true );

    if ( ! $is_super_admin && ! $is_company_admin ) {
        wp_send_json_error( array( 'message' => 'Acceso denegado.' ), 403 );
        return;
    }

    $user_id = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : 0;

    if ( ! $user_id || $user_id === $current_user->ID ) {
        wp_send_json_error( array( 'message' => 'Acción no permitida.' ), 400 );
        return;
    }

    $target_user = get_userdata( $user_id );

    if ( ! $target_user ) {
        wp_send_json_error( array( 'message' => 'El usuario no existe.' ), 404 );
        return;
    }

    // PROTECCIÓN DEL ADMINISTRADOR GENERAL
    // Nadie puede eliminar a un Administrador General desde aquí,
    // ni siquiera otro Administrador General.
    if ( self::es_super_admin( $target_user ) ) {
        wp_send_json_error(
            array( 'message' => 'No se puede eliminar un Administrador General desde esta sección.' ),
            403
        );
        return;
    }

    if ( ! $is_super_admin ) {

        $empresa_id        = self::get_user_empresa_id( $current_user->ID );
        $target_empresa_id = self::get_user_empresa_id( $user_id );

        if ( ! $empresa_id ) {
            wp_send_json_error( array( 'message' => 'Tu usuario no tiene una empresa asociada.' ), 403 );
            return;
        }

        if ( $target_empresa_id !== $empresa_id ) {
            wp_send_json_error( array( 'message' => 'No puedes eliminar usuarios de otra empresa.' ), 403 );
            return;
        }

        if ( in_array( 'ruteo_admin', (array) $target_user->roles, true ) ) {
            wp_send_json_error( array( 'message' => 'No tienes permisos para eliminar al Administrador de la empresa.' ), 403 );
            return;
        }
    }

    // Guardamos estos datos ANTES de borrar, para poder loguearlos después.
    $target_username = $target_user->user_login;
    $target_empresa   = self::get_user_empresa_id( $user_id );

    require_once ABSPATH . 'wp-admin/includes/user.php';

    $deleted = wp_delete_user( $user_id );

    if ( ! $deleted ) {
        wp_send_json_error( array( 'message' => 'No se pudo eliminar el usuario.' ), 500 );
        return;
    }

    self::registrar_log(
        'Usuario Eliminado',
        'Se eliminó al usuario ' . $target_username . ' (ID #' . $user_id . '), empresa ID: ' . $target_empresa
    );

    wp_send_json_success( array( 'message' => 'Usuario eliminado correctamente.' ) );
}

    public function handle_ajax_recover_password() {
        check_ajax_referer( 'ruteo_submit_nonce', 'nonce' );
        $user_input = isset( $_POST['user_login'] ) ? trim( wp_unslash( $_POST['user_login'] ) ) : '';

        if ( empty( $user_input ) ) {
            wp_send_json_error( array( 'message' => 'Por favor ingresa tu usuario o correo electronico.' ) );
            return;
        }

        $user = is_email( $user_input ) ? get_user_by( 'email', $user_input ) : get_user_by( 'login', $user_input );
        if ( ! $user ) {
            wp_send_json_error( array( 'message' => 'No se encontro ninguna cuenta registrada con esa informacion.' ) );
            return;
        }

        if ( ! function_exists( 'retrieve_password' ) ) {
            require_once ABSPATH . 'wp-includes/user.php';
        }

        $retrieved = retrieve_password( $user->user_login );

        if ( is_wp_error( $retrieved ) ) {
            wp_send_json_error( array( 'message' => 'Error al iniciar la recuperacion: ' . $retrieved->get_error_message() ) );
            return;
        }

        wp_send_json_success( array(
            'message' => 'Se ha enviado un correo de recuperacion a ' . esc_html( $user->user_email ) . '. Revisa tu bandeja de entrada o carpeta de SPAM.'
        ) );
    }

    public function handle_ajax_update_profile() {
        check_ajax_referer( 'ruteo_submit_nonce', 'nonce' );
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => 'Debes iniciar sesion.' ) );
            return;
        }

        $user_id      = get_current_user_id();
        $display_name = isset( $_POST['display_name'] ) ? sanitize_text_field( wp_unslash( $_POST['display_name'] ) ) : '';
        $email        = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
        $new_password = isset( $_POST['new_password'] ) ? $_POST['new_password'] : '';
        $phone        = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
        $pm_assigned  = isset( $_POST['pm_assigned'] ) ? sanitize_text_field( wp_unslash( $_POST['pm_assigned'] ) ) : '';
        $position     = isset( $_POST['position'] ) ? sanitize_text_field( wp_unslash( $_POST['position'] ) ) : '';

        $update_data = array( 'ID' => $user_id );
        if ( ! empty( $display_name ) ) {
            $update_data['display_name'] = $display_name;
        }
        if ( ! empty( $email ) && is_email( $email ) ) {
            $existing_user = get_user_by( 'email', $email );
            if ( $existing_user && (int) $existing_user->ID !== (int) $user_id ) {
                wp_send_json_error( array( 'message' => 'El correo electronico ya esta registrado en otra cuenta.' ) );
                return;
            }
            $update_data['user_email'] = $email;
        }
        if ( ! empty( $new_password ) ) {
            if ( strlen( $new_password ) < 6 ) {
                wp_send_json_error( array( 'message' => 'La nueva clave debe tener al menos 6 caracteres.' ) );
                return;
            }
            $update_data['user_pass'] = $new_password;
        }

        if ( count( $update_data ) > 1 ) {
            $updated = wp_update_user( $update_data );
            if ( is_wp_error( $updated ) ) {
                wp_send_json_error( array( 'message' => 'Error actualizando usuario: ' . $updated->get_error_message() ) );
                return;
            }
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

        wp_send_json_success( array( 'message' => 'Perfil y datos actualizados correctamente.', 'firma' => $firma_actual ) );
    }
    
    public function handle_ajax_update_site_logo() {
        check_ajax_referer( 'ruteo_submit_nonce', 'nonce' );

        $current_user   = wp_get_current_user();
        $is_super_admin = self::es_super_admin( $current_user );

        if ( ! $is_super_admin ) {
            wp_send_json_error( array( 'message' => 'Solo el Administrador General puede actualizar el logo del sistema.' ) );
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
    'empresa_id'  => self::get_user_empresa_id( get_current_user_id() ),
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

    $current_user = wp_get_current_user();
    $empresa_id   = self::get_user_empresa_id( $current_user->ID );
    $is_super     = self::es_super_admin( $current_user );

    $materiales = get_option( 'wp_ruteo_materiales_store', array() );
    if ( ! is_array( $materiales ) ) {
        $materiales = array();
    }

    if ( ! $is_super ) {
        $materiales = array_values( array_filter( $materiales, function( $m ) use ( $empresa_id ) {
            $m_empresa = isset( $m['empresa_id'] ) ? (int) $m['empresa_id'] : 0;
            // Reportes antiguos sin empresa_id se muestran igual, para no perder historial previo.
            return $m_empresa === $empresa_id;
        } ) );
    }

    wp_send_json_success( array(
        'materiales' => $materiales,
        'total'      => count( $materiales )
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

    $current_user = wp_get_current_user();
    $empresa_id   = self::get_user_empresa_id( $current_user->ID );
    $is_super     = self::es_super_admin( $current_user );

    $clientes = get_option( 'ruteo_clientes_list', array() );
    if ( empty( $clientes ) || ! is_array( $clientes ) ) {
        $clientes = array(
            array(
                'id'         => 'CLI-CYMTEL',
                'nombre'     => 'CYMTEL',
                'ruc'        => '20512345678',
                'logo'       => '',
                'empresa_id' => 0,
            )
        );
        update_option( 'ruteo_clientes_list', $clientes, false );
    }

    // El Administrador General ve todos. Cada empresa solo ve los suyos
    // (o los antiguos sin empresa_id, para no perder datos ya existentes).
    if ( ! $is_super ) {
        $clientes = array_values( array_filter( $clientes, function( $c ) use ( $empresa_id ) {
            $c_empresa = isset( $c['empresa_id'] ) ? (int) $c['empresa_id'] : 0;
            return $c_empresa === 0 || $c_empresa === $empresa_id;
        } ) );
    }

    wp_send_json_success( array( 'clientes' => $clientes ) );
}

    public function handle_ajax_save_cliente() {
    check_ajax_referer( 'ruteo_submit_nonce', 'nonce' );
    $current_user = wp_get_current_user();
    $is_admin     = self::es_super_admin( $current_user )
        || in_array( 'ruteo_admin', (array) $current_user->roles, true );

    if ( ! $is_admin ) {
        wp_send_json_error( array( 'message' => 'Solo administradores pueden gestionar clientes.' ) );
        return;
    }

    $empresa_id = self::get_user_empresa_id( $current_user->ID );

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

                // Un admin de empresa no puede editar clientes de otra empresa.
                $c_empresa = isset( $c['empresa_id'] ) ? (int) $c['empresa_id'] : 0;
                if ( ! self::es_super_admin( $current_user ) && $c_empresa !== 0 && $c_empresa !== $empresa_id ) {
                    wp_send_json_error( array( 'message' => 'No puedes editar clientes de otra empresa.' ) );
                    return;
                }

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
        unset( $c );

    } else {

        $new_id = 'CLI-' . time();
        $clientes[] = array(
            'id'         => $new_id,
            'nombre'     => $nombre,
            'ruc'        => $ruc,
            'direccion'  => $direccion,
            'contacto'   => $contacto,
            'logo'       => $logo,
            'empresa_id' => $empresa_id,
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
    $is_admin     = self::es_super_admin( $current_user )
        || in_array( 'ruteo_admin', (array) $current_user->roles, true );

    if ( ! $is_admin ) {
        wp_send_json_error( array( 'message' => 'Acceso denegado.' ) );
        return;
    }

    $empresa_id = self::get_user_empresa_id( $current_user->ID );

    $id = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
    if ( empty( $id ) ) {
        wp_send_json_error( array( 'message' => 'ID invalido.' ) );
        return;
    }

    $clientes = get_option( 'ruteo_clientes_list', array() );
    if ( is_array( $clientes ) ) {

        // No permitir borrar clientes de otra empresa.
        foreach ( $clientes as $c ) {
            if ( isset( $c['id'] ) && $c['id'] === $id ) {
                $c_empresa = isset( $c['empresa_id'] ) ? (int) $c['empresa_id'] : 0;
                if ( ! self::es_super_admin( $current_user ) && $c_empresa !== 0 && $c_empresa !== $empresa_id ) {
                    wp_send_json_error( array( 'message' => 'No puedes eliminar clientes de otra empresa.' ) );
                    return;
                }
                break;
            }
        }

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


    /**
     * Lista todas las empresas. Solo el Administrador General puede verlas todas.
     */
    public function handle_ajax_get_empresas() {
        check_ajax_referer( 'ruteo_submit_nonce', 'nonce' );

        if ( ! self::es_super_admin() ) {
            wp_send_json_error( array( 'message' => 'Acceso denegado. Solo el Administrador General puede ver las empresas.' ) );
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'ruteo_empresas';
        $empresas = $wpdb->get_results( "SELECT * FROM $table ORDER BY fecha_creacion DESC", ARRAY_A );

        wp_send_json_success( array( 'empresas' => $empresas ) );
    }

    /**
     * Crea una Empresa + su Administrador en un solo paso.
     * Solo el Administrador General puede hacerlo.
     */
    public function handle_ajax_save_empresa() {
        check_ajax_referer( 'ruteo_submit_nonce', 'nonce' );

        if ( ! self::es_super_admin() ) {
            wp_send_json_error( array( 'message' => 'Acceso denegado. Solo el Administrador General puede crear empresas.' ) );
            return;
        }

        $nombre    = isset( $_POST['nombre'] ) ? sanitize_text_field( wp_unslash( $_POST['nombre'] ) ) : '';
        $ruc       = isset( $_POST['ruc'] ) ? sanitize_text_field( wp_unslash( $_POST['ruc'] ) ) : '';
        $direccion = isset( $_POST['direccion'] ) ? sanitize_text_field( wp_unslash( $_POST['direccion'] ) ) : '';
        $contacto  = isset( $_POST['contacto'] ) ? sanitize_text_field( wp_unslash( $_POST['contacto'] ) ) : '';
        $logo      = '';

        $admin_username = isset( $_POST['admin_username'] ) ? sanitize_user( wp_unslash( $_POST['admin_username'] ) ) : '';
        $admin_email    = isset( $_POST['admin_email'] ) ? sanitize_email( wp_unslash( $_POST['admin_email'] ) ) : '';
        $admin_password = isset( $_POST['admin_password'] ) ? $_POST['admin_password'] : '';
        $admin_nombre   = isset( $_POST['admin_nombre'] ) ? sanitize_text_field( wp_unslash( $_POST['admin_nombre'] ) ) : '';

        if ( empty( $nombre ) || empty( $admin_username ) || empty( $admin_email ) || empty( $admin_password ) ) {
            wp_send_json_error( array( 'message' => 'Nombre de empresa, usuario, correo y clave del administrador son obligatorios.' ) );
            return;
        }

        if ( username_exists( $admin_username ) ) {
            wp_send_json_error( array( 'message' => 'El nombre de usuario del administrador ya existe.' ) );
            return;
        }

        if ( email_exists( $admin_email ) ) {
            wp_send_json_error( array( 'message' => 'El correo del administrador ya esta registrado.' ) );
            return;
        }

        if ( ! empty( $_FILES['logo']['tmp_name'] ) ) {
            $tmp     = $_FILES['logo']['tmp_name'];
            $type    = mime_content_type( $tmp );
            $content = file_get_contents( $tmp );
            $logo    = 'data:' . $type . ';base64,' . base64_encode( $content );
            @unlink( $tmp );
        }

        global $wpdb;
        $table = $wpdb->prefix . 'ruteo_empresas';

        $wpdb->insert( $table, array(
            'nombre'    => $nombre,
            'ruc'       => $ruc,
            'direccion' => $direccion,
            'contacto'  => $contacto,
            'logo'      => $logo,
            'estado'    => 'activa',
        ) );

        $empresa_id = $wpdb->insert_id;

        if ( ! $empresa_id ) {
            wp_send_json_error( array( 'message' => 'No se pudo crear la empresa en la base de datos.' ) );
            return;
        }

        $admin_user_id = wp_insert_user( array(
            'user_login'   => $admin_username,
            'user_pass'    => $admin_password,
            'user_email'   => $admin_email,
            'display_name' => ! empty( $admin_nombre ) ? $admin_nombre : $admin_username,
            'role'         => 'ruteo_admin',
        ) );

        if ( is_wp_error( $admin_user_id ) ) {
            $wpdb->delete( $table, array( 'id' => $empresa_id ) );
            wp_send_json_error( array( 'message' => 'Empresa no creada: ' . $admin_user_id->get_error_message() ) );
            return;
        }

        update_user_meta( $admin_user_id, 'ruteo_empresa_id', $empresa_id );
        $wpdb->update( $table, array( 'admin_user_id' => $admin_user_id ), array( 'id' => $empresa_id ) );

        if ( $logo ) {
            update_user_meta( $admin_user_id, 'ruteo_avatar', $logo );
        }

        self::registrar_log( 'Empresa Creada', 'Se creo la empresa "' . $nombre . '" (ID ' . $empresa_id . ') con administrador ' . $admin_username );

        wp_send_json_success( array(
            'message'    => 'Empresa y administrador creados correctamente.',
            'empresa_id' => $empresa_id,
        ) );
    }

    /**
     * Elimina una empresa. Solo el Administrador General.
     * No elimina al usuario administrador por seguridad (se hace aparte).
     */
    public function handle_ajax_delete_empresa() {
        check_ajax_referer( 'ruteo_submit_nonce', 'nonce' );

        if ( ! self::es_super_admin() ) {
            wp_send_json_error( array( 'message' => 'Acceso denegado.' ) );
            return;
        }

        $empresa_id = isset( $_POST['empresa_id'] ) ? intval( $_POST['empresa_id'] ) : 0;
        if ( ! $empresa_id ) {
            wp_send_json_error( array( 'message' => 'ID de empresa invalido.' ) );
            return;
        }

        global $wpdb;
        $usuarios_asociados = get_users(
    array(
        'meta_key'   => 'ruteo_empresa_id',
        'meta_value' => $empresa_id,
        'fields'     => 'ID',
        'number'     => 1,
    )
);

if ( ! empty( $usuarios_asociados ) ) {
    wp_send_json_error(
        array(
            'message' => 'No puedes eliminar esta empresa porque todavía tiene usuarios asociados.'
        ),
        409
    );
    return;
}
        $table = $wpdb->prefix . 'ruteo_empresas';
        $wpdb->delete( $table, array( 'id' => $empresa_id ) );

        self::registrar_log( 'Empresa Eliminada', 'Se elimino la empresa ID ' . $empresa_id );

        wp_send_json_success( array( 'message' => 'Empresa eliminada correctamente.' ) );
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
        $empresa_id_creador = self::get_user_empresa_id( $current_user->ID );

        $id    = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
        $etapa = isset( $_POST['etapa'] ) ? sanitize_text_field( wp_unslash( $_POST['etapa'] ) ) : '';

        $is_admin = self::es_super_admin( $current_user )
            || in_array( 'ruteo_admin', (array) $current_user->roles, true );
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
            foreach ( array( 'foto1' => 'foto1_url', 'foto2' => 'foto2_url', 'neg-foto1' => 'foto1_url', 'neg-foto2' => 'foto2_url' ) as $file_key => $target_col ) {
                if ( ! empty( $_FILES[ $file_key ]['tmp_name'] ) ) {
                    $tmp     = $_FILES[ $file_key ]['tmp_name'];
                    $type    = mime_content_type( $tmp );
                    if ( ! $type || $type === 'application/octet-stream' ) {
                        $ext  = strtolower( pathinfo( $_FILES[ $file_key ]['name'] ?? '', PATHINFO_EXTENSION ) );
                        $type = ( $ext === 'png' ) ? 'image/png' : ( ( $ext === 'webp' ) ? 'image/webp' : 'image/jpeg' );
                    }
                    $content = file_get_contents( $tmp );
                    if ( ! empty( $content ) ) {
                        $campos[ $target_col ] = 'data:' . $type . ';base64,' . base64_encode( $content );
                    }
                    @unlink( $tmp );
                }
            }

            if ( empty( $campos['foto1_url'] ) && ! empty( $_POST['foto1_base64'] ) ) {
                $campos['foto1_url'] = $_POST['foto1_base64'];
            }
            if ( empty( $campos['foto2_url'] ) && ! empty( $_POST['foto2_base64'] ) ) {
                $campos['foto2_url'] = $_POST['foto2_base64'];
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

            $update_fields = array(
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
            );

            if ( ! empty( $_POST['foto1_base64'] ) ) {
                $update_fields['foto1_url'] = $_POST['foto1_base64'];
            }
            if ( ! empty( $_POST['foto2_base64'] ) ) {
                $update_fields['foto2_url'] = $_POST['foto2_base64'];
            }

            $update_ok = $wpdb->update( $table, $update_fields, array( 'id' => $id ) );
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

    public function handle_ajax_negativa_editar() {
        check_ajax_referer( 'ruteo_submit_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => 'Acceso denegado. Debes iniciar sesion.' ) );
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'ruteo_negativas';
        $current_user = wp_get_current_user();
        $id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;

        if ( $id <= 0 ) {
            wp_send_json_error( array( 'message' => 'Registro invalido.' ) );
            return;
        }

        $registro = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id ), ARRAY_A );
        if ( ! $registro ) {
            wp_send_json_error( array( 'message' => 'El registro no existe.' ) );
            return;
        }

        $is_admin        = in_array( 'administrator', (array) $current_user->roles, true ) || in_array( 'ruteo_admin', (array) $current_user->roles, true );
        $is_sup_operativo = in_array( 'ruteo_sup_operativo', (array) $current_user->roles, true );
        $es_creador       = ( $registro['creado_por'] === $current_user->display_name );

        if ( ! $is_admin && ! $is_sup_operativo && ! $es_creador ) {
            wp_send_json_error( array( 'message' => 'No tienes permiso para editar este registro. Solo puede editarlo quien lo creo, el Supervisor Operativo o un Administrador.' ) );
            return;
        }

        $campos = array(
            'proceso'                     => sanitize_text_field( wp_unslash( $_POST['proceso'] ?? $registro['proceso'] ) ),
            'cm_localidad'                => sanitize_text_field( wp_unslash( $_POST['cm_localidad'] ?? $registro['cm_localidad'] ) ),
            'contratista'                 => sanitize_text_field( wp_unslash( $_POST['contratista'] ?? $registro['contratista'] ) ),
            'sub_contratista'             => sanitize_text_field( wp_unslash( $_POST['sub_contratista'] ?? $registro['sub_contratista'] ) ),
            'relacionado_a'               => sanitize_text_field( wp_unslash( $_POST['relacionado_a'] ?? $registro['relacionado_a'] ) ),
            'lugar_trabajo'               => sanitize_text_field( wp_unslash( $_POST['lugar_trabajo'] ?? $registro['lugar_trabajo'] ) ),
            'fecha'                       => sanitize_text_field( wp_unslash( $_POST['fecha'] ?? $registro['fecha'] ) ),
            'hora_inicio'                 => sanitize_text_field( wp_unslash( $_POST['hora_inicio'] ?? $registro['hora_inicio'] ) ),
            'hora_fin'                    => sanitize_text_field( wp_unslash( $_POST['hora_fin'] ?? $registro['hora_fin'] ) ),
            'total_horas'                 => sanitize_text_field( wp_unslash( $_POST['total_horas'] ?? $registro['total_horas'] ) ),
            'supervisor_operativo_nombre' => sanitize_text_field( wp_unslash( $_POST['supervisor_operativo_nombre'] ?? $registro['supervisor_operativo_nombre'] ) ),
            'trabajador_reportante'       => sanitize_text_field( wp_unslash( $_POST['trabajador_reportante'] ?? $registro['trabajador_reportante'] ) ),
            'razones_negativa'            => sanitize_textarea_field( wp_unslash( $_POST['razones_negativa'] ?? $registro['razones_negativa'] ) ),
        );

        $wpdb->update( $table, $campos, array( 'id' => $id ) );

        self::registrar_log( 'Negativa Editada', 'El usuario ' . $current_user->display_name . ' edito el registro #' . $id . ' de Negativa al Trabajo.' );

        wp_send_json_success( array( 'message' => 'Registro actualizado correctamente.' ) );
    }

    /**
     * Elimina un registro de Negativa al Trabajo. Solo Administradores
     * (General o de Empresa) pueden hacerlo, y solo de su propia empresa.
     */
    public function handle_ajax_negativa_eliminar() {
        check_ajax_referer( 'ruteo_submit_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => 'Acceso denegado. Debes iniciar sesion.' ) );
            return;
        }

        global $wpdb;
        $table         = $wpdb->prefix . 'ruteo_negativas';
        $current_user  = wp_get_current_user();
        $id            = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;

        if ( $id <= 0 ) {
            wp_send_json_error( array( 'message' => 'Registro invalido.' ) );
            return;
        }

        $registro = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id ), ARRAY_A );
        if ( ! $registro ) {
            wp_send_json_error( array( 'message' => 'El registro no existe.' ) );
            return;
        }

        $is_super_admin   = self::es_super_admin( $current_user );
        $is_company_admin = in_array( 'ruteo_admin', (array) $current_user->roles, true );

        if ( ! $is_super_admin && ! $is_company_admin ) {
            wp_send_json_error( array( 'message' => 'Solo un Administrador puede eliminar un registro de Negativa al Trabajo.' ) );
            return;
        }

        if ( ! $is_super_admin ) {
            $empresa_id           = self::get_user_empresa_id( $current_user->ID );
            $registro_empresa_id  = isset( $registro['empresa_id'] ) ? (int) $registro['empresa_id'] : 0;
            if ( $registro_empresa_id !== $empresa_id ) {
                wp_send_json_error( array( 'message' => 'No puedes eliminar registros de otra empresa.' ) );
                return;
            }
        }

        $wpdb->delete( $table, array( 'id' => $id ) );

        self::registrar_log( 'Negativa Eliminada', 'El usuario ' . $current_user->display_name . ' elimino el registro #' . $id . ' de Negativa al Trabajo.' );

        wp_send_json_success( array( 'message' => 'Registro eliminado correctamente.' ) );
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

    global $wpdb;
    $table = $wpdb->prefix . 'ruteo_negativas';

    $current_user = wp_get_current_user();
    $empresa_id   = self::get_user_empresa_id( $current_user->ID );
    $is_super     = self::es_super_admin( $current_user );

    if ( $is_super ) {
        $db_registros = $wpdb->get_results( "SELECT * FROM $table ORDER BY id DESC", ARRAY_A );
    } else {
        $db_registros = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE empresa_id = %d ORDER BY id DESC",
                $empresa_id
            ),
            ARRAY_A
        );
    }

    if ( ! is_array( $db_registros ) ) {
        $db_registros = array();
    }

    $db_map = array();
    foreach ( $db_registros as $row ) {
        if ( ! empty( $row['id'] ) ) {
            $db_map[ intval( $row['id'] ) ] = $row;
        }
    }

    $sheets_registros = array();
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
                if ( isset( $json['status'] ) && $json['status'] === 'success' && isset( $json['negativas'] ) && is_array( $json['negativas'] ) ) {
                    $sheets_registros = $json['negativas'];
                }
            }
        }
    }

    $final_registros = array();
    if ( ! empty( $sheets_registros ) ) {
        foreach ( $sheets_registros as $s_row ) {
            $sid = isset( $s_row['id'] ) ? intval( $s_row['id'] ) : 0;
            if ( $sid > 0 && isset( $db_map[ $sid ] ) ) {
                $db_row = $db_map[ $sid ];
                $merged = array_merge( $s_row, array_filter( $db_row, function( $v ) {
                    return $v !== null && $v !== '';
                } ) );

                $img_keys = array( 'foto1_url', 'foto2_url', 'firma_tecnico_img', 'firma_sup_operativo_img', 'firma_sup_seguridad_img', 'firma_hse_img', 'cliente_logo' );
                foreach ( $img_keys as $k ) {
                    if ( ! empty( $db_row[ $k ] ) ) {
                        $merged[ $k ] = $db_row[ $k ];
                    }
                }
                $final_registros[] = $merged;
                unset( $db_map[ $sid ] );
            } elseif ( $is_super ) {
                // Un registro que existe en Sheets pero no en la BD (o no en la BD
                // filtrada por empresa) solo se muestra sin filtro al Administrador
                // General, porque las filas de Sheets no tienen empresa_id y no
                // podemos verificar de forma segura a que empresa pertenecen.
                $final_registros[] = $s_row;
            }
        }
        foreach ( $db_map as $db_row ) {
            $final_registros[] = $db_row;
        }
    } else {
        $final_registros = $db_registros;
    }

    update_option( 'ruteo_cache_negativas', $final_registros, false );
    wp_send_json_success( array( 'registros' => $final_registros ) );
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
            'fecha'      => current_time( 'mysql' ),
            'usuario'    => $user_name,
            'pm'         => $user_pm ?: '-',
            'accion'     => $accion,
            'detalles'   => $detalles,
            'empresa_id' => self::get_user_empresa_id( $current_user->exists() ? $current_user->ID : 0 ),
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
        $current_user = wp_get_current_user();
        $empresa_id   = self::get_user_empresa_id( $current_user->ID );
        $is_super     = self::es_super_admin( $current_user );
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

        if ( ! $is_super ) {
            $logs = array_values( array_filter( $logs, function( $l ) use ( $empresa_id ) {
                $l_empresa = isset( $l['empresa_id'] ) ? (int) $l['empresa_id'] : -1;
                return $l_empresa === $empresa_id;
            } ) );
        }

        // Adjuntamos el nombre de la empresa a cada log, para poder filtrar por empresa en pantalla.
        global $wpdb;
        $tabla_empresas = $wpdb->prefix . 'ruteo_empresas';
        $empresas_rows  = $wpdb->get_results( "SELECT id, nombre FROM $tabla_empresas", ARRAY_A );
        $empresas_map   = array();
        foreach ( $empresas_rows as $er ) {
            $empresas_map[ (int) $er['id'] ] = $er['nombre'];
        }
        foreach ( $logs as &$l ) {
            $eid = isset( $l['empresa_id'] ) ? (int) $l['empresa_id'] : 0;
            $l['empresa'] = ( $eid && isset( $empresas_map[ $eid ] ) ) ? $empresas_map[ $eid ] : 'Admin General';
        }
        unset( $l );

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
        $version_actual = '2.4.0';
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
        empresa_id BIGINT UNSIGNED DEFAULT 0,
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

/**
 * Crea la tabla de Empresas (entidad real del sistema multiempresa).
 * Cada empresa tiene su administrador vinculado via admin_user_id.
 */
function ruteo_crear_tabla_empresas() {
    global $wpdb;
    $table = $wpdb->prefix . 'ruteo_empresas';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(150) NOT NULL,
        ruc VARCHAR(20),
        direccion VARCHAR(255),
        contacto VARCHAR(150),
        logo LONGTEXT,
        admin_user_id BIGINT UNSIGNED,
        estado VARCHAR(20) DEFAULT 'activa',
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}
register_activation_hook( __FILE__, 'ruteo_crear_tabla_empresas' );
add_action( 'plugins_loaded', 'ruteo_crear_tabla_empresas' );

register_activation_hook( __FILE__, 'ruteo_crear_tabla_negativas' );
register_activation_hook( __FILE__, array( 'WPRuteoApp', 'activar_cuentas_prueba' ) );

$GLOBALS['wp_ruteo_app'] = new WPRuteoApp();