<?php

defined('ABSPATH') || exit;

return [
    'order_postfix' => 'tst', //move to prefixes config

    'organization' => API_URI . 'entity/organization/2a67194f-e0af-11e7-7a31-d0fd00062f0f', // LION RITTER s.r.o.

    'counterparty' => API_URI .'entity/counterparty/010ac4e9-2a2e-11ef-0a80-0f3e000c1694', // Universal customer

    'state' => [
        'new' => API_URI . 'entity/customerorder/metadata/states/2a758e5c-e0af-11e7-7a31-d0fd00062f2c', // Новый
        'cod' => API_URI . 'entity/customerorder/metadata/states/1e8ce383-875d-11ec-0a80-0e2400239f0c' // Оплата при получении
    ],

    'currency' => [
        'EUR' => API_URI . 'entity/currency/1198e251-57b4-11ea-0a80-06920019ecd8',
        'PLN' => API_URI . 'entity/currency/161b19d8-e308-11eb-0a80-0180002d5baf',
        'BGN' => API_URI . 'entity/currency/7fa00f16-7482-11ed-0a80-066a00398f9b',
        'RON' => API_URI . 'entity/currency/c44ccb8e-6d73-11ec-0a80-05b700db4c0a',
    ],

    'store' => [
        'main' => API_URI . 'entity/store/2a682476-e0af-11e7-7a31-d0fd00062f11',
        'export' => API_URI . 'entity/store/7378e01c-77cc-11ee-0a80-1039000b65ec'
    ],

    'project' => [
        'diamag.bg' => API_URI . 'entity/project/24d61cf0-fc8c-11ee-0a80-10b30007bbd9',
        'diabetes1.si' => API_URI . 'entity/project/53b0fd34-fc8c-11ee-0a80-09030007c562',
        'senzorglicemie.ro' => API_URI . 'entity/project/92848692-fc8c-11ee-0a80-02b700088886',
        'sensoriailibre.lt' => API_URI . 'entity/project/a0b3ff96-fc8c-11ee-0a80-16fe00089c3d',
        'diabetyk1.pl' => API_URI . 'entity/project/e5738299-fc8b-11ee-0a80-16fe00086e79',
        'sensor.loc' => API_URI . 'entity/project/ec83d6ef-323b-11ef-0a80-044b00487030',
        'diabet1.ro' => API_URI . 'entity/project/08fcc980-3ca0-11ef-0a80-0bf8002d3b1e',
        'diabetiki1.kz' => API_URI . 'entity/project/0fd6f271-fc8c-11ee-0a80-089b0007acd6',
        'diabetes1.ae' => API_URI . 'entity/project/9303fff5-fc8b-11ee-0a80-10b300079b6d',
        'diabetes1.ge' => API_URI . 'entity/project/31a23603-fc8c-11ee-0a80-10b30007bf0a',
        'med24.am' => API_URI . 'entity/project/4384450e-fc8c-11ee-0a80-17080007bb6d',
        'diabetes5.com' => API_URI . 'entity/project/cf9e6331-fc8b-11ee-0a80-10b30007a9f9',
        'diabetestienda.es' => API_URI . 'entity/project/fcf18f34-fc8b-11ee-0a80-011e00079b66',
        'diaapteka.ru' => API_URI . 'entity/project/318b13b1-6caf-11e9-912f-f3d4000fe93e'


    ],

    'attributes' => [
        'name' =>  API_URI . 'entity/customerorder/metadata/attributes/a0201f33-6b2a-11e9-912f-f3d4000427ef',//ФИО
        'city' => API_URI . 'entity/customerorder/metadata/attributes/afa95276-6b2a-11e9-9107-504800045108', //город
        'shipping_address' => API_URI . 'entity/customerorder/metadata/attributes/56d4e704-6b2f-11e9-9ff4-31500004b9e9',
        'account_manager' => API_URI . 'entity/customerorder/metadata/attributes/2d3217ef-6cac-11e9-9109-f8fc000f79f2',//менеджер
        'payment_method' => API_URI . 'entity/customerorder/metadata/attributes/26073552-77cd-11e9-9ff4-34e800063912',
        'country' =>  API_URI . 'entity/customerorder/metadata/attributes/3c05b7d3-7893-11e9-9109-f8fc0005a9fd',
    ],

    'custom_payment_methods' => [
        'gpwebpaybinder' => API_URI . 'entity/customentity/0822cef1-77cd-11e9-9ff4-34e800061338/3b3287b1-77cd-11e9-9ff4-34e800063ae3',
        'gpwebpaybindergooglepay' => API_URI . 'entity/customentity/0822cef1-77cd-11e9-9ff4-34e800061338/3b3287b1-77cd-11e9-9ff4-34e800063ae3',
        'gpwebpaybinderapplepay' => API_URI . 'entity/customentity/0822cef1-77cd-11e9-9ff4-34e800061338/3b3287b1-77cd-11e9-9ff4-34e800063ae3',
        'payusecureform' => API_URI . 'entity/customentity/0822cef1-77cd-11e9-9ff4-34e800061338/3b3287b1-77cd-11e9-9ff4-34e800063ae3',
        'cod' => API_URI . 'entity/customentity/0822cef1-77cd-11e9-9ff4-34e800061338/a21becbf-86be-11ec-0a80-07b7001c3ccb',
        'bacs' => API_URI . 'entity/customentity/0822cef1-77cd-11e9-9ff4-34e800061338/3e38e19a-77cd-11e9-9109-f8fc0006403f',
    ],

    'custom_countries' => [
        'LT' => API_URI . 'entity/customentity/cf95b85c-788f-11e9-9109-f8fc00054056/b8cf42a9-4603-11ef-0a80-13600018c8f2',
        'PL' => API_URI . 'entity/customentity/cf95b85c-788f-11e9-9109-f8fc00054056/478196c1-875e-11ec-0a80-0e240023b78d',
        'RO' => API_URI . 'entity/customentity/cf95b85c-788f-11e9-9109-f8fc00054056/64145db7-26f6-11ef-0a80-158b00357b10',
        'KZ' => API_URI . 'entity/customentity/cf95b85c-788f-11e9-9109-f8fc00054056/4004d063-7890-11e9-9107-50480005445b',
        'SA' => API_URI . 'entity/customentity/cf95b85c-788f-11e9-9109-f8fc00054056/4f420981-a3ef-11ea-0a80-030d00209a65',
        'SI' => API_URI . 'entity/customentity/cf95b85c-788f-11e9-9109-f8fc00054056/7678c8da-3ca2-11ef-0a80-0bf8002d4d3c',
        'AZ' => API_URI . 'entity/customentity/cf95b85c-788f-11e9-9109-f8fc00054056/8bda9f32-789a-11e9-9ff4-315000064d21',
        'GE' => API_URI . 'entity/customentity/cf95b85c-788f-11e9-9109-f8fc00054056/39b124ac-7890-11e9-9109-f8fc00054d66',
        'RU' => API_URI . 'entity/customentity/cf95b85c-788f-11e9-9109-f8fc00054056/1e40c481-7890-11e9-9109-f8fc00054a6e',
        'BG' => API_URI . 'entity/customentity/cf95b85c-788f-11e9-9109-f8fc00054056/cd925e66-45fd-11ef-0a80-07b700187c48'
    ],

    'shipping' => API_URI . 'entity/service/23f82ebd-33a1-11ef-0a80-0208000b9028', //přeprava DPD
    'cod_payment' => API_URI . 'entity/service/30273e93-33a1-11ef-0a80-09c2000bb139',//dobírka

    'unknown_product_external' => 'ZcnR18DwhhgaPBBcbhiRu1', //neznámé zboží external code
    'unknown_product_id' => API_URI . 'entity/product/3482d084-33a6-11ef-0a80-0456000d7790', //neznámé zboží - id
    'unknown_custom_country' => API_URI . 'entity/customentity/cf95b85c-788f-11e9-9109-f8fc00054056/6fab9a30-7890-11e9-912f-f3d40005735e',
    'unknown_project' => API_URI . 'entity/project/5358be78-6caf-11e9-9ff4-315000104bed'


];