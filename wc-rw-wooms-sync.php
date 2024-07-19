<?php
/**
Plugin Name:  WooCommerce RW WooMS Synchronisation
Description: Transfers orders from WooCommerce to Moy Sklad CRM.
Version: 1.5.0
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
        $this->register_hooks();
        $this->load_debugger();

    }

    /**
     * Register all hooks.
     */
    private function register_hooks() {
        add_action('admin_enqueue_scripts', [$this, 'load_admin_scripts']);
        add_action('wp_ajax_synchronise_order_action', [$this, 'register_ajax_handler']);
        add_action('plugins_loaded', [$this, 'initialize_plugin']);
    }

    /**
     * Initialize the plugin.
     */
    public function initialize_plugin()
    {
        $this->load_classes();
        Wc_Rw_Wooms_Sync_Init::get_instance();

    }


    /**
     * Register the AJAX handler.
     */
    public function register_ajax_handler(){
        require_once WP_PLUGIN_DIR . '/wc-rw-wooms-sync/includes/class-wc-rw-wooms-sync-ajax-handler.php';
        Wc_Rw_Wooms_Ajax_Handler::synchronise_order_action();
    }

    /**
     * Load admin scripts.
     *
     * @param string $hook
     */
    public function load_admin_scripts($hook){

        if ( 'post.php' != $hook && 'post-new.php' != $hook ) {
            return;
        }

        wp_enqueue_script('wc-rw-wooms-sync-ajax-script', WP_PLUGIN_URL  . '/wc-rw-wooms-sync/assets/js/ajax.js', array('jquery'), "1.5.0", true);
        wp_localize_script('wc-rw-wooms-sync-ajax-script','wc_rw_wooms_sync_ajax_obj', array('ajax_url' => admin_url( 'admin-ajax.php' ),'security' => wp_create_nonce('wc_rw_wooms_sync_ajax_nonce')));
        wp_enqueue_style( 'wc-rw-wooms-sync-admin-style', WP_PLUGIN_URL . '/wc-rw-wooms-sync/assets/css/admin.css', array(), '1.5.0' );

    }


    /**
     * Load the configuration files.
     */
    private function load_config(){
        require_once WP_PLUGIN_DIR . '/wc-rw-wooms-sync/config/config_api.php';
        require_once  WP_PLUGIN_DIR . '/wc-rw-wooms-sync/config/init.php';
        require_once WP_PLUGIN_DIR . '/wc-rw-wooms-sync/includes/class-wc-rw-wooms-sync-config.php';

    }

    /**
     * Load all necessary classes.
     */
    private function load_classes(){
        require_once WP_PLUGIN_DIR . '/wc-rw-wooms-sync/includes/class-wc-rw-wooms-sync-init.php';
        require_once WP_PLUGIN_DIR . '/wc-rw-wooms-sync/includes/class-wc-rw-wooms-sync-data-getter.php';
        require_once WP_PLUGIN_DIR . '/wc-rw-wooms-sync/includes/class-wc-rw-wooms-sync-api-request.php';
        require_once WP_PLUGIN_DIR . '/wc-rw-wooms-sync/includes/class-wc-rw-wooms-sync-logger.php';
        require_once WP_PLUGIN_DIR . '/wc-rw-wooms-sync/includes/class-wc-rw-wooms-sync-actions.php';

    }


    private function load_debugger()
    {
        require_once WP_PLUGIN_DIR . '/wc-rw-wooms-sync/includes/wc-rw-wooms-sync-debug.php';

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