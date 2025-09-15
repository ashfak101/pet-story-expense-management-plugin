<?php
/**
 * Pet Shop Manager Class
 *
 * This class is responsible for initializing the Pet Shop Manager plugin,
 * loading necessary components, and setting up hooks and filters.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Pet_Shop_Manager {

    private $database;

    /**
     * Constructor to initialize the plugin.
     */
    public function __construct() {
        // Initialize database
        $this->database = new Pet_Shop_Manager_Database();

        // Register activation and deactivation hooks
        register_activation_hook( PET_SHOP_MANAGER_DIR . 'pet-shop-manager.php', array( $this, 'activate' ) );
        register_deactivation_hook( PET_SHOP_MANAGER_DIR . 'pet-shop-manager.php', array( $this, 'deactivate' ) );

        // Initialize the admin interface
        $this->init_admin();

        // Load scripts and styles
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

        // Add AJAX handlers
        add_action( 'wp_ajax_pet_shop_action', array( $this, 'handle_ajax_request' ) );
    }

    /**
     * Activate the plugin.
     */
    public function activate() {
        // Database tables are created in the constructor
        flush_rewrite_rules();
    }

    /**
     * Deactivate the plugin.
     */
    public function deactivate() {
        flush_rewrite_rules();
    }

    /**
     * Initialize the admin interface.
     */
    private function init_admin() {
        new Pet_Shop_Manager_Admin();
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts( $hook ) {
        if ( strpos( $hook, 'pet-shop' ) === false ) {
            return;
        }

        wp_enqueue_style(
            'pet-shop-admin-style',
            PET_SHOP_MANAGER_URL . 'assets/css/admin-style.css',
            array(),
            PET_SHOP_MANAGER_VERSION
        );

        wp_enqueue_script(
            'pet-shop-admin-script',
            PET_SHOP_MANAGER_URL . 'assets/js/admin-script.js',
            array( 'jquery' ),
            PET_SHOP_MANAGER_VERSION,
            true
        );

        wp_localize_script( 'pet-shop-admin-script', 'pet_shop_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'pet_shop_nonce' )
        ));
    }

    /**
     * Handle AJAX requests
     */
    public function handle_ajax_request() {
        check_ajax_referer( 'pet_shop_nonce', 'nonce' );

        $action = sanitize_text_field( $_POST['pet_action'] );

        switch ( $action ) {
            case 'add_product':
                $this->ajax_add_product();
                break;
            case 'edit_product':
                $this->ajax_edit_product();
                break;
            case 'delete_product':
                $this->ajax_delete_product();
                break;
            case 'get_products':
                $this->ajax_get_products();
                break;
            case 'add_sale':
                $this->ajax_add_sale();
                break;
            case 'edit_sale':
                $this->ajax_edit_sale();
                break;
            case 'delete_sale':
                $this->ajax_delete_sale();
                break;
            case 'get_sale':
                $this->ajax_get_sale();
                break;
            case 'get_sales':
                $this->ajax_get_sales();
                break;
            case 'add_expense':
                $this->ajax_add_expense();
                break;
            case 'edit_expense':
                $this->ajax_edit_expense();
                break;
            case 'delete_expense':
                $this->ajax_delete_expense();
                break;
            case 'get_expense':
                $this->ajax_get_expense();
                break;
            case 'get_expenses':
                $this->ajax_get_expenses();
                break;
            default:
                wp_die( 'Invalid action' );
        }
    }

    private function ajax_add_product() {
        $name = sanitize_text_field( $_POST['name'] );
        $buying_price = floatval( $_POST['buying_price'] );

        $result = $this->database->insert_product( $name, $buying_price );

        wp_send_json_success( array( 'message' => 'Product added successfully' ) );
    }

    private function ajax_get_products() {
        $products = $this->database->get_products();
        wp_send_json_success( $products );
    }

    private function ajax_delete_product() {
        $id = intval( $_POST['id'] );
        $this->database->delete_product( $id );
        wp_send_json_success( array( 'message' => 'Product deleted successfully' ) );
    }

    private function ajax_edit_product() {
        $id = intval( $_POST['id'] );
        $name = sanitize_text_field( $_POST['name'] );
        $buying_price = floatval( $_POST['buying_price'] );

        $this->database->update_product( $id, $name, $buying_price );
        wp_send_json_success( array( 'message' => 'Product updated successfully' ) );
    }

    private function ajax_add_sale() {
        $selling_price = floatval( $_POST['selling_price'] );
        $items = json_decode( stripslashes( $_POST['items'] ), true );
        $is_delivery = intval( $_POST['is_delivery'] );
        $is_bkash = intval( $_POST['is_bkash'] );

        $this->database->insert_sale( $selling_price, $items, $is_delivery, $is_bkash );
        wp_send_json_success( array( 'message' => 'Sale logged successfully' ) );
    }

    private function ajax_get_sales() {
        $sales = $this->database->get_sales();
        wp_send_json_success( $sales );
    }

    private function ajax_add_expense() {
        $description = sanitize_text_field( $_POST['description'] );
        $amount = floatval( $_POST['amount'] );
        $expense_date = sanitize_text_field( $_POST['expense_date'] );

        $this->database->insert_expense( $description, $amount , $expense_date );
        wp_send_json_success( array( 'message' => 'Expense added successfully' ) );
    }

    private function ajax_get_expenses() {
        $expenses = $this->database->get_expenses();
        wp_send_json_success( $expenses );
    }

    private function ajax_get_expense() {
        $id = intval( $_POST['id'] );
        $expense = $this->database->get_expense( $id );
        wp_send_json_success( $expense );
    }

    private function ajax_edit_expense() {
        $id = intval( $_POST['id'] );
        $description = sanitize_text_field( $_POST['description'] );
        $amount = floatval( $_POST['amount'] );
        $expense_date = sanitize_text_field( $_POST['expense_date'] );

        $this->database->update_expense( $id, $description, $amount, $expense_date );
        wp_send_json_success( array( 'message' => 'Expense updated successfully' ) );
    }

    private function ajax_delete_expense() {
        $id = intval( $_POST['id'] );
        $this->database->delete_expense( $id );
        wp_send_json_success( array( 'message' => 'Expense deleted successfully' ) );
    }

    private function ajax_edit_sale() {
        $id = intval( $_POST['sale_id'] );
        $selling_price = floatval( $_POST['selling_price'] );
        $items = json_decode( stripslashes( $_POST['items'] ), true );
        $is_delivery = intval( $_POST['is_delivery'] );
        $is_bkash = intval( $_POST['is_bkash'] );
        $sale_date = sanitize_text_field( $_POST['sale_date'] );

        // First delete the old sale
        $this->database->delete_sale( $id );

        // Then create a new sale with the same ID
        $this->database->insert_sale( $selling_price, $items, $is_delivery, $is_bkash, $sale_date );
        wp_send_json_success( array( 'message' => 'Sale updated successfully' ) );
    }

    private function ajax_delete_sale() {
        $id = intval( $_POST['id'] );
        $result = $this->database->delete_sale( $id );
        wp_send_json_success( array( 'message' => 'Sale deleted successfully' ) );
    }

    private function ajax_get_sale() {
        $id = intval( $_POST['id'] );
        $sale = $this->database->get_sale( $id );
        wp_send_json_success( $sale );
    }
}
?>