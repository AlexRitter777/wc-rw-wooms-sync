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

        $data_getter = Wc_Rw_Wooms_Sync_Data_Getter::get_instance();

        // Get the order ID from the POST request
        $order_id = isset($_POST['order_id']) ? sanitize_text_field($_POST['order_id']) : '';

        // Check if order ID is valid (3 to 6 digits)
        if (empty($order_id) || !preg_match('/^\d{3,6}$/', $order_id)) {
            wp_send_json_error('Invalid order ID');
        }

        $plugin_actions = Wc_Rw_Wooms_Sync_Actions::get_instance();

        $sync_date = $plugin_actions->add_sync_date_meta_action($order_id);

        // Get the external product codes for the order
        $products_external_codes =  $data_getter->get_products_external_codes($order_id);

        $api_request = Wc_Rw_Wooms_Sync_Api_Request::get_instance();

        // Get the product IDs from MoySklad
        $products_ids = $api_request->get_products_ids($products_external_codes, $order_id);

        if(!$products_ids){
            $plugin_actions->add_sync_status_meta_action($order_id, 'ERROR');
            $data = [
                'values' => $plugin_actions->get_values_for_response($sync_date, 'ERROR'),
                'message' => 'Failed to retrieve product IDs from MoySklad.'
            ];
            wp_send_json_error($data);
        }

        // Get the order data
        $order_data = $data_getter->get_order_data($order_id);
        if(!$order_data){
            $plugin_actions->add_sync_status_meta_action($order_id, 'ERROR');
            $data = [
                'values' => $plugin_actions->get_values_for_response($sync_date, 'ERROR'),
                'message' => 'Invalid order data.'
            ];
            wp_send_json_error($data);

        }


        // Create the order in MoySklad
        $response = $api_request->create_order($order_data, $products_ids, $order_id);

        if(!$response){
            $plugin_actions->add_sync_status_meta_action($order_id, 'ERROR');
            $data = [
                'values' => $plugin_actions->get_values_for_response($sync_date, 'ERROR'),
                'message' => 'Failed to create order in MoySklad.'
                ];
            wp_send_json_error($data);
        }

        // Send success response
        $plugin_actions->add_sync_status_meta_action($order_id, 'OK');
        $plugin_actions->add_success_sync_order_note_action($order_id);

        $data = [
            'values' => $plugin_actions->get_values_for_response($sync_date, 'OK'),
            'message' => 'Order successfully created.'
        ];
        wp_send_json_success($data);

    }


}