<?php

class Wc_Rw_Wooms_Sync_Api_Request{

    private static $instance;
    private string $credentials;

    public function __construct(){

        $api_config = require plugin_dir_path( __DIR__ ) . 'config/config_api.php';
        $user_name = $api_config['name'];
        $password = $api_config['password'];
        $this->credentials = base64_encode("$user_name:$password");


    }


    public static function get_instance()
    {

        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    public function get_products_id(array $external_codes){

        $product_ms_ids = [];

        foreach ($external_codes as $product_id => $product_data) {

            $base_url = $product_data['bundle'] ? API_REQUEST_BUNDLE_URI : API_REQUEST_PRODUCT_URI;

            $url = $base_url . $product_data['external_code'];

            $args = array(
                'headers' => array(
                    'Authorization' => 'Basic ' . $this->credentials,
                    'Content-Type'  => 'application/json'
                )
            );

            $response = wp_remote_get($url, $args);

            $body = wp_remote_retrieve_body($response);

            $product = json_decode($body, true);

            $product_ms_ids[$product_id]['type'] = $product_data['bundle'] ? 'bundle' : 'product';
            $product_ms_ids[$product_id]['href'] = $product['rows'][0]['meta']['href'];
            $product_ms_ids[$product_id]['metadataHref'] = $product['rows'][0]['meta']['metadataHref'];

            //$product_ms_ids[$product_id] = $product;


            /*if (is_wp_error($response)) {
                echo 'Error occurred: ' . $response->get_error_message() . "\n";
            } else {
                $body = wp_remote_retrieve_body($response);
                $productData = json_decode($body, true);

                // Проверяем на ошибки декодирования JSON
                if (json_last_error() === JSON_ERROR_NONE) {
                    print_r($productData);  // Обработка полученных данных продукта
                } else {
                    echo 'JSON decode error: ' . json_last_error_msg() . "\n";
                }
            }*/

        }

        return $product_ms_ids;

    }

    public function create_order($order_data, $product_ids){

        $url = 'https://api.moysklad.ru/api/remap/1.2/entity/customerorder';

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
                "value" => $order_data['order_rate']
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

        //return json_encode($order_data);
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

// Выполнение POST-запроса
        $response = wp_remote_post($url, $args);

        return $response;
// Проверка на наличие ошибок
        /*if (is_wp_error($response)) {
            echo 'Error occurred: ' . $response->get_error_message() . "\n";
        } else {
            $body = wp_remote_retrieve_body($response);
            $response_data = json_decode($body, true);

            // Проверяем на ошибки декодирования JSON
            if (json_last_error() === JSON_ERROR_NONE) {
                print_r($response_data);  // Обработка полученных данных ответа
            } else {
                echo 'JSON decode error: ' . json_last_error_msg() . "\n";
            }
        }*/


    }

    private function create_positions_array($products, $product_ids) {

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

    private function create_services_array($fees_data, $shipping_data) {

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