<?php

defined( 'ABSPATH' ) || exit;

class Wc_Rw_Wooms_Ajax_Handler {

    /**
     * Handles AJAX request for synchronizing an order with MoySklad.
     *
     * This method retrieves the order ID from the POST request, gets the external product codes,
     * retrieves the corresponding product IDs from MoySklad, and creates an order in MoySklad.
     * It sends a JSON response indicating success or failure.
     */
    public static function synchronise_order_action(){
        // Ensure the request is coming from an authorized source
        check_ajax_referer('wc_rw_wooms_sync_ajax_nonce', 'security');

        $data_getter = new Wc_Rw_Wooms_Sync_Data_Getter();

        // Get the order ID from the POST request
        $order_id = isset($_POST['order_id']) ? sanitize_text_field($_POST['order_id']) : '';

        // Check if order ID is valid (3 to 6 digits)
        if (empty($order_id) || !preg_match('/^\d{3,6}$/', $order_id)) {
            wp_send_json_error('Invalid order ID');
        }

        // Get the external product codes for the order
        $products_external_codes =  $data_getter->get_products_external_codes($order_id);

        $api_request = Wc_Rw_Wooms_Sync_Api_Request::get_instance();

        // Get the product IDs from MoySklad
        $products_ids = $api_request->get_products_ids($products_external_codes, $order_id);

        if(!$products_ids){
            wp_send_json_error('Failed to retrieve product IDs from MoySklad.');
        }

        // Get the order data
        $order_data = $data_getter->get_order_data($order_id);
        if(!$order_data){
            wp_send_json_error('Invalid order data.');
        }


        // Create the order in MoySklad
        $response = $api_request->create_order($order_data, $products_ids, $order_id);

        if(!$products_ids){
            wp_send_json_error('Failed to create order in MoySklad.');
        }

        // Send success response
        wp_send_json_success(true);

    }


}