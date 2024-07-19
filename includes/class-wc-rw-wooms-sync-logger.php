<?php

defined( 'ABSPATH' ) || exit;


class Wc_Rw_Wooms_Sync_Logger
{

    public static function make_log($order_id, $error_code, $error_message, $error_origin, $error_type, $advanced_api_message = '-') {

        error_log("[" . date('Y-m-d H:i:s') . "] | Order: {$order_id} | Error code: {$error_code} | Error message: {$error_message} | Error origin: {$error_origin} | Error type: {$error_type} | Info: {$advanced_api_message} \n============================\n", 3, plugin_dir_path( __DIR__ ) . 'tmp/errors.log');

    }


}