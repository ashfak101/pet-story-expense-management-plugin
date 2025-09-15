<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Pet_Shop_Manager_Admin {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    public function add_admin_menu() {
        add_menu_page(
            'Pet Shop Manager',
            'Pet Shop',
            'manage_options',
            'pet-shop-manager',
            array( $this, 'admin_dashboard' ),
            'dashicons-pets',
            6
        );

        add_submenu_page(
            'pet-shop-manager',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'pet-shop-manager',
            array( $this, 'admin_dashboard' )
        );

        add_submenu_page(
            'pet-shop-manager',
            'Products',
            'Products',
            'manage_options',
            'pet-shop-manager-products',
            array( $this, 'admin_products' )
        );

        add_submenu_page(
            'pet-shop-manager',
            'Sales',
            'Sales',
            'manage_options',
            'pet-shop-manager-sales',
            array( $this, 'admin_sales' )
        );

        add_submenu_page(
            'pet-shop-manager',
            'Expenses',
            'Expenses',
            'manage_options',
            'pet-shop-manager-expenses',
            array( $this, 'admin_expenses' )
        );

        add_submenu_page(
            'pet-shop-manager',
            'Data Management',
            'Data Management',
            'manage_options',
            'pet-shop-manager-data',
            array( $this, 'admin_data_management' )
        );
    }

    public function register_settings() {
        // Register any settings here
    }

    public function admin_dashboard() {
        include_once plugin_dir_path( __FILE__ ) . '../admin/partials/dashboard.php';
    }

    public function admin_products() {
        include_once plugin_dir_path( __FILE__ ) . '../admin/partials/products.php';
    }

    public function admin_sales() {
        include_once plugin_dir_path( __FILE__ ) . '../admin/partials/sales.php';
    }

    public function admin_expenses() {
        include_once plugin_dir_path( __FILE__ ) . '../admin/partials/expenses.php';
    }

    public function admin_data_management() {
        include_once plugin_dir_path( __FILE__ ) . '../admin/partials/data-management.php';
    }
}
?>