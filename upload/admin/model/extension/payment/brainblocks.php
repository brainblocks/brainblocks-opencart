<?php

class ModelExtensionPaymentBrainblocks extends Model
{
    public function install()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "brainblocks_tokens` (
            `brainblocks_tokens_id` int(11) NOT NULL AUTO_INCREMENT,
            `order_id` int(11) NOT NULL DEFAULT '0',
            `token` text NOT NULL DEFAULT '',
            `date_added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`brainblocks_tokens_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "brainblocks_response` (
            `brainblocks_response_id` int(11) NOT NULL AUTO_INCREMENT,
            `order_id` int(11) NOT NULL DEFAULT '0',
            `response` text NULL,
            `date_added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`brainblocks_response_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "brainblocks_no_response` (
            `brainblocks_no_response_id` int(11) NOT NULL AUTO_INCREMENT,
            `order_id` int(11) NOT NULL DEFAULT '0',
            `token` text NULL,
            `date_added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`brainblocks_no_response_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
    }

    public function uninstall()
    {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "brainblocks_tokens`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "brainblocks_response`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "brainblocks_no_response`");
    }

    public function getResponses($order_id)
    {
        return $this->db->query("
          SELECT * FROM `" . DB_PREFIX . "brainblocks_response` 
          WHERE `order_id` = '" . (int)$order_id . "'
          ORDER BY `date_added` DESC
        ")->rows;
    }

    public function getNoResponse($order_id)
    {
        return $this->db->query("
          SELECT * FROM `" . DB_PREFIX . "brainblocks_no_response` 
          WHERE `order_id` = '" . (int)$order_id . "'
        ")->row;
    }
}