<?php
class Pet_Shop_Manager_Admin_Pages {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_pages'));
    }

    public function add_admin_pages() {
        add_menu_page(
            'Pet Shop Manager',
            'Pet Shop',
            'manage_options',
            'pet-shop-manager',
            array($this, 'dashboard_page'),
            'dashicons-pets',
            110
        );

        add_submenu_page(
            'pet-shop-manager',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'pet-shop-manager',
            array($this, 'dashboard_page')
        );

        add_submenu_page(
            'pet-shop-manager',
            'Products',
            'Products',
            'manage_options',
            'pet-shop-products',
            array($this, 'products_page')
        );

        add_submenu_page(
            'pet-shop-manager',
            'Sales',
            'Sales',
            'manage_options',
            'pet-shop-sales',
            array($this, 'sales_page')
        );

        add_submenu_page(
            'pet-shop-manager',
            'Expenses',
            'Expenses',
            'manage_options',
            'pet-shop-expenses',
            array($this, 'expenses_page')
        );

        add_submenu_page(
            'pet-shop-manager',
            'Data Management',
            'Data Management',
            'manage_options',
            'pet-shop-data-management',
            array($this, 'data_management_page')
        );
    }

    public function dashboard_page() {
        include_once plugin_dir_path(__FILE__) . '../partials/dashboard.php';
    }

    public function products_page() {
        include_once plugin_dir_path(__FILE__) . '../partials/products.php';
    }

    public function sales_page() {
        include_once plugin_dir_path(__FILE__) . '../partials/sales.php';
    }

    public function expenses_page() {
        include_once plugin_dir_path(__FILE__) . '../partials/expenses.php';
    }

    public function data_management_page() {
        include_once plugin_dir_path(__FILE__) . '../partials/data-management.php';
    }
}
?>