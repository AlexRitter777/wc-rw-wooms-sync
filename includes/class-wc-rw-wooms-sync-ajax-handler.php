<?php

defined( 'ABSPATH' ) || exit;

class Wc_Rw_Wooms_Ajax_Handler {

    /**
     * Handle ajax request - order synchronize
     */

    public static function synchronise_order_action(){

        $response=[];
        //$response['success'] = false;

        require_once WP_PLUGIN_DIR . '/wc-rw-wooms-sync/includes/class-wc-rw-wooms-sync-data-getter.php';

        $data_getter = new Wc_Rw_Wooms_Sync_Data_Getter();

        $order_id = $_POST['order_id'];

        $products_codes =  $data_getter->get_products_codes($order_id);

        require WP_PLUGIN_DIR . '/wc-rw-wooms-sync/includes/class-wc-rw-wooms-sync-api-request.php';

        $api_request = Wc_Rw_Wooms_Sync_Api_Request::get_instance();

        $products_ids = $api_request->get_products_id($products_codes);

        $order_data = $data_getter->get_order_data($order_id);

        $response = $api_request->create_order($order_data, $products_ids);


        //get products or bundles ids
        // order data -> id's array as argument

        //$response['order_data'] = $data_getter->get_order_data($order_id);
        //$response['orderId'] = $order_id;
        //$response['products_codes'] = $products_codes;
        //$response['products_ids'] = $products_ids;

        echo json_encode($response);
        //echo json_encode($response);
        wp_die();

    }



}