<?php
/*
Plugin Name: DigitalChalk Single Sign-on for WordPress
Plugin URI: https://digitalchalk.com/
Description: Provides single sign-on to DigitalChalk from WordPress
Version: 1.1.1
Author: Bob Robinson (brobinson@digitalchalk.com), Daniel Hensley (dhensley@digitalchalk.com)
Author URI: https://digitalchalk.com
License: GPLv2 or later
Text Domain: wpdcsso
Domain Path: /languages
Requires at least: 5.0
Tested up to: 6.6
Requires PHP: 8.0
Network: false
*/

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Allow custom functions file
if ( file_exists( WP_PLUGIN_DIR . '/wpdcsso-custom.php' ) ) {
	include_once( WP_PLUGIN_DIR . '/wpdcsso-custom.php' );
}
	
if ( ! defined( 'WPDCSSO_ABSPATH' ) ) {
	define( 'WPDCSSO_ABSPATH', dirname( __FILE__ ) );
}

if ( ! defined( 'WPDCSSO_VERSION_KEY' ) ) {
	define( 'WPDCSSO_VERSION_KEY', 'wpdcsso_version' );
}

if ( ! defined( 'WPDCSSO_VERSION_NUM' ) ) {
	define( 'WPDCSSO_VERSION_NUM', '1.1.1' );
}

// Initialize the plugin
if ( get_option( WPDCSSO_VERSION_KEY ) !== WPDCSSO_VERSION_NUM ) {
    update_option( WPDCSSO_VERSION_KEY, WPDCSSO_VERSION_NUM );
}
add_action( 'init', 'wpdcsso_activate_updater' );

require_once( WPDCSSO_ABSPATH . '/includes/class-wpdcsso.php' );

/**
 * Add settings link to plugin actions
 *
 * @param array $links Plugin action links
 * @return array Modified links
 */
function wpdcsso_settings_link( $links ) {
	$settings_link = '<a href="' . esc_url( admin_url( 'options-general.php?page=wpdcsso_options' ) ) . '">' . esc_html__( 'Settings', 'wpdcsso' ) . '</a>';
	array_unshift( $links, $settings_link );
	return $links;	
}

/**
 * Activate the updater
 */
function wpdcsso_activate_updater() {
	require_once( WPDCSSO_ABSPATH . '/includes/class-wpdcsso-updater.php' );
	new WPDCSSO_Updater( WPDCSSO_VERSION_NUM, 'https://raw.githubusercontent.com/digitalchalk/wordpress-digitalchalk-sso-plugin/main/update', plugin_basename( __FILE__ ) );
}

$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_$plugin", 'wpdcsso_settings_link' );

$GLOBALS['WPDCSSO'] = new WPDCSSO();
register_activation_hook( __FILE__, array( 'WPDCSSO', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WPDCSSO', 'deactivate' ) );
