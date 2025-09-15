<?php
/**
 * Plugin Name: Pet Shop Manager
 * Description: A plugin to manage pet shop operations including product management, sales tracking, and expense tracking.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * License: GPL2
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'PET_SHOP_MANAGER_VERSION', '1.0.0' );
define( 'PET_SHOP_MANAGER_DIR', plugin_dir_path( __FILE__ ) );
define( 'PET_SHOP_MANAGER_URL', plugin_dir_url( __FILE__ ) );

// Include necessary files
require_once PET_SHOP_MANAGER_DIR . 'includes/class-database.php';
require_once PET_SHOP_MANAGER_DIR . 'includes/class-products.php';
require_once PET_SHOP_MANAGER_DIR . 'includes/class-sales.php';
require_once PET_SHOP_MANAGER_DIR . 'includes/class-expenses.php';
require_once PET_SHOP_MANAGER_DIR . 'includes/class-admin.php';
require_once PET_SHOP_MANAGER_DIR . 'includes/class-pet-shop-manager.php';

// Initialize the plugin
function run_pet_shop_manager() {
    new Pet_Shop_Manager();
}
add_action( 'plugins_loaded', 'run_pet_shop_manager' );
?>