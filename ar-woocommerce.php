<?php
/**
* Plugin Name: AR for WooCommerce
* Plugin URI: https://augmentedrealityplugins.com
* Description: AR for WooCommerce Augmented Reality plugin.
* Version: 6.2
* Author: Web and Print Design	
* Author URI: https://webandprint.design
* License:  GPL2
* Text Domain: ar-for-woocommerce
* Domain Path: /languages
* WC requires at least: 4.2
* WC tested up to: 9.1.2
**/

if ( ! defined( 'ABSPATH' ) ) exit; 


$ar_plugin_id='ar-for-woocommerce';
$ar_wc_active = true;
if ( is_admin() ) {
    if( ! function_exists( 'get_plugin_data' ) ) {
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }
    $ar_plugin_data = get_plugin_data( __FILE__ );
    $ar_version = $ar_plugin_data['Version'];
}

add_action( 'plugins_loaded', 'ar_woo_load_text_domain' );
function ar_woo_load_text_domain() {
    load_plugin_textdomain( 'ar-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}


add_action( 'before_woocommerce_init', function() {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
} );

// Functions Load
require_once(plugin_dir_path(__FILE__). 'ar-functions.php');
require_once(plugin_dir_path(__FILE__). 'ar-wc-functions.php');

// Widgets Load
require_once(plugin_dir_path(__FILE__). 'ar-widgets.php');

// Endpoint API Load
require_once(plugin_dir_path(__FILE__). 'ar-wc-api.php');

// Endpoint for Media upload
require_once(plugin_dir_path(__FILE__). 'ar-add-media.php');

// Block Gutenberg Load
require_once(plugin_dir_path(__FILE__). 'gutenberg-block/src/init.php');

// Plugin Updates 
$this_file = __FILE__;
$update_check = "https://augmentedrealityplugins.com/plugins/check-update-ar-for-woocommerce.txt";

require_once(plugin_dir_path(__FILE__) . 'ar-updates.php');

// Add the data to the custom columns for the AR Model Products
add_action( 'manage_product_posts_custom_column' , 'ar_advance_custom_armodels_column', 10, 2 );

// Add column to indicate product has an AR Model - Admin Page
function ar_woo_advance_custom_edit_wp_columns($columns) {
    unset( $columns['date'] );
    $columns['Shortcode'] = __('AR Shortcode', 'ar-for-woocommerce' );
    $ARimgSrc = esc_url( plugins_url( "assets/images/chair.png", __FILE__ ) );    
    $columns['thumbs'] = '<div class="ar_tooltip"><img src="'.$ARimgSrc.'" width="15"><span class="ar_tooltip_text">'.__('AR Model', 'ar-for-woocommerce' ).'</span></div>'; //name of the column
    $columns['date'] = __( 'Date', 'ar-for-woocommerce' );
    return $columns;
}
add_filter( 'manage_edit-product_columns', 'ar_woo_advance_custom_edit_wp_columns' );

// Add AR Display setting tab to Woocommerce > Settings section
add_action( 'woocommerce_settings_tabs', 'wc_settings_tabs_ar_display_tab' );

function wc_settings_tabs_ar_display_tab() {
    $current_tab = ( isset($_GET['tab']) && $_GET['tab'] === 'ar_display' ) ? 'nav-tab-active' : '';
    echo '<a href="admin.php?page=wc-settings&tab=ar_display" class="nav-tab '.$current_tab.'">'.__( "AR Display", "ar-for-woocommerce" ).'</a>';
}

// The settings tab content
add_action( 'woocommerce_settings_ar_display', 'ar_subscription_setting' );

// Add links to Settings page on Plugins page
add_filter( 'plugin_action_links_ar-for-woocommerce/ar-woocommerce.php', 'arwc_settings_link' );
function arwc_settings_link( $links ) {
	$url = esc_url( add_query_arg(
		'page',
		'wc-settings',
		get_admin_url() . 'admin.php'
	) );
	$settings_link = "<a href='$url&tab=ar_display'>" . __( 'Settings', 'ar-for-woocommerce' ) . '</a>';
	array_push($links,$settings_link);
	$url = esc_url( add_query_arg(
		'post_type',
		'armodels',
		'https://wordpress.org/plugins/ar-for-woocommerce/#developers'
	) );
	$settings_link = "<a href='$url'>" . __( 'Whats New', 'ar-for-woocommerce' ) . '</a>';
	array_push($links,$settings_link);
	return $links;
}

//Add documentation link to the plugin on the plugins page
add_filter('plugin_row_meta', 'ar_woo_plugin_documentation_link', 10, 2);

function ar_woo_plugin_documentation_link($links, $file) {
    if (plugin_basename(__FILE__) === $file) {
        $documentation_link = '<a href="https://augmentedrealityplugins.com/support/" target="_blank">Documentation</a>';
        $links[] = $documentation_link;
    }
    return $links;
}

?>