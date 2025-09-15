<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Pet_Shop_Manager_Database {
    private $table_prefix;

    public function __construct() {
        global $wpdb;
        $this->table_prefix = $wpdb->prefix;
        $this->create_tables();
    }

    private function create_tables() {
        global $wpdb;

        $products_table = $this->table_prefix . 'pet_shop_products';
        $sales_table = $this->table_prefix . 'pet_shop_sales';
        $sale_items_table = $this->table_prefix . 'pet_shop_sale_items';
        $expenses_table = $this->table_prefix . 'pet_shop_expenses';

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        // Create products table
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $products_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            buying_price decimal(10,2) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta($sql);

        // Create sales table
        $sql = "CREATE TABLE $sales_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            sale_date date NOT NULL,
            selling_price decimal(10,2) NOT NULL,
            is_delivery tinyint(1) DEFAULT 0,
            is_bkash tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta($sql);

        // Create sale items table
        $sql = "CREATE TABLE $sale_items_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            sale_id mediumint(9) NOT NULL,
            product_id mediumint(9) NOT NULL,
            quantity int(11) NOT NULL,
            PRIMARY KEY  (id),
            KEY sale_id (sale_id),
            KEY product_id (product_id)
        ) $charset_collate;";
        dbDelta($sql);

        // Create expenses table
        $sql = "CREATE TABLE $expenses_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            expense_date date NOT NULL,
            description varchar(255) NOT NULL,
            amount decimal(10,2) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta($sql);
    }

    public function insert_product($name, $buying_price) {
        global $wpdb;
        return $wpdb->insert(
            $this->table_prefix . 'pet_shop_products',
            array(
                'name' => $name,
                'buying_price' => $buying_price,
            ),
            array('%s', '%f')
        );
    }

    public function update_product($id, $name, $buying_price) {
        global $wpdb;
        return $wpdb->update(
            $this->table_prefix . 'pet_shop_products',
            array(
                'name' => $name,
                'buying_price' => $buying_price,
            ),
            array('id' => $id),
            array('%s', '%f'),
            array('%d')
        );
    }

    public function insert_sale($selling_price, $items, $is_delivery = 0, $is_bkash = 0, $sale_date = null) {
        global $wpdb;

        if (!$sale_date) {
            $sale_date = current_time('Y-m-d');
        }

        // Start transaction
        $wpdb->query('START TRANSACTION');

        try {
            // Insert sale record
            $result = $wpdb->insert(
                $this->table_prefix . 'pet_shop_sales',
                array(
                    'sale_date' => $sale_date,
                    'selling_price' => $selling_price,
                    'is_delivery' => $is_delivery,
                    'is_bkash' => $is_bkash,
                ),
                array('%s', '%f', '%d', '%d')
            );

            if (!$result) {
                throw new Exception('Failed to insert sale');
            }

            $sale_id = $wpdb->insert_id;

            // Insert sale items
            foreach ($items as $item) {
                $result = $wpdb->insert(
                    $this->table_prefix . 'pet_shop_sale_items',
                    array(
                        'sale_id' => $sale_id,
                        'product_id' => $item['productId'],
                        'quantity' => $item['quantity'],
                    ),
                    array('%d', '%d', '%d')
                );

                if (!$result) {
                    throw new Exception('Failed to insert sale item');
                }
            }

            $wpdb->query('COMMIT');
            return $sale_id;

        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            return false;
        }
    }

    public function insert_expense($description, $amount, $expense_date = null) {
        global $wpdb;

        
        if (!$expense_date) {
            $expense_date = current_time('Y-m-d');
        }

        return $wpdb->insert(
            $this->table_prefix . 'pet_shop_expenses',
            array(
                'expense_date' => $expense_date,
                'description' => $description,
                'amount' => $amount,
            ),
            array('%s', '%s', '%f')
        );
    }

    public function get_products() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$this->table_prefix}pet_shop_products ORDER BY name ASC");
    }

    public function get_sales($date_from = null, $date_to = null) {
        global $wpdb;

        $sales_table = $this->table_prefix . 'pet_shop_sales';
        $items_table = $this->table_prefix . 'pet_shop_sale_items';
        $products_table = $this->table_prefix . 'pet_shop_products';

        $where_clause = '';
        if ($date_from && $date_to) {
            $where_clause = $wpdb->prepare(" WHERE s.sale_date BETWEEN %s AND %s", $date_from, $date_to);
        }

        $sales = $wpdb->get_results("SELECT * FROM {$sales_table} s {$where_clause} ORDER BY s.created_at DESC");

        // Get items for each sale
        foreach ($sales as &$sale) {
            $items = $wpdb->get_results($wpdb->prepare("
                SELECT si.quantity, si.product_id as productId, p.name as product_name, p.buying_price
                FROM {$items_table} si
                JOIN {$products_table} p ON si.product_id = p.id
                WHERE si.sale_id = %d
            ", $sale->id));

            $sale->items = $items;
        }

        return $sales;
    }

    public function get_expenses($date_from = null, $date_to = null) {
        global $wpdb;

        $where_clause = '';
        if ($date_from && $date_to) {
            $where_clause = $wpdb->prepare(" WHERE expense_date BETWEEN %s AND %s", $date_from, $date_to);
        }

        return $wpdb->get_results("SELECT * FROM {$this->table_prefix}pet_shop_expenses {$where_clause} ORDER BY expense_date DESC, created_at DESC");
    }

    public function delete_product($id) {
        global $wpdb;
        return $wpdb->delete($this->table_prefix . 'pet_shop_products', array('id' => $id), array('%d'));
    }

    public function delete_sale($id) {
        global $wpdb;

        // Start transaction
        $wpdb->query('START TRANSACTION');

        try {
            // Delete sale items first
            $wpdb->delete($this->table_prefix . 'pet_shop_sale_items', array('sale_id' => $id), array('%d'));

            // Delete sale
            $result = $wpdb->delete($this->table_prefix . 'pet_shop_sales', array('id' => $id), array('%d'));

            $wpdb->query('COMMIT');
            return $result;

        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            return false;
        }
    }

    public function delete_expense($id) {
        global $wpdb;
        return $wpdb->delete($this->table_prefix . 'pet_shop_expenses', array('id' => $id), array('%d'));
    }

    public function get_product($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_prefix}pet_shop_products WHERE id = %d", $id));
    }

    public function get_sale($id) {
        global $wpdb;
        $sale = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_prefix}pet_shop_sales WHERE id = %d", $id));

        if ($sale) {
            $items = $wpdb->get_results($wpdb->prepare("
                SELECT si.quantity, si.product_id as productId, p.name as product_name, p.buying_price
                FROM {$this->table_prefix}pet_shop_sale_items si
                JOIN {$this->table_prefix}pet_shop_products p ON si.product_id = p.id
                WHERE si.sale_id = %d
            ", $sale->id));

            $sale->items = $items;
        }

        return $sale;
    }

    public function update_expense($id, $description, $amount, $expense_date) {
        global $wpdb;
        return $wpdb->update(
            $this->table_prefix . 'pet_shop_expenses',
            array(
                'description' => $description,
                'amount' => $amount,
                'expense_date' => $expense_date,
            ),
            array('id' => $id),
            array('%s', '%f', '%s'),
            array('%d')
        );
    }
}
?>