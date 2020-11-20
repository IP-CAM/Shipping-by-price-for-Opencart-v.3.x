<?php

class ModelExtensionShippingPrice extends Model {

    function getQuote($address) {
        $this->load->language('extension/shipping/price');

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int) $this->config->get('shipping_price_geo_zone_id') . "' AND country_id = '" . (int) $address['country_id'] . "' AND (zone_id = '" . (int) $address['zone_id'] . "' OR zone_id = '0')");

        if(!$this->config->get('shipping_price_geo_zone_id')) {
            $status = true;
        } elseif($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }

        if($this->cart->getSubTotal() < $this->config->get('shipping_price_total')) {
            $status = false;
        }

        $tarif = $this->config->get('shipping_price_tarif');

        $arr = array_map(function ($path) {
            return explode(':', $path);
        }, explode(';', $tarif));

        $cost  = 0;
        $total = $this->cart->getTotal();

        foreach ($arr as $value) {
            list($summ, $shipping_price) = $value;
            if($total >= $summ) {
                $cost = $shipping_price;
            }
        }


        if($this->config->get('shipping_price_title')) {
            $title = $this->config->get('shipping_price_title');
        } else {
            $title = $this->language->get('text_title');
        }

        if($this->config->get('shipping_price_descr')) {
            $description = $this->config->get('shipping_price_descr');
        } else {
            $description = $this->language->get('text_description');
        }

        $method_data = array();

        if($status) {
            $quote_data = array();

            $quote_data['price'] = array(
                'code'         => 'price.price',
                'title'        => $description,
                'cost'         => $cost,
                'tax_class_id' => 0,
                'text'         => $this->currency->format($cost, $this->session->data['currency'])
            );

            $method_data = array(
                'code'       => 'price',
                'title'      => $title,
                'quote'      => $quote_data,
                'sort_order' => $this->config->get('shipping_price_sort_order'),
                'error'      => false
            );
        }

        return $method_data;
    }

}
