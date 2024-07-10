<?php

defined( 'ABSPATH' ) || exit;

class Wc_Rw_Wooms_Sync_Data_Getter {

    private static $instance;
    private $config;

    private function __construct(){
        $this->config = Wc_Rw_Wooms_Sync_Config::get_instance();
    }

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    /**
     * Retrieves the external codes and bundle status of products in an order.
     *
     * @param string $order_id
     * @return array
     */
    public function get_products_external_codes(string $order_id) : array
    {

        $result = [];

        $order = wc_get_order($order_id);

        foreach ($order->get_items() as $key => $item){

            $product_id = $item->get_product_id();

            $product = wc_get_product( $item->get_product_id());

            $moy_sklad_ext_code = get_post_meta($product->get_id(), '_moy_sklad_ext_code', true);

            $result[$product_id]['moy_sklad_ext_code'] = !empty($moy_sklad_ext_code) ? $moy_sklad_ext_code : $this->config->get_property('unknown_product_external');

            $result[$product_id]['is_bundle'] = get_post_meta($product->get_id(), '_is_bundle', true);

        }

        return $result;
    }

    /**
     * Retrieves comprehensive order data for a given order ID.
     *
     * @param $order_id
     * @return array|null
     */
    public function get_order_data($order_id){

        $order_data = null;

        $order = wc_get_order($order_id);

        $prefixes = require plugin_dir_path( __DIR__ ) . 'config/prefixes.php';

        if ($order) {

            $order_data['order_number'] = $this->get_order_number($order_id, $prefixes);
            $order_data['customer_note'] = $order->get_customer_note( 'edit' );
            $order_data['counterparty'] = $this->config->get_property('counterparty');
            $order_data['organization'] = $this->config->get_property('organization');
            $order_data['moment'] = $this->get_order_moment();
            $order_data['state'] = $this->get_order_state($order, $this->config);
            $order_data['currency'] = $this->get_order_currency($order, $this->config);
            $order_data['store'] = $this->get_order_store($this->config);
            $order_data['project'] = $this->get_order_project($this->config);
            $order_data['customer_name'] = $this->get_customer_name($order, $this->config);
            $order_data['customer_city'] = $this->get_customer_city($order, $this->config);
            $order_data['customer_shipping_address'] = $this->get_customer_shipping_address($order, $this->config);
            $order_data['account_manager'] = $this->get_order_account_manager($this->config);
            $order_data['payment_method'] = $this->get_order_payment_method($order, $this->config);
            $order_data['country'] = $this->get_order_country($order, $this->config);
            $order_data['vat_enabled'] = $this->is_order_has_vat($order);
            $order_data['vat_included'] = $order_data['vat_enabled'];
            $order_data['items_data'] = $this->get_order_items_data($order, $this->config);
            $order_data['shipping_data'] = $this->get_order_shipping_data($order, $this->config);
            $order_data['fees_data'] = $this->get_order_fees_data($order, $this->config);

            if(!$this->check_data($order_data)) {
                return null;
            }

        }
        Wc_Rw_Wooms_Sync_Logger::make_log($order_id, '-', "Order not found", "data_getter", "internal_error");
        return $order_data;

    }

    /**
     * Retrieves the order items data from the order and configuration.
     *
     *
     * @param WC_Order $order
     * @param Wc_Rw_Wooms_Sync_Config $config
     * @return array
     */
    private function get_order_items_data(WC_Order $order, Wc_Rw_Wooms_Sync_Config $config) : array
    {
        $items_data = [];

        foreach ($order->get_items() as $key => $item) {

            $product_id = $item->get_product_id();

            // VAT enabled
            $items_data[$product_id]['vat_enabled'] = (bool)$item->get_total_tax();

            // Quantity
            $items_data[$product_id]['quantity'] = $item->get_quantity();

            // Total price including VAT
            $total_price_exl_vat = $item->get_total();
            $total_vat = $item->get_total_tax();
            $items_data[$product_id]['unit_price_inc_vat'] = $this->make_price(($total_price_exl_vat + $total_vat)/$item->get_quantity());

            // Tax rate
            $product = wc_get_product( $item->get_product_id() );
            $tax_rates = WC_Tax::get_rates( $product->get_tax_class() );
            $tax_rate = reset($tax_rates);
            $items_data[$product_id]['tax_rate'] = $tax_rate['rate'];

            // External code
            $moy_sklad_ext_code = get_post_meta($product->get_id(), '_moy_sklad_ext_code', true);
            $items_data[$product_id]['moy_sklad_ext_code'] = $moy_sklad_ext_code ? $moy_sklad_ext_code : $config->get_property('unknown_product_external');

            // Is bundle
            $items_data[$product_id]['is_bundle'] = get_post_meta($product->get_id(), '_is_bundle', true);

        }

        return $items_data;

    }

    /**
     * Retrieves the order state based on the payment method and configuration.
     *
     * This method checks the payment method of the order and returns the corresponding state
     * from the provided configuration. If the payment method is 'cod' (Cash on Delivery),
     * it returns the 'cod' state, otherwise it returns the 'new' state.
     *
     * @param WC_Order $order
     * @param Wc_Rw_Wooms_Sync_Config $config
     * @return string
     */
    private function get_order_state(WC_Order $order, Wc_Rw_Wooms_Sync_Config $config) : string
    {
        $state = $config->get_property('state');
        $wooStatus =  $order->get_payment_method();
        if($wooStatus == 'cod') {
            return $state['cod'];
        }
        return $state['new'];

    }


    /**
     * Creates order number from order ID and prefix
     *
     * @param int $order_id
     * @param array $prefixes
     * @return string|null
     */
    public function get_order_number(int $order_id, array $prefixes)
    {
        $site_URL = $_SERVER['SERVER_NAME'];
        $order_prefix = '';
        foreach ($prefixes as $url => $prefix) {
            if ($site_URL == $url) {
                $order_prefix = $prefix;
                break;
            }
        }
        if(empty($order_prefix)){
            Wc_Rw_Wooms_Sync_Logger::make_log($order_id, '-', "Missing prefix for $site_URL", "data_getter", "internal_error");
            return null;
        }
        return $order_id . $order_prefix;
    }


    /**
     * Retrieves the order currency based on the order's currency and configuration.
     *
     * @param WC_Order $order
     * @param Wc_Rw_Wooms_Sync_Config $config
     * @return string|null
     */
    private function get_order_currency(WC_Order $order, Wc_Rw_Wooms_Sync_Config $config)
    {
        $currency = $config->get_property('currency');

        if(!$currency) return null;

        $order_currency = $order->get_currency();

        if (!isset($currency[$order_currency]) || empty($currency[$order_currency])) {
            Wc_Rw_Wooms_Sync_Logger::make_log($order->get_order_number(), '-', "Missing currency for $order_currency", "data_getter", "internal_error");
            return null;
        };
        return $currency[$order_currency];
    }




    /**
     * Retrieves the main store configuration.
     *
     * @param Wc_Rw_Wooms_Sync_Config $config The configuration object containing store information.
     * @return string The main store configuration.
     */
    private function get_order_store(Wc_Rw_Wooms_Sync_Config $config) : string
    {
        $store = $config->get_property('store');
        return $store['main'];
    }


    /**
     * Retrieves the project configuration based on the site URL.
     *
     * @param Wc_Rw_Wooms_Sync_Config $config
     * @return string|null
     */
    private function get_order_project(Wc_Rw_Wooms_Sync_Config $config)
    {
        $site_URL = $_SERVER['SERVER_NAME'];
        $project = $config->get_property('project');

        if(!$project) return null;

        if (!isset($project[$site_URL]) || empty($project[$site_URL])) {
            Wc_Rw_Wooms_Sync_Logger::make_log('-', '-', "Missing project for $site_URL", "data_getter", "internal_error");
            return null;
        };

        return $project[$site_URL];
    }

    /**
     * Retrieves the customer's name from the order and the corresponding attribute ID from the configuration.
     *
     * @param WC_Order $order
     * @param Wc_Rw_Wooms_Sync_Config $config
     * @return array
     */
    private function get_customer_name(WC_Order $order, Wc_Rw_Wooms_Sync_Config $config)
    {
        $attributes = $config->get_property('attributes');

        if(!$attributes) return null;

        if (!isset($attributes['name']) || empty($attributes['name'])) {
            Wc_Rw_Wooms_Sync_Logger::make_log('-', '-', "Missing attr. name", "data_getter", "internal_error");
            return null;
        };

        $result['id'] = $attributes['name'];
        $result['value'] = $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name();

        return $result;

    }

    /**
     * Retrieves the customer's city from the order and the corresponding attribute ID from the configuration.
     *
     * @param WC_Order $order
     * @param Wc_Rw_Wooms_Sync_Config $config
     * @return array|null
     */
    private function get_customer_city(WC_Order $order, Wc_Rw_Wooms_Sync_Config $config)
    {
        $attributes = $config->get_property('attributes');

        if(!$attributes) return null;

        if (!isset($attributes['city']) || empty($attributes['city'])) {
            Wc_Rw_Wooms_Sync_Logger::make_log('-', '-', "Missing attr. city", "data_getter", "internal_error");
            return null;
        };

        $result['id'] = $attributes['city'];
        $result['value'] = $order->get_billing_city();

        return $result;
    }


    /**
     * Retrieves the customer's shipping address from the order and the corresponding attribute ID from the configuration.
     *
     * @param WC_Order $order
     * @param Wc_Rw_Wooms_Sync_Config $config
     * @return array|null
     */
    private function get_customer_shipping_address(WC_Order $order, Wc_Rw_Wooms_Sync_Config $config)
    {
        $attributes = $config->get_property('attributes');

        if(!$attributes) return null;

        if (!isset($attributes['shipping_address']) || empty($attributes['shipping_address'])) {
            Wc_Rw_Wooms_Sync_Logger::make_log('-', '-', "Missing attr. sipping address", "data_getter", "internal_error");
            return null;
        };

        $first_name = $order->get_shipping_first_name();
        $last_name = $order->get_shipping_last_name();
        $address_1 = $order->get_shipping_address_1();
        $address_2 = $order->get_shipping_address_2();
        $postcode = $order->get_shipping_postcode();
        $city = $order->get_shipping_city();
        $phone_number = $order->get_billing_phone();
        $email = $order->get_billing_email();

        $result['value'] = "$first_name $last_name\n$address_1\n" . (!empty($address_2) ? "$address_2\n" : "") . "$postcode $city\ntelefon: $phone_number\ne-mail: $email";
        $result['id'] = $attributes['shipping_address'];

        return $result;
    }

    /**
     * Retrieves the account manager attribute ID from the configuration and assigns the value 'api'.
     *
     * @param Wc_Rw_Wooms_Sync_Config $config
     * @return array|null
     */
    private function get_order_account_manager(Wc_Rw_Wooms_Sync_Config $config)
    {

        $attributes = $config->get_property('attributes');

        if(!$attributes) return null;

        if (!isset($attributes['account_manager']) || empty($attributes['account_manager'])) {
            Wc_Rw_Wooms_Sync_Logger::make_log('-', '-', "Missing attr. acc. manager", "data_getter", "internal_error");
            return null;
        };

        $result['value'] = 'api';
        $result['id'] = $attributes['account_manager'];

        return $result;

    }

    /**
     * Retrieves the payment method attribute ID and value from the configuration.
     *
     * @param WC_Order $order
     * @param Wc_Rw_Wooms_Sync_Config $config
     * @return array|null
     */
    private function get_order_payment_method(WC_Order $order, Wc_Rw_Wooms_Sync_Config $config)
    {

        $attributes = $config->get_property('attributes');
        if(!$attributes) return null;

        $custom_payment_methods =  $config->get_property('custom_payment_methods');
        if(!$custom_payment_methods) return null;

        if (!isset($attributes['payment_method']) || empty($attributes['payment_method'])) {
            Wc_Rw_Wooms_Sync_Logger::make_log('-', '-', "Missing attr. payment method", "data_getter", "internal_error");
            return null;
        };

        $payment_method =  $order->get_payment_method();

        if (!isset($custom_payment_methods[$payment_method]) || empty($custom_payment_methods[$payment_method])) {
            Wc_Rw_Wooms_Sync_Logger::make_log('-', '-', "Missing custom payment method for $payment_method", "data_getter", "internal_error");
            return null;
        };

        $result['value'] = $custom_payment_methods[$payment_method];
        $result['id'] = $attributes['payment_method'];

        return $result;
    }


    /**
     * Retrieves the country attribute ID and value from the configuration.
     *
     * @param WC_Order $order
     * @param Wc_Rw_Wooms_Sync_Config $config
     * @return array|null
     */
    private function get_order_country(WC_Order $order, Wc_Rw_Wooms_Sync_Config $config)
    {
        $attributes = $config->get_property('attributes');
        if(!$attributes) return null;

        $custom_countries =  $config->get_property('custom_countries');
        if(!$custom_countries) return null;

         if (!isset($attributes['country']) || empty($attributes['country'])) {
            Wc_Rw_Wooms_Sync_Logger::make_log('-', '-', "Missing attr. country", "data_getter", "internal_error");
            return null;
        }

        $country = $order->get_shipping_country();

        if (!isset($custom_countries[$country]) || empty($custom_countries[$country])) {
            Wc_Rw_Wooms_Sync_Logger::make_log('-', '-', "Missing custom country method for $country", "data_getter", "internal_error");
            return null;
        }

        $result['value'] = $custom_countries[$country];
        $result['id'] = $attributes['country'];

        return $result;
    }


    /**
     * Checks if the order has VAT (tax).
     *
     * @param WC_Order $order
     * @return bool
     */
    private function is_order_has_vat(WC_Order $order) : bool
    {
        if (!$order->get_total_tax()) return false;

        return true;

    }

    /**
     * Retrieves the order shipping data from the order and configuration.
     *
     * @param WC_Order $order
     * @param Wc_Rw_Wooms_Sync_Config $config
     * @return array|null
     */
    private function get_order_shipping_data(WC_Order $order, Wc_Rw_Wooms_Sync_Config $config)
    {

        $shipping = $config->get_property('shipping');
        if(!$shipping) return null;

        $result = [];

        $shipping_exc_vat = $order->get_shipping_total();
        $shipping_vat = $order->get_shipping_tax();
        $result['price'] = $this->make_price($shipping_exc_vat + $shipping_vat);
        $result['vat'] = $this->getStandardVatRate();
        $result['id'] = $shipping;

        return $result;

    }

    /**
     * Retrieves the order fees data from the order and configuration.
     *
     * @param WC_Order $order
     * @param Wc_Rw_Wooms_Sync_Config $config
     * @return false|null
     */
    private function get_order_fees_data(WC_Order $order, Wc_Rw_Wooms_Sync_Config $config)
    {
        $result = false;

        if($fees = $order->get_fees()) {
            $fee = reset($fees);
            $fee_exc_vat = $fee->get_total();
            $fee_vat = $fee->get_total_tax();

            $fees = $config->get_property('cod_payment');
            if(!$fees) return null;

            $result['price'] = $this->make_price($fee_exc_vat + $fee_vat);
            $result['vat'] = $this->getStandardVatRate();
            $result['id'] = $fees;
        }

        return $result;
    }

    /**
     * Converts the amount to a different format by multiplying it by 100.
     *
     * @param float $amount
     * @return float
     */
    private function make_price(float $amount) : float {

        return $amount * 100;
    }

    /**
     * Return standard VAT rate from WoCommerce
     *
     * @return int
     */
    private function getStandardVatRate() : int {
        $standardVatRates = WC_Tax::get_rates_for_tax_class('standard');
        $standardVatRateObj = reset($standardVatRates);
        return (int)($standardVatRateObj->tax_rate);
    }

    /**
     * Retrieves the current date and time formatted for the order moment.
     *
     * @return string
     */
    private function get_order_moment() : string
    {
        $date = new DateTime();
        return $date->format('Y-m-d H:i:s') . '.000';
    }

    /**
     * Checks if the array contains any null values.
     *
     * @param array $data
     * @return bool
     */
    private function check_data(array $data) {
        foreach ($data as $value) {
            if (is_null($value)){
                return false;
            }
        }
        return true;
    }


}