<?php
defined( 'ABSPATH' ) || exit;

class Wc_Rw_Wooms_Sync_Actions {

    private static $instance;

    public static function get_instance() {

        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

}