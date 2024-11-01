<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ){
    die();
}

function wpbiztextwc_delete_plugin(){
    global $wpdb;


    $plugin_options = $wpdb->get_results( "SELECT option_name FROM $wpdb->options WHERE option_name LIKE 'wpbiztextwc%'" );

    foreach( $plugin_options as $option ) {
        delete_option( $option->option_name );
    }

   
    // $plugin_options = $wpdb->get_results( "SELECT meta_key FROM $wpdb->postmeta WHERE meta_key = '_billing_mobile_wpbiztextwc_phone'");

    // foreach( $plugin_options as $option ) {
    //     delete_option( $option->option_name );
    // }

    $postmeta_table = $wpdb->postmeta;
    $posts_table = $wpdb->posts;

    $postmeta_table = str_replace($wpdb->base_prefix, $wpdb->prefix, $postmeta_table);

    $wpdb->query("DELETE FROM " . $postmeta_table . " WHERE meta_key = '_billing_mobile_wpbiztextwc_phone'");
  
}

wpbiztextwc_delete_plugin();
?>