<?php
/**
 * WPDCSSO Updater Class
 *
 * @since 1.1.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPDCSSO_Updater' ) ) {
	
	class WPDCSSO_Updater {
		
		/**
		 * Current version
		 *
		 * @var string
		 */
		public $current_version;
		
		/**
		 * Update base URL
		 *
		 * @var string
		 */
		public $update_base;
		
		/**
		 * Plugin slug
		 *
		 * @var string
		 */
		public $plugin_slug;
		
		/**
		 * Slug
		 *
		 * @var string
		 */
		public $slug;
		
		/**
		 * Constructor
		 *
		 * @param string $current_version Current plugin version
		 * @param string $update_base Base URL for updates
		 * @param string $plugin_slug Plugin slug
		 */
		public function __construct( $current_version, $update_base, $plugin_slug ) {
			$this->current_version = $current_version;
			$this->update_base = $update_base;
			$this->plugin_slug = $plugin_slug;
			
			$parts = explode( '/', $plugin_slug );
			if ( isset( $parts[1] ) ) {
				$this->slug = str_replace( '.php', '', $parts[1] );
			}
			
			// Define the alternative API for updating checking
			add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );
			
			// Define the alternative response for information checking
			add_filter( 'plugins_api', array( $this, 'check_info' ), 10, 3 );
		}
		/**
		 * Check for updates
		 *
		 * @param object $transient Update transient
		 * @return object
		 */
		public function check_update( $transient ) {
			if ( empty( $transient->checked ) ) {
				return $transient;
			}
			
			$remote_info = $this->get_remote_info();
			if ( ! empty( $remote_info ) && isset( $remote_info->new_version ) ) {
				if ( version_compare( $this->current_version, $remote_info->new_version, '<' ) ) {
					$obj = new stdClass();
					$obj->slug = $this->slug;
					$obj->new_version = $remote_info->new_version;
					$obj->url = $remote_info->info_page ?? '';
					$obj->package = $remote_info->download_link ?? '';
					$transient->response[ $this->plugin_slug ] = $obj;
				}
			}
			
			return $transient;
		}
		
		/**
		 * Check plugin info
		 *
		 * @param false|object|array $result The result object or array
		 * @param string $action The type of information being requested
		 * @param object $arg Plugin API arguments
		 * @return false|object
		 */
		public function check_info( $result, $action, $arg ) {
			if ( isset( $arg->slug ) && $arg->slug === $this->slug ) {
				$remote_info = $this->get_remote_info();
				return $remote_info;
			}
			return false;
		}
		
		/**
		 * Get remote version info
		 *
		 * @return object|null
		 */
		public function get_remote_info() {
			$request = wp_remote_get( 
				$this->update_base . '/latestversion',
				array(
					'timeout' => 30,
				)
			);
			
			if ( ! is_wp_error( $request ) && wp_remote_retrieve_response_code( $request ) === 200 ) {
				$body = wp_remote_retrieve_body( $request );
				$result = json_decode( $body );
				
				// Convert sections to PHP array
				if ( isset( $result->sections ) && is_array( $result->sections ) ) {
					$new_sections = array();
					foreach ( $result->sections as $section ) {
						if ( is_object( $section ) ) {
							foreach ( $section as $key => $value ) {
								$new_sections[ $key ] = $value;
							}
						}
					}
					$result->sections = $new_sections;
				}
				
				return $result;
			}
			return null;
		}
	}  // end class
}  // end if class_exists

?>

?>