<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Pet_Shop_Manager_Sales {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function log_sale($selling_price, $items, $is_delivery = 0, $is_bkash = 0, $sale_date = null) {
        return $this->db->insert_sale($selling_price, $items, $is_delivery, $is_bkash, $sale_date);
    }

    public function get_sales($date_from = null, $date_to = null) {
        return $this->db->get_sales($date_from, $date_to);
    }

    public function calculate_profit($sale) {
        $selling_price = floatval($sale->selling_price);
        $total_cost = $this->calculate_items_cost($sale->items);
        return $selling_price - $total_cost;
    }

    private function calculate_items_cost($items) {
        $total_cost = 0;
        foreach ($items as $item) {
            $buying_price = floatval($item->buying_price);
            $quantity = intval($item->quantity);
            $total_cost += $buying_price * $quantity;
        }
        return $total_cost;
    }

    public function delete_sale($id) {
        return $this->db->delete_sale($id);
    }

    public function get_sale($id) {
        return $this->db->get_sale($id);
    }
}