<?php
/**
Plugin Name:  WooCommerce RW WooMS Synchronisation
Description: Transfers orders from WooCommerce to Moy Sklad CRM.
Version: 1.0.0
Author: Alexej Bogačev
 */


// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Wc_Rw_Wooms_Sync {

    public function __construct()
    {

        $this->load_config();

        //register styles and scripts
        add_action('admin_enqueue_scripts', array($this, 'load_admin_scripts'));

        $this->register_ajax_handler();
        $this->initialize_plugin();
        $this->load_debugger();
        $this->load_classes();

    }

    private function initialize_plugin()
    {

        require WP_PLUGIN_DIR . '/wc-rw-wooms-sync/includes/class-wc-rw-wooms-sync-init.php';
        Wc_Rw_Wooms_Sync_Init::get_instance();

    }



    protected function register_ajax_handler(){

        require_once WP_PLUGIN_DIR . '/wc-rw-wooms-sync/includes/class-wc-rw-wooms-sync-ajax-handler.php';
        add_action( 'wp_ajax_synchronise_order_action', array('Wc_Rw_Wooms_Ajax_Handler','synchronise_order_action' ));

    }

    public function load_admin_scripts($hook){

        if ( 'post.php' != $hook && 'post-new.php' != $hook ) {
            return;
        }

        wp_enqueue_script('wc-rw-wooms-sync-ajax-script', WP_PLUGIN_URL  . '/wc-rw-wooms-sync/assets/js/ajax.js', array('jquery'), "1.1", true);
        wp_localize_script('wc-rw-wooms-sync-ajax-script','wc_rw_wooms_sync_ajax_obj', array('ajax_url' => admin_url( 'admin-ajax.php' ),'security' => wp_create_nonce('wc_rw_wooms_sync_ajax_nonce')));

    }

    private function load_debugger()
    {
        require_once WP_PLUGIN_DIR . '/wc-rw-wooms-sync/includes/wc-rw-wooms-sync-debug.php';


    }

    private function load_config(){
        require_once WP_PLUGIN_DIR . '/wc-rw-wooms-sync/config/config_api.php';
        require_once  WP_PLUGIN_DIR . '/wc-rw-wooms-sync/config/init.php';
        require_once WP_PLUGIN_DIR . '/wc-rw-wooms-sync/includes/class-wc-rw-wooms-sync-config.php';

    }

    private function load_classes(){
        require_once WP_PLUGIN_DIR . '/wc-rw-wooms-sync/includes/class-wc-rw-wooms-sync-data-getter.php';
        require_once WP_PLUGIN_DIR . '/wc-rw-wooms-sync/includes/class-wc-rw-wooms-sync-api-request.php';
        require_once WP_PLUGIN_DIR . '/wc-rw-wooms-sync/includes/class-wc-rw-wooms-sync-logger.php';

    }





}

/**
 * @return mixed|Wc_Rw_Shipping_Tracking
 *
 * Get instance of main plugin class
 */
function wc_rw_wooms_sync() {
    static $instance;

    if ( ! isset( $instance ) ) {
        $instance = new Wc_Rw_Wooms_Sync();
    }

    return $instance;
}

/**
 * Begin execution of the plugin.
 */
wc_rw_wooms_sync();