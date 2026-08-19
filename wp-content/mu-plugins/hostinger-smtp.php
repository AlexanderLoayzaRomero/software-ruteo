<?php
/*
Plugin Name: Hostinger SMTP Force Config
Description: Fuerza a WordPress a enviar TODOS los correos por SMTP SSL de Hostinger.
Version: 1.0.0
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'phpmailer_init', function( $phpmailer ) {
    $phpmailer->isSMTP();
    $phpmailer->Host       = 'smtp.hostinger.com';
    $phpmailer->SMTPAuth   = true;
    $phpmailer->Port       = 465;
    $phpmailer->Username   = 'Desarrollador_SW@oracleperu.org';
    $phpmailer->Password   = 'OPS_id_001';
    $phpmailer->SMTPSecure = 'ssl';
    $phpmailer->From       = 'Desarrollador_SW@oracleperu.org';
    $phpmailer->FromName   = 'Software O&M';
    $phpmailer->Sender     = 'Desarrollador_SW@oracleperu.org';
    $phpmailer->SMTPOptions = array(
        'ssl' => array(
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true,
        ),
    );
}, 99999 );

add_filter( 'wp_mail_from', function() {
    return 'Desarrollador_SW@oracleperu.org';
}, 99999 );

add_filter( 'wp_mail_from_name', function() {
    return 'Software O&M';
}, 99999 );
