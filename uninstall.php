<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Clean up the database when the plugin is uninstalled
global $wpdb;

// Remove product table
$table_name = $wpdb->prefix . 'pet_shop_products';
$wpdb->query("DROP TABLE IF EXISTS $table_name");

// Remove sales table
$table_name = $wpdb->prefix . 'pet_shop_sales';
$wpdb->query("DROP TABLE IF EXISTS $table_name");

// Remove sale items table
$table_name = $wpdb->prefix . 'pet_shop_sale_items';
$wpdb->query("DROP TABLE IF EXISTS $table_name");

// Remove expenses table
$table_name = $wpdb->prefix . 'pet_shop_expenses';
$wpdb->query("DROP TABLE IF EXISTS $table_name");

// Optionally, remove any plugin options
delete_option('pet_shop_manager_options');