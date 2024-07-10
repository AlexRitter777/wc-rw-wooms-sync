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

    /**
     * Adds synchronization date meta data to an order.
     *
     * @param string $order_id
     * @return string
     */
    public function add_sync_date_meta_action(string $order_id) : string
    {
        $date = new DateTime();
        $sync_date = $date->format('Y-m-d H:i:s');
        update_post_meta($order_id, 'moy_sklad_sync_date', $sync_date);
        return $sync_date;
    }

    /**
     * Adds synchronization status meta data to an order.
     *
     * @param string $order_id
     * @param string $status
     */
    public function add_sync_status_meta_action(string $order_id, string $status) : void
    {
        update_post_meta($order_id,'moy_sklad_sync_status', $status);
    }

    /**
     * Adds a success synchronization order note to the order.
     *
     * @param string $order_id
     */
    public function add_success_sync_order_note_action(string $order_id) : void
    {
        $order = wc_get_order( $order_id );
        $sync_time = $order->get_meta('moy_sklad_sync_date');
        $note = "Успешная синхронизация заказа с Мой склад $sync_time";
        $order->add_order_note($note);
    }

    /**
     * Retrieves synchronization meta data from the order.
     *
     * @param string $order_id
     * @return array
     */
    public function get_order_meta_data($order_id) : array
    {
        $data = [];
        $order = wc_get_order( $order_id );
        $data['moy_sklad_sync_date'] = $order->get_meta('moy_sklad_sync_date');
        $data['moy_sklad_sync_status'] = $order->get_meta('moy_sklad_sync_status');

        return $data;
    }

    /**
     * Returns an array of synchronization date and status.
     *
     * @param $sync_date
     * @param $sync_status
     * @return array
     */
    public function get_values_for_response($sync_date, $sync_status) : array
    {

        return [
            'moy_sklad_sync_date' => $sync_date,
            'moy_sklad_sync_status' => $sync_status
        ];

    }

}