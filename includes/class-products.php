<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Pet_Shop_Products {

    private $db;

    public function __construct( $database ) {
        $this->db = $database;
    }

    public function add_product( $name, $buying_price ) {
        return $this->db->insert_product( $name, $buying_price );
    }

    public function edit_product( $id, $name, $buying_price ) {
        return $this->db->update_product( $id, $name, $buying_price );
    }

    public function delete_product( $id ) {
        return $this->db->delete_product( $id );
    }

    public function get_product( $id ) {
        return $this->db->get_product( $id );
    }

    public function get_all_products() {
        return $this->db->get_products();
    }
}
?>