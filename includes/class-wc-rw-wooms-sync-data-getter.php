<?php

class Wc_Rw_Wooms_Sync_Data_Getter {


    private $config;

    public function __construct(){
        $this->config = require plugin_dir_path( __DIR__ ) . 'config/config_msid.php';
    }


    /**
     * Gets external goods codes
     */
    public function get_products_codes($order_id){

        $result = [];

        $order = wc_get_order($order_id);

        $itemsData = $this->get_order_items_data($order);

        foreach ($itemsData as $item_id => $item_data) {
            $result[$item_id]['external_code'] = $item_data['moy_sklad_ext_code'];
            $result[$item_id]['bundle'] = $item_data['is_bundle'];
        }

        return $result;

    }

    public function get_products_external_codes(string $order_id){

        //

    }


    /**
     * Returns array of items data
     *
     *
     * @param WC_Order $order
     * @return array
     */
    private function get_order_items_data(WC_Order $order)
    {
        $items_data = [];

        foreach ($order->get_items() as $key => $item) {

            $product_id = $item->get_product_id();

            $items_data[$product_id]['vat_enabled'] = (bool)$item->get_total_tax();

            $items_data[$product_id]['quantity'] = $item->get_quantity();

            $total_price_exl_vat = $item->get_total();
            $total_vat = $item->get_total_tax();
            $items_data[$product_id]['unit_price_inc_vat'] = $this->make_price(($total_price_exl_vat + $total_vat)/$item->get_quantity());

            $product = wc_get_product( $item->get_product_id() );
            $tax_rates = WC_Tax::get_rates( $product->get_tax_class() );
            $tax_rate = reset($tax_rates);
            $items_data[$product_id]['tax_rate'] = $tax_rate['rate'];


            $moy_sklad_ext_code = get_post_meta($product->get_id(), '_moy_sklad_ext_code', true);

            $items_data[$product_id]['moy_sklad_ext_code'] = $moy_sklad_ext_code ? $moy_sklad_ext_code : $this->config['unknown_product_external'];

            $items_data[$product_id]['is_bundle'] = get_post_meta($product->get_id(), '_is_bundle', true);

        }
        return $items_data;

    }




    public function get_order_data($order_id){

        $order_data = [];

        //Order object
        $order = wc_get_order($order_id);

        $exchange_rates = require plugin_dir_path( __DIR__ ) . 'config/exchange_rates.php';
        $prefixes = require plugin_dir_path( __DIR__ ) . 'config/prefixes.php';

        if ($order) {

            $order_data['order_number'] = $this->get_order_number($order_id, $prefixes);
            $order_data['customer_note'] = $order->get_customer_note( 'edit' );
            $order_data['counterparty'] = $this->config['counterparty'];//make method
            $order_data['organization'] = $this->config['organization'];//make method
            $date = new DateTime();
            $order_data['moment'] = $date->format('Y-m-d H:i:s') . '.000'; //make method
            $order_data['state'] = $this->get_order_state($order, $this->config); //remake!
            //$result['state'] = $order->get_payment_method();
            $order_data['order_rate'] = $this->get_order_rate($order, $exchange_rates);
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

        }

        return $order_data;


    }




    private function get_order_state($order, $config)
    {

        $wooStatus =  $order->get_payment_method();
        if($wooStatus == 'cod') {
            return $config['state']['cod'];
        }
        return $config['state']['new'];

    }

    /**
     * Get current order exchange rate
     *
     * Method compare currency code of current order from WooCommerce
     * with user config array Currency => Exchange rate
     *
     * @param object $order
     * @param array $currencies
     * @return float|boolean
     */
    public function get_order_rate(object $order, array $currencies)
    {
        $curr = $order->get_currency();
        foreach ($currencies as $code => $rate) {
            if ($code == $curr) {
                return $rate;
            }
        }

        return false;
    }

    /**
     * Creates order number from order ID and prefix
     *
     * @param int $order_id
     * @param array $prefixes
     * @return string
     */
    public function get_order_number(int $order_id, array $prefixes) : string
    {
        $site_URL = $_SERVER['SERVER_NAME'];
        $order_prefix = '';
        foreach ($prefixes as $url => $prefix) {
            if ($site_URL == $url) $order_prefix = $prefix;
        }
        return $order_id . $order_prefix;
    }

    private function get_order_currency(object $order, array $config)
    {

        $order_currency = $order->get_currency();
        return $config['currency'][$order_currency];

    }

    private function get_order_store($config)
    {
        return $config['store']['main'];
    }

    private function get_order_project(array $config) : string
    {
        $site_URL = $_SERVER['SERVER_NAME'];
        return $config['project'][$site_URL];

    }

    private function get_customer_name(WC_Order $order, array $config) : array
    {
        $result['id'] = $config['attributes']['name'];

        $result['value'] = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();

        return $result;

    }

    private function get_customer_city(WC_Order $order, array $config) : array
    {
        $result['id'] = $config['attributes']['city'];

        $result['value'] = $order->get_billing_city();

        return $result;

    }

    private function get_customer_shipping_address(WC_Order $order, array $config) : array
    {

        $result['id'] = $config['attributes']['shipping_address'];

        $first_name = $order->get_shipping_first_name() ? $order->get_shipping_first_name() : $order->get_billing_first_name();

        $last_name = $order->get_shipping_last_name() ? $order->get_shipping_last_name() : $order->get_billing_last_name();

        $address_1 = $order->get_shipping_address_1() ? $order->get_shipping_address_1() : $order->get_billing_address_1();

        $address_2 = $order->get_shipping_address_2() ? $order->get_shipping_address_2() : $order->get_billing_address_2();

        $postcode = $order->get_shipping_postcode() ? $order->get_shipping_postcode() : $order->get_billing_postcode();

        $city = $order->get_shipping_city() ? $order->get_shipping_city() : $order->get_billing_city();

        $phone_number = $order->get_billing_phone();

        $email = $order->get_billing_email();

        $result['value'] = "$first_name $last_name\n$address_1\n" . (!empty($address_2) ? "$address_2\n" : "") . "$postcode $city\ntelefon: $phone_number\ne-mail: $email";

        $result['id'] = $config['attributes']['shipping_address'];

        return $result;


    }

    private function get_order_account_manager(array $config) : array
    {

        $result['value'] = 'api';
        $result['id'] = $config['attributes']['account_manager'];

        return $result;

    }

    private function get_order_payment_method(WC_Order $order, array $config) : array
    {

        $payment_method =  $order->get_payment_method();
        $result['value'] = $config['custom_payment_methods'][$payment_method];
        $result['id'] = $config['attributes']['payment_method'];

        return $result;
    }

    private function get_order_country(WC_Order $order, array $config) : array
    {

        $country = $order->get_shipping_country() ? $order->get_shipping_country() : $order->get_billing_country();
        $result['value'] = $config['custom_countries'][$country];
        $result['id'] = $config['attributes']['country'];

        return $result;
    }

    private function is_order_has_vat(WC_Order $order) : bool
    {
        if (!$order->get_total_tax()) return false;

        return true;

    }

    private function get_order_shipping_data(WC_Order $order, array $config)
    {
        $result = [];
        $shipping_exc_vat = $order->get_shipping_total();
        $shipping_vat = $order->get_shipping_tax();
        $result['price'] = $this->make_price($shipping_exc_vat + $shipping_vat);
        $result['vat'] = $this->getStandardVatRate();
        $result['id'] = $config['shipping'];

        return $result;

    }

    private function get_order_fees_data(WC_Order$order, array $config)
    {
        $result = null;
        if($fees = $order->get_fees()) {
            $fee = reset($fees);
            $fee_exc_vat = $fee->get_total();
            $fee_vat = $fee->get_total_tax();
            $result['price'] = $this->make_price($fee_exc_vat + $fee_vat);
            $result['vat'] = $this->getStandardVatRate();
            $result['id'] = $config['cod_payment'];
        }
        return $result;
    }


    private function make_price(float $amount) : float {

        /*$string_amount =  (string) $amount * 100;
        if(!strpos($string_amount, '.')) return $string_amount . '.0';
        return $string_amount;*/

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


}