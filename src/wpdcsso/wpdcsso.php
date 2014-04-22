<?php
/*
Plugin Name: DigitalChalk Single Sign-on for Wordpress
Plugin URI: http://digitalchalk.com/
Description: Provides single sign-on to DigitalChalk from Wordpress
Version: 1.0
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

require_once( WPDCSSO_ABSPATH . '/includes/class-wpdcsso.php' );
	
$GLOBALS['WPDCSSO'] =& new WPDCSSO();
register_activation_hook( __FILE__, array('WPDCSSO', 'activate') );
register_deactivation_hook( __FILE__, array('WPDCSSO', 'deactivate') );	
?>