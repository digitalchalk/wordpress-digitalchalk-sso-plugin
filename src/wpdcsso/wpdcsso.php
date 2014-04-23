<?php
/*
Plugin Name: DigitalChalk Single Sign-on for Wordpress
Plugin URI: http://digitalchalk.com/
Description: Provides single sign-on to DigitalChalk from Wordpress
Version: 1.0.2
Author: Bob Robinson (brobinson@digitalchalk.com)
Author URI: http://digitalchalk.com
License: GPLv2 or later
Text Domain: wpdcsso
*/

// Allow custom functions file
if ( file_exists( WP_PLUGIN_DIR . '/wpdcsso-custom.php' ) )
	include_once( WP_PLUGIN_DIR . '/wpdcsso-custom.php' );
	
if ( !defined( 'WPDCSSO_ABSPATH' ) )
	define( 'WPDCSSO_ABSPATH', dirname( __FILE__ ) );

if (!defined('WPDCSSO_VERSION_KEY'))
	define('WPDCSSO_VERSION_KEY', 'wpdcsso_version');

if (!defined('WPDCSSO_VERSION_NUM'))
	define('WPDCSSO_VERSION_NUM', '1.0.2');

add_option(WPDCSSO_VERSION_KEY, WPDCSSO_VERSION_NUM);
add_action('init', 'wpdcsso_activate_updater');

require_once( WPDCSSO_ABSPATH . '/includes/class-wpdcsso.php' );

function wpdcsso_settings_link($links) {
	$settings_link = '<a href="options-general.php?page=wpdcsso_options">Settings</a>';
	array_unshift($links, $settings_link);
	return $links;	
}

function wpdcsso_activate_updater() {
	require_once(WPDCSSO_ABSPATH . '/includes/class-wpdcsso-updater.php');
	new wpdcsso_updater(WPDCSSO_VERSION_NUM, 'https://raw.github.com/digitalchalk/wordpress-digitalchalk-sso-plugin/master/update', plugin_basename(__FILE__));
}

$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'wpdcsso_settings_link' );

$GLOBALS['WPDCSSO'] =& new WPDCSSO();
register_activation_hook( __FILE__, array('WPDCSSO', 'activate') );
register_deactivation_hook( __FILE__, array('WPDCSSO', 'deactivate') );	
?>