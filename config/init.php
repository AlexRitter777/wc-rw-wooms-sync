<?php

defined('ABSPATH') || exit;


const API_URI = "https://api.moysklad.ru/api/remap/1.2/";

const API_REQUEST_PRODUCT_URI = API_URI . 'entity/product?filter=externalCode=';

const API_REQUEST_BUNDLE_URI = API_URI . 'entity/bundle?filter=externalCode=';