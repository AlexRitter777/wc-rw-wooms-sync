<?php

defined( 'ABSPATH' ) || exit;


class Wc_Rw_Wooms_Sync_Api_Request{

    private static $instance;
    private $credentials;
    private $config;

    private function __construct(){

        $api_config = require plugin_dir_path( __DIR__ ) . 'config/config_api.php';
        $user_name = $api_config['name'];
        $password = $api_config['password'];
        $this->credentials = base64_encode("$user_name:$password");
        $this->config = Wc_Rw_Wooms_Sync_Config::get_instance();

    }


    public static function get_instance()
    {

        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Retrieves product IDs from MoySklad using external codes.
     *
     * This method performs HTTP requests to the MoySklad API to obtain product IDs based on provided external codes.
     * In case of request or response processing errors, the method returns false.
     *
     * @param array $external_codes Associative array of external product codes where the key is the product ID and the value is the product data.
     * @param int $order_id The order ID for logging errors.
     * @return array|false Associative array with product IDs from MoySklad or false in case of an error.
     */
    public function get_products_ids(array $external_codes, int $order_id){

        $product_ms_ids = [];

        foreach ($external_codes as $product_id => $product_data) {

            $base_url = $product_data['is_bundle'] ? API_REQUEST_BUNDLE_URI : API_REQUEST_PRODUCT_URI;

            $url = $base_url . $product_data['moy_sklad_ext_code'];

            $args = array(
                'headers' => array(
                    'Authorization' => 'Basic ' . $this->credentials,
                    'Content-Type'  => 'application/json'
                )
            );

            $response = wp_remote_get($url, $args);

            if(is_wp_error($response)) {

                Wc_Rw_Wooms_Sync_Logger::make_log($order_id, '-', $response->get_error_message(), 'product_id_request', 'wp_remote_get_error');
                return false;

            }

            $response_code = wp_remote_retrieve_response_code($response);
            if($response_code !== 200) {

                Wc_Rw_Wooms_Sync_Logger::make_log($order_id, $response_code, wp_remote_retrieve_response_message($response), 'product_id_request', 'api_moy_sklad');
                return false;

            }

            $body = wp_remote_retrieve_body($response);
            $product = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {

                Wc_Rw_Wooms_Sync_Logger::make_log($order_id, '-', json_last_error_msg(), 'product_id_request', 'json_decode_error');
                return false;

            }

            $product_ms_ids[$product_id]['type'] = $product_data['is_bundle'] ? 'bundle' : 'product';

            if(isset($product['rows'][0]['meta']['href'])){
                $product_ms_ids[$product_id]['href'] = $product['rows'][0]['meta']['href'];
            } else {
                $product_ms_ids[$product_id]['href'] = $this->config->get_property('unknown_product_id');
                $product_ms_ids[$product_id]['type'] = 'product';
            }

            $product_ms_ids[$product_id]['metadataHref'] = $product['rows'][0]['meta']['metadataHref'] ?? 'https://api.moysklad.ru/api/remap/1.2/entity/product/metadata';

        }

        return  $product_ms_ids;
    }



    /**
     * Creates an order in MoySklad using provided order data and product IDs.
     *
     * This method performs a POST request to the MoySklad API to create an order based on the provided data.
     * In case of request or response processing errors, the method logs the error and returns false.
     *
     * @param array $order_data Associative array containing order data.
     * @param array $product_ids Associative array of product IDs from MoySklad.
     * @param int $order_id The order ID for logging errors.
     * @return bool True if the order was created successfully, false otherwise.
     */
    public function create_order(array $order_data, array $product_ids, int $order_id){

        $url = API_CREATE_ORDER;

        $order_data = [
            "name" => $order_data['order_number'],
            "description" => $order_data['customer_note'],
            "organization" => [
                "meta" => [
                    "href" => $order_data['organization'],
                    "type" => "organization",
                    "mediaType" => "application/json"
                ]
            ],
            "moment" => $order_data['moment'],
            "applicable" => false,
            "agent" => [
                "meta" => [
                    "href" => $order_data['counterparty'],
                    "type" => "counterparty",
                    "mediaType" => "application/json"
                ]
            ],
            "state" => [
                "meta" => [
                    "href" => $order_data['state'],
                    "type" => "state",
                    "mediaType" => "application/json"
                ]
            ],
            "rate" => [
                "currency" => [
                    "meta" => [
                        "href" => $order_data['currency'],
                        "metadataHref" => "https://api.moysklad.ru/api/remap/1.2/entity/currency/metadata",
                        "type" => "currency",
                        "mediaType" => "application/json"
                    ]
                ],
            ],
            "store" => [
                "meta" => [
                    "href" => $order_data['store'],
                    "metadataHref" => "https://api.moysklad.ru/api/remap/1.2/entity/store/metadata",
                    "type" => "store",
                    "mediaType" => "application/json"
                ]
            ],
            "project" => [
                "meta" => [
                    "href" => $order_data['project'],
                    "metadataHref" => "https://api.moysklad.ru/api/remap/1.2/entity/project/metadata",
                    "type" => "project",
                    "mediaType" => "application/json"
                ]
            ],
            "attributes" => [
                [
                    "meta" => [
                        "href" => $order_data['customer_name']['id'],
                        "type" => "attributemetadata",
                        "mediaType" => "application/json"
                    ],
                    "type" => "string",
                    "value" => $order_data['customer_name']['value']
                ],
                [
                    "meta" => [
                        "href" => $order_data['customer_city']['id'],
                        "type" => "attributemetadata",
                        "mediaType" => "application/json"
                    ],
                    "type" => "string",
                    "value" => $order_data['customer_city']['value']
                ],
                [
                    "meta" => [
                        "href" => $order_data['customer_shipping_address']['id'],
                        "type" => "attributemetadata",
                        "mediaType" => "application/json"
                    ],
                    "type" => "text",
                    "value" => $order_data['customer_shipping_address']['value']
                ],
                [
                    "meta" => [
                        "href" => $order_data['account_manager']['id'],
                        "type" => "attributemetadata",
                        "mediaType" => "application/json"
                    ],
                    "type" => "string",
                    "value" => $order_data['account_manager']['value']
                ],
                [
                    "meta" => [
                        "href" => $order_data['payment_method']['id'],
                        "type" => "attributemetadata",
                        "mediaType" => "application/json"
                    ],
                    "type" => "customentity",
                    "value" => [
                        "meta" => [
                            "href" => $order_data['payment_method']['value'],
                            "metadataHref" => "https://api.moysklad.ru/api/remap/1.2/context/companysettings/metadata/customEntities/0822cef1-77cd-11e9-9ff4-34e800061338",
                            "type" => "customentity",
                            "mediaType" => "application/json"
                        ]
                    ]
                ],
                [
                    "meta" => [
                        "href" => $order_data['country']['id'],
                        "type" => "attributemetadata",
                        "mediaType" => "application/json"
                    ],
                    "type" => "customentity",
                    "value" => [
                        "meta" => [
                            "href" => $order_data['country']['value'],
                            "metadataHref" => "https://api.moysklad.ru/api/remap/1.2/context/companysettings/metadata/customEntities/cf95b85c-788f-11e9-9109-f8fc00054056",
                            "type" => "customentity",
                            "mediaType" => "application/json"
                        ]
                    ]
                ],
                [
                    "meta" => [
                        "href" => "https://api.moysklad.ru/api/remap/1.2/entity/customerorder/metadata/attributes/45864eaa-7d26-11e9-9ff4-34e80001ad6a",
                        "type" => "attributemetadata",
                        "mediaType" => "application/json"
                    ],
                    "id" => "45864eaa-7d26-11e9-9ff4-34e80001ad6a",
                    "name" => "Priority",
                    "type" => "boolean",
                    "value" => true
                ]
            ],
            "vatEnabled" => $order_data['vat_enabled'],
            "vatIncluded" => $order_data['vat_included'],
            "positions" => array_merge($this->create_positions_array($order_data['items_data'], $product_ids ), $this->create_services_array($order_data['fees_data'], $order_data['shipping_data']))
        ];

        // Encode the order data as JSON
        $order_data_json = json_encode($order_data);

        $args = array(
            'body'        => $order_data_json,
            'headers'     => array(
                'Authorization' => 'Basic ' . $this->credentials,
                'Content-Type'  => 'application/json'
            ),
            'method'      => 'POST',
            'data_format' => 'body',
        );

        $response = wp_remote_post($url, $args);

        // Check for errors in the response
        if(is_wp_error($response)) {
            Wc_Rw_Wooms_Sync_Logger::make_log($order_id, '-', $response->get_error_message(), 'create_order_request', 'wp_remote_post_error');
            return false;
        }

        // Retrieve and check the response code
        $response_code = wp_remote_retrieve_response_code($response);
        if($response_code !== 200) {

            $response_body = wp_remote_retrieve_body($response);
            $decoded_response_body = json_decode($response_body, true);
            $advanced_error_message = $decoded_response_body['errors'][0]['error'] ?? 'Unknown error';

            Wc_Rw_Wooms_Sync_Logger::make_log($order_id, $response_code, wp_remote_retrieve_response_message($response), 'create_order_request', 'api_moy_sklad', $advanced_error_message);
            return false;
        }

        return true;

    }


    /**
     * Creates an array of positions for the order from provided product data and product IDs.
     *
     * This method constructs an array of positions using the product details and their corresponding IDs from MoySklad.
     *
     * @param array $products Associative array of product details, where the key is the product ID and the value is the product data.
     * @param array $product_ids Associative array of product IDs from MoySklad, where the key is the product ID and the value is the product metadata.
     * @return array An array of positions formatted for the MoySklad order.
     */
    private function create_positions_array(array $products, array $product_ids) {

        $positions = [];

        foreach ($products as $id => $product_data) {

            $positions[] =

                [
                    "quantity" => $product_data['quantity'],
                    "price" => $product_data['unit_price_inc_vat'],
                    "discount" => 0.0,
                    "vat" => $product_data['tax_rate'],
                    "vatEnabled" => $product_data['vat_enabled'],
                    "assortment" => [
                        "meta" => [
                            "href" => $product_ids[$id]['href'],
                            "metadataHref" => $product_ids[$id]['metadataHref'],
                            "type" => $product_ids[$id]['type'],
                            "mediaType" => "application/json"
                        ]
                    ]
                ];


        }

        return $positions;
    }

    /**
     * Creates an array of services for the order from provided fees and shipping data.
     *
     * This method constructs an array of services using the fees and shipping details provided.
     *
     * @param bool|array $fees_data Associative array containing the fees data.
     * @param array $shipping_data Associative array containing the shipping data.
     * @return array An array of services formatted for the MoySklad order.
     */
    private function create_services_array($fees_data, array $shipping_data) {

        $services = [];

        if($fees_data){

            $services[] = [

                "quantity" => 1.0,
                "price" => $fees_data['price'],
                "discount" => 0.0,
                "vat" => $fees_data['vat'],
                "vatEnabled" => true,
                "assortment" => [
                    "meta" => [
                        "href" => $fees_data['id'],
                        "metadataHref" => "https://api.moysklad.ru/api/remap/1.2/entity/service/metadata",
                        "type" => "service",
                        "mediaType" => "application/json"
                    ]
                ]


            ];

        }

        $services[] = [

            "quantity" => 1.0,
            "price" => $shipping_data['price'],
            "discount" => 0.0,
            "vat" => $shipping_data['vat'],
            "vatEnabled" => true,
            "assortment" => [
                "meta" => [
                    "href" => $shipping_data['id'],
                    "metadataHref" => "https://api.moysklad.ru/api/remap/1.2/entity/service/metadata",
                    "type" => "service",
                    "mediaType" => "application/json"
                ]
            ]


        ];


        return $services;

    }



}