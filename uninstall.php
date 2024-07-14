<?php

// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

wc_rw_drop_plugin_added_meta();

function wc_rw_drop_plugin_added_meta(){

    // Delete all order meta fields
    delete_post_meta_by_key('moy_sklad_sync_date');
    delete_post_meta_by_key('moy_sklad_sync_status');

    // Delete all product and variations meta fields
    delete_post_meta_by_key('_moy_sklad_ext_code');
    delete_post_meta_by_key('_is_bundle');

}