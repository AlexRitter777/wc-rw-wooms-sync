<?php

defined('ABSPATH') || exit;

class Wc_Rw_Wooms_Sync_Init {


    private static $instance;

    public function __construct()
    {

        // Add admin meta box
        add_action( 'add_meta_boxes', array($this, 'create_admin_meta_box') );

        // Add custom product fields  "MS code" and "Is bundle"
        add_action( 'woocommerce_product_options_general_product_data', array($this, 'add_custom_product_field' ));

        // Save custom product fields
        add_action( 'woocommerce_process_product_meta', array($this, 'save_custom_product_field') );

        // Add custom product fields  "MS code" and "Is bundle" for product variations
        add_action('woocommerce_variation_options_pricing', [$this, 'add_variation_custom_product_field'], 10, 3);

        // Save product fields  "MS code" and "Is bundle" for product variations
        add_action('woocommerce_save_product_variation', [$this, 'save_variation_custom_product_field'], 10, 2);

        // Add new column in admin order list
        add_filter('manage_edit-shop_order_columns', array($this, 'create_new_order_column'));

        // Add content to added column
        add_action( 'manage_shop_order_posts_custom_column', array($this, 'new_order_column_add_content'), 10, 2);

        // Hide plugin added order meta fields on order page
        add_filter('is_protected_meta', array($this, 'hide_meta_fields'), 10, 2);

    }

    public static function get_instance() {

        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Add new meta box to admin order page
     */
    public function create_admin_meta_box(){

        add_meta_box(
            'wc_rw_wooms_sync_box',
            'Moy sklad',
            array($this, 'get_admin_meta_box_html'),
            'shop_order'
        );

    }


    /**
     * Get meta box html content
     */
    public function get_admin_meta_box_html(){

        $order_id = $_GET['post'];
        $order = new WC_Order($order_id);
        $moy_sklad_sync_date =  !empty($order->get_meta('moy_sklad_sync_date')) ? $order->get_meta('moy_sklad_sync_date') : str_repeat('&nbsp;', 4) . '&ndash;';
        $moy_sklad_sync_status = !empty($order->get_meta('moy_sklad_sync_status')) ? $order->get_meta('moy_sklad_sync_status') : str_repeat('&nbsp;', 4) . '&ndash;';

            echo '
                <div id="wc-rw-wooms-sync-opacity">
                     <ul style="margin: 0">
                        <li >
                            <strong>Дата синхронизации: </strong><span id="wc-rw-wooms-sync-date"> '. $moy_sklad_sync_date . '</span>
                         </li >
                         <li >
                            <strong>Статус синхронизации: </strong><span id="wc-rw-wooms-sync-status">' . $moy_sklad_sync_status .'</span>
                         </li >';
            if($moy_sklad_sync_status !== 'OK'){
                echo '
                        <li id="sync_button" style="text-align: right">
                            <button id="wc-rw-wooms-sync-button" class="add_note button button-primary">Sync</button>
                        </li>
                    </ul>';
                }
            echo '
                </div>
                <span id="wc-rw-wooms-sync-spinner" class="spinner"></span>
                ';

    }

    /**
     * Add custom product fields.
     */
    public function add_custom_product_field() {

        echo '<div class="options_group">';

        woocommerce_wp_text_input(
            array(
                'id'          => '_moy_sklad_ext_code',
                'label'       => __( 'Внешний код "Мой склад"', 'woocommerce' ),
                'placeholder' => 'Внешний код',
                'desc_tip'    => 'true',
                'description' => __( 'Введите внешний код товара из Мой склад.', 'woocommerce' )
            )
        );

        woocommerce_wp_checkbox(
            array(
                'id'          => '_is_bundle',
                'label'       => __( 'Комплект', 'woocommerce' ),
                'description' => __( 'Отметьте, если это товар является комплектом.', 'woocommerce' )
            )
        );

        echo '</div>';
    }


    /**
     * Save custom product fields.
     *
     * @param int $post_id
     */
    public function save_custom_product_field($post_id) {

        $custom_field_value_external_code = !empty( $_POST['_moy_sklad_ext_code'] ) ? sanitize_text_field( $_POST['_moy_sklad_ext_code'] ) : '';

        update_post_meta( $post_id, '_moy_sklad_ext_code', $custom_field_value_external_code );

        $custom_field_value_bundle = !empty( $_POST['_moy_sklad_ext_code'] ) ?  $_POST['_is_bundle'] : '';

        update_post_meta( $post_id, '_is_bundle', $custom_field_value_bundle );

    }

    /**
     * Add custom product fields for product with variations.
     */
    public function add_variation_custom_product_field($loop, $variation_data, $variation) {

        woocommerce_wp_text_input(array(
            'id' => '_moy_sklad_ext_code[' . $loop . ']',
            'label' => __('Внешний код "Мой склад"', 'woocommerce'),
            'placeholder' => 'Внешний код',
            'desc_tip' => 'true',
            'description' => __('Введите внешний код товара из Мой склад.', 'woocommerce'),
            'wrapper_class' => 'form-row form-row-full',
            'value' => get_post_meta($variation->ID, "_moy_sklad_ext_code", true),
        ));

        woocommerce_wp_checkbox(
            array(
                'id'          => '_is_bundle[' . $loop . ']',
                'label'       => __( 'Комплект', 'woocommerce' ),
                'desc_tip' => 'true',
                'description' => __( 'Отметьте, если это товар является комплектом.', 'woocommerce' ),
                'value'     => get_post_meta($variation->ID, '_is_bundle', true),
            )
        );

    }

    /**
     * Save custom product fields for product with variations.
     *
     * @param $variation_id
     * @param $i
     */
    public function save_variation_custom_product_field($variation_id, $i){

        if (isset($_POST['_moy_sklad_ext_code'][$i])) {
            update_post_meta($variation_id, '_moy_sklad_ext_code', sanitize_text_field($_POST['_moy_sklad_ext_code'][$i]));
        }

        $custom_field_value_bundle = (!empty( $_POST['_moy_sklad_ext_code'][$i]) && isset($_POST['_is_bundle'][$i]))  ?  $_POST['_is_bundle'][$i] : '';
        update_post_meta( $variation_id, '_is_bundle', $custom_field_value_bundle );


    }
    
    
    

    /**
     * Add additional column header in admin order list
     *
     * @param $columns
     * @return mixed
     *
     */
    public function create_new_order_column( $columns )
    {
        $columns['ms_status'] = 'Moy sklad';
        return $columns;
    }

    /**
     *
     * Add sync status each order in additional column on admin orders list
     *
     * @param $column
     * @param $post_id
     */

    public function  new_order_column_add_content($column, $post_id){

        if ( 'ms_status' === $column ) {

            $order = wc_get_order($post_id);
            $status = !empty($order->get_meta('moy_sklad_sync_status')) ? $order->get_meta('moy_sklad_sync_status') : '&ndash;';

            echo '<div><p> '. $status . ' </p></div>';

        }
    }

    /**
     * Hide plugin added meta fields from admin order page
     *
     * @param $protected
     * @param $meta_key
     * @return bool|mixed
     */
    public function hide_meta_fields($protected, $meta_key) {

        if( in_array($meta_key, array('moy_sklad_sync_date', 'moy_sklad_sync_status'))) {
            return true;
        }
        return $protected;

    }

}