<?php

class ModelExtensionPaymentBrainblocks extends Model
{
    public function getMethod($address, $total)
    {
        $this->load->language('extension/payment/brainblocks');

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payment_brainblocks_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

        if ($this->config->get('payment_brainblocks_total') > 0 && $this->config->get('payment_brainblocks_total') > $total) {
            $status = false;
        } elseif (!$this->config->get('payment_brainblocks_geo_zone_id')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }

        $currencies = array(
            'aud', 'brl', 'cad', 'chf', 'clp', 'cny', 'czk', 'dkk', 'eur', 'gbp', 'hkd',
            'huf', 'idr', 'ils', 'inr', 'jpy', 'krw', 'mxn', 'myr', 'nok', 'nzd', 'php',
            'pkr', 'pln', 'rub', 'sek', 'sgd', 'thb', 'try', 'usd', 'twd', 'zar'
        );

        if (!in_array(strtolower($this->session->data['currency']), $currencies)) {
            $status = false;
        }

        $method_data = array();

        if ($status) {
            $method_data = array(
                'code'       => 'brainblocks',
                'title'      => $this->language->get('text_title'),
                'terms'      => '',
                'sort_order' => $this->config->get('payment_brainblocks_sort_order')
            );
        }

        return $method_data;
    }

    public function checkTokenReuse($token = '')
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "brainblocks_tokens WHERE token = '" . $this->db->escape($token) . "'");

        return $query->num_rows;
    }

    public function addToken($order_id, $token)
    {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "brainblocks_tokens` SET `token` = '" . $this->db->escape($token) . "', `order_id` = '" . (int)$order_id . "', `date_added` = NOW()");
    }

    public function addResponse($order_id, $response)
    {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "brainblocks_response` SET `response` = '" . $this->db->escape($response) . "', `order_id` = '" . (int)$order_id . "', `date_added` = NOW()");
    }

    public function addNoResponse($order_id, $token)
    {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "brainblocks_no_response` SET `token` = '" . $this->db->escape($token) . "', `order_id` = '" . (int)$order_id . "', `date_added` = NOW()");
    }

    public function removeNoResponses($order_id)
    {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "brainblocks_no_response` WHERE `order_id` = '" . (int)$order_id . "'");
    }
}
