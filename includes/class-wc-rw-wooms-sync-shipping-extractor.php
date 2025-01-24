<?php

/**
 * Class Wc_Rw_Wooms_Sync_Shipping_Extractor
 *
 * This class is responsible for extracting shipping details from an order,
 * specifically shipping descriptions provided by the Flexible Shipping Plugin by Octolise.
 * If the shipping method does not match the Flexible Shipping Plugin's structure, the method returns null.
 */
class Wc_Rw_Wooms_Sync_Shipping_Extractor
{

    /**
     * @var WC_Order $order The WooCommerce order object.
     */
    private WC_Order $order;

    /**
     * Constructor for the Wc_Rw_Wooms_Sync_Shipping_Extractor class.
     *
     * @param WC_Order $order WooCommerce order object.
     */
    public function __construct(WC_Order $order){
        $this->order = $order;
    }

    /**
     * Retrieves the shipping description set by the Flexible Shipping Plugin.
     *
     * This method extracts the shipping description, which is used as the shipping method code
     * by the Flexible Shipping Plugin. If the shipping method is not managed by the Flexible Shipping Plugin,
     * or if any required data is missing, the method returns null.
     *
     * @return string|null Returns the shipping description if available; otherwise, returns null.
     */
    public function get_shipping_from_third_part_plugin()
    {

        $shipping = $this->order->get_shipping_methods();
        $shipping_object = reset($shipping);
        if(!$shipping_object instanceof WC_Order_Item_Shipping) {
            return null;
        }

        $meta_data_value = $this->get_private_property('meta_data', $shipping_object);

        if(!isset($meta_data_value[1])) {
            return null;
        }

        $current_data_value = $this->get_private_property('current_data', $meta_data_value[1]);

        if(!$current_data_value) {
            return null;
        }

        return $current_data_value['value']['method_description'] ?? null;


    }

    /**
     * Retrieves the value of a private or protected property from an object.
     *
     * This method uses reflection to access private or protected properties of an object.
     * If the property or object does not exist, or if an error occurs during reflection, the method returns null.
     *
     * @param string $property_name The name of the property to retrieve.
     * @param object $object The object from which the property value should be retrieved.
     *
     * @return mixed|null The value of the property if accessible; otherwise, null.
     */
    private function get_private_property($property_name, $object)
    {
        if (!is_object($object)) {
            return null;
        }

        if (!property_exists($object, $property_name)) {
            return null;
        }

        try {
            $reflection = new ReflectionClass($object);
            $property = $reflection->getProperty($property_name);
            $property->setAccessible(true);

            return $property->getValue($object);

        } catch (ReflectionException $e) {
            //Wc_Rw_Wooms_Sync_Logger::make_log($this->order->get_id(), '-', $e->getMessage(), 'shipping extractor', 'internal_error');
            return null;
        }
    }




}