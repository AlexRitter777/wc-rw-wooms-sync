<?php

defined( 'ABSPATH' ) || exit;

class Wc_Rw_Wooms_Sync_Config
{

    private static $instance;
    private $config =[];

    private function __construct() {
        $this->config = include plugin_dir_path( __DIR__ ) . 'config/config_msid.php';
    }

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }



    /**
     * Retrieves a property from the configuration.
     *
     * This method checks if the given key exists in the configuration and returns its value.
     * If the key is not found or the value is empty, it logs the event and returns null.
     *
     * @param string $key
     * @return mixed|null
     */
    public function get_property($key){

        if (isset($this->config[$key]) && !empty($this->config[$key])) {
            return $this->config[$key];
        }

        Wc_Rw_Wooms_Sync_Logger::make_log('-', '-', "Missing $key", "config");
        return null;
    }

}