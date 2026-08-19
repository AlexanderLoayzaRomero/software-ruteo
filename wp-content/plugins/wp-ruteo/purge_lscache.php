<?php
$wp_load_path = dirname(__FILE__) . '/../../../wp-load.php';
if ( file_exists( $wp_load_path ) ) {
    require_once $wp_load_path;
}

header( 'Content-Type: text/plain' );

$purged = false;

if ( function_exists( 'litespeed_purge_all' ) ) {
    litespeed_purge_all();
    $purged = true;
    echo "LITESPEED_PURGED_VIA_FUNCTION\n";
}

if ( class_exists( 'LiteSpeed\Purge' ) ) {
    LiteSpeed\Purge::purge_all();
    $purged = true;
    echo "LITESPEED_PURGED_VIA_CLASS\n";
}

if ( function_exists( 'wp_cache_flush' ) ) {
    wp_cache_flush();
    echo "WP_CACHE_FLUSHED\n";
}

if ( ! $purged ) {
    echo "NO_LITESPEED_PLUGIN_FOUND_BUT_WP_CACHE_FLUSHED\n";
}
