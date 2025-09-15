<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Pet_Shop_Expenses {

    private $db;

    public function __construct( $database ) {
        $this->db = $database;
    }

    public function add_expense( $description, $amount, $expense_date = null ) {
        return $this->db->insert_expense( $description, $amount, $expense_date );
    }

    public function edit_expense( $id, $description, $amount, $expense_date ) {
        return $this->db->update_expense( $id, $description, $amount, $expense_date );
    }

    public function delete_expense( $id ) {
        return $this->db->delete_expense( $id );
    }

    public function get_expenses( $date_from = null, $date_to = null ) {
        return $this->db->get_expenses( $date_from, $date_to );
    }

    public function get_expense( $id ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}pet_shop_expenses WHERE id = %d", $id ) );
    }

    public function get_total_expenses( $date_from = null, $date_to = null ) {
        global $wpdb;
        $where_clause = '';
        if ( $date_from && $date_to ) {
            $where_clause = $wpdb->prepare( " WHERE expense_date BETWEEN %s AND %s", $date_from, $date_to );
        }
        return $wpdb->get_var( "SELECT SUM(amount) FROM {$wpdb->prefix}pet_shop_expenses {$where_clause}" );
    }
}
?>