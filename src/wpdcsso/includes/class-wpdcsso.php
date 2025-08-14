<?php
/**
 * Main WPDCSSO Class
 *
 * @since 1.1.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPDCSSO' ) ) {
	class WPDCSSO {
		
		/**
		 * Constructor
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'init_action' ) );
			if ( is_admin() ) {
				add_action( 'admin_menu', array( $this, 'init_admin_menu' ) );
				add_action( 'admin_init', array( $this, 'register_settings' ) );
			}
		}

		/**
		 * Register plugin settings
		 */
		public function register_settings() {
			register_setting( 'wpdcsso_options', 'wpdcsso_dc_url', array(
				'type' => 'string',
				'sanitize_callback' => 'esc_url_raw',
			) );
			register_setting( 'wpdcsso_options', 'wpdcsso_email_username', array(
				'type' => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default' => 'email',
			) );
			register_setting( 'wpdcsso_options', 'wpdcsso_api_url', array(
				'type' => 'string',
				'sanitize_callback' => 'esc_url_raw',
				'default' => 'https://api.digitalchalk.com/api/v5',
			) );
			register_setting( 'wpdcsso_options', 'wpdcsso_shared_secret', array(
				'type' => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			) );
			register_setting( 'wpdcsso_options', 'wpdcsso_shared_token', array(
				'type' => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			) );
			register_setting( 'wpdcsso_options', 'wpdcsso_single_sign_out', array(
				'type' => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default' => 'yes',
			) );
		}

		/**
		 * Initialize actions
		 */
		public function init_action() {
			global $current_user;
			
			// Sanitize input
			$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
			$error_code = isset( $_GET['errorcode'] ) ? sanitize_text_field( wp_unslash( $_GET['errorcode'] ) ) : '';
			
			$current_user = wp_get_current_user();
			
			if ( ! empty( $action ) && empty( $error_code ) ) {
				if ( $action === 'wpdcsso_login' ) {
					if ( get_option( 'wpdcsso_dc_url' ) ) {
						if ( is_user_logged_in() ) {
							$this->do_sso_post();
							exit;
						} else {
							wp_safe_redirect( wp_login_url( $this->make_wp_sso_login_url() ) );
							exit;
						}
					}
				} elseif ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'wpdcsso_logout' ) {
					if ( get_option( 'wpdcsso_single_sign_out' ) === 'yes' ) {
						if ( is_user_logged_in() ) {
							wp_logout();
							wp_safe_redirect( home_url() );
							exit;
						}
					} else {
						wp_safe_redirect( home_url() );
						exit;
					}
				}
			}
		}

		/**
		 * Create WordPress SSO login URL
		 *
		 * @return string
		 */
		public function make_wp_sso_login_url() {
			$url = wp_login_url();
			$separator = $this->contains( '?', $url ) ? '&' : '?';
			$url .= $separator . 'action=wpdcsso_login';
			return $url;
		}

		/**
		 * Perform SSO post to DigitalChalk
		 */
		public function do_sso_post() {
			global $current_user;
			$current_user = wp_get_current_user();
			$timestamp = time();
			$to_hash = $timestamp . '|' . get_option( 'wpdcsso_shared_secret' ) . '|' . $current_user->user_email;
			$hash = md5( $to_hash );

			// Escape all output for security
			$dc_url = esc_url( get_option( 'wpdcsso_dc_url' ) );
			$timestamp_escaped = esc_attr( $timestamp );
			$hash_escaped = esc_attr( $hash );
			$email_escaped = esc_attr( $current_user->user_email );

			// Render page to execute form
			echo '<!DOCTYPE html>
			<html>
			<head>
				<meta charset="UTF-8">
				<title>' . esc_html__( 'Redirecting to DigitalChalk...', 'wpdcsso' ) . '</title>
			</head>
			<script type="text/javascript">
				window.onload = function () {
					document.forms["ssoform"].submit();
				}
			</script>
			<body>
				<p>' . esc_html__( 'Redirecting to DigitalChalk...', 'wpdcsso' ) . '</p>
				<form name="ssoform" id="ssoform" action="' . $dc_url . '" method="POST">
					<input type="hidden" name="action" value="auth"/>
					<input type="hidden" name="timestamp" value="' . $timestamp_escaped . '"/>
					<input type="hidden" name="hash" value="' . $hash_escaped . '"/>
					<input type="hidden" name="email" value="' . $email_escaped . '"/>';
			
			if ( ! empty( $current_user->user_firstname ) ) {
				echo '<input type="hidden" name="firstname" value="' . esc_attr( $current_user->user_firstname ) . '"/>';
			}
			
			if ( ! empty( $current_user->user_lastname ) ) {
				echo '<input type="hidden" name="lastname" value="' . esc_attr( $current_user->user_lastname ) . '"/>';
			}
			
			if ( get_option( 'wpdcsso_email_username' ) === 'username' ) {
				echo '<input type="hidden" name="username" value="' . esc_attr( $current_user->user_login ) . '"/>';
			}

			echo '</form>
			</body>
			</html>';
		}

		/**
		 * Debug content (unused)
		 *
		 * @param string $content Content to append to
		 * @return string
		 */
		public function append_debug_content( $content ) {
			$content .= 'This is the append_debug_content';
			return $content;
		}

		/**
		 * Initialize admin menu
		 */
		public function init_admin_menu() {
			add_options_page( 
				esc_html__( 'DigitalChalk SSO Options', 'wpdcsso' ), 
				esc_html__( 'DigitalChalk SSO', 'wpdcsso' ), 
				'manage_options', 
				'wpdcsso_options', 
				array( $this, 'display_settings_page' ) 
			);
		}

		/**
		 * Display settings page
		 */
		public function display_settings_page() {
			?>
			<div class="wrap">
				<h1><?php esc_html_e( 'DigitalChalk SSO Options', 'wpdcsso' ); ?></h1>
				<form action="options.php" method="post">
				<?php 
				settings_fields( 'wpdcsso_options' );
				do_settings_sections( 'wpdcsso_options' );
				?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="wpdcsso_login_url"><?php esc_html_e( 'Login URL', 'wpdcsso' ); ?></label></th>
						<td>
							<p><?php echo esc_url( $this->make_login_url() ); ?></p>
							<p class="description"><?php esc_html_e( 'Copy this value into Login URL in your DigitalChalk account', 'wpdcsso' ); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="wpdcsso_logout_url"><?php esc_html_e( 'Logout URL', 'wpdcsso' ); ?></label></th>
						<td>
							<p><?php echo esc_url( $this->make_logout_url() ); ?></p>
							<p class="description"><?php esc_html_e( 'Copy this value into Logout URL in your DigitalChalk account', 'wpdcsso' ); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="wpdcsso_dc_url"><?php esc_html_e( 'DigitalChalk SSO URL', 'wpdcsso' ); ?></label></th>
						<td>
							<input name="wpdcsso_dc_url" type="url" id="wpdcsso_dc_url" value="<?php echo esc_attr( get_option( 'wpdcsso_dc_url' ) ); ?>" class="large-text" />
							<p class="description"><?php esc_html_e( 'The DC SSO URL provided to you by DigitalChalk.', 'wpdcsso' ); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="wpdcsso_api_url"><?php esc_html_e( 'DigitalChalk API URL', 'wpdcsso' ); ?></label></th>
						<td>
							<input name="wpdcsso_api_url" type="url" id="wpdcsso_api_url" value="<?php echo esc_attr( get_option( 'wpdcsso_api_url', 'https://api.digitalchalk.com/api/v5' ) ); ?>" class="large-text" />
							<p class="description">
								<?php esc_html_e( 'The API URL provided to you by DigitalChalk.', 'wpdcsso' ); ?>
								<button type="button" id="useDefault" data-host-url="https://api.digitalchalk.com/api/v5"><?php esc_html_e( 'Use Default', 'wpdcsso' ); ?></button>
							</p>
							<script>
							(function() {
								const hostnameInput = document.getElementById("wpdcsso_api_url");
								const defaultHostUrl = document.getElementById("useDefault");
								
								if (defaultHostUrl) {
									defaultHostUrl.onclick = function() {
										hostnameInput.value = defaultHostUrl.getAttribute('data-host-url');
									};
								}
							})();
							</script>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="wpdcsso_shared_secret"><?php esc_html_e( 'Shared Secret', 'wpdcsso' ); ?></label></th>
						<td>
							<input name="wpdcsso_shared_secret" type="password" id="wpdcsso_shared_secret" value="<?php echo esc_attr( get_option( 'wpdcsso_shared_secret' ) ); ?>" class="medium-text" autocomplete="off" />
							<p class="description"><?php esc_html_e( 'The shared secret key provided to you by DigitalChalk.', 'wpdcsso' ); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="wpdcsso_shared_token"><?php esc_html_e( 'API token', 'wpdcsso' ); ?></label></th>
						<td>
							<input name="wpdcsso_shared_token" type="password" id="wpdcsso_shared_token" value="<?php echo esc_attr( get_option( 'wpdcsso_shared_token' ) ); ?>" class="large-text" autocomplete="off" />
							<p class="description"><?php esc_html_e( 'The OAuth2 token provided to you by DigitalChalk.', 'wpdcsso' ); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="wpdcsso_email_username"><?php esc_html_e( 'User Identifier', 'wpdcsso' ); ?></label></th>
						<td>
							<fieldset>
								<legend class="screen-reader-text"><?php esc_html_e( 'User Identifier', 'wpdcsso' ); ?></legend>
								<label>
									<input type="radio" name="wpdcsso_email_username" id="wpdcsso_email_username_email" value="email" <?php checked( get_option( 'wpdcsso_email_username' ), 'email' ); ?> />
									<?php esc_html_e( 'Email Address', 'wpdcsso' ); ?>
								</label><br>
								<label>
									<input type="radio" name="wpdcsso_email_username" id="wpdcsso_email_username_username" value="username" <?php checked( get_option( 'wpdcsso_email_username' ), 'username' ); ?> />
									<?php esc_html_e( 'Username', 'wpdcsso' ); ?>
								</label>
							</fieldset>
							<p class="description"><?php esc_html_e( 'Does the user login to DigitalChalk with their email address or username?', 'wpdcsso' ); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="wpdcsso_single_sign_out"><?php esc_html_e( 'Single Sign Out?', 'wpdcsso' ); ?></label></th>
						<td>
							<fieldset>
								<legend class="screen-reader-text"><?php esc_html_e( 'Single Sign Out', 'wpdcsso' ); ?></legend>
								<label>
									<input type="radio" name="wpdcsso_single_sign_out" id="wpdcsso_single_sign_out_yes" value="yes" <?php checked( get_option( 'wpdcsso_single_sign_out' ), 'yes' ); ?> />
									<?php esc_html_e( 'Yes, log user out WordPress when they log out of DigitalChalk', 'wpdcsso' ); ?>
								</label><br>
								<label>
									<input type="radio" name="wpdcsso_single_sign_out" id="wpdcsso_single_sign_out_no" value="no" <?php checked( get_option( 'wpdcsso_single_sign_out' ), 'no' ); ?> />
									<?php esc_html_e( 'No, leave user logged into WordPress when they log out of DigitalChalk', 'wpdcsso' ); ?>
								</label>
							</fieldset>
							<p class="description"><?php esc_html_e( 'Use single sign out?', 'wpdcsso' ); ?></p>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
				</form>
			</div>
		<?php
		}

		/**
		 * Check if current URL is login URL
		 *
		 * @return bool
		 */
		public function is_login_url() {
			$server_name = isset( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) : '';
			$php_self = isset( $_SERVER['PHP_SELF'] ) ? sanitize_text_field( wp_unslash( $_SERVER['PHP_SELF'] ) ) : '';
			return $this->contains( $server_name . $php_self, wp_login_url(), true );
		}

		/**
		 * Create login URL
		 *
		 * @return string
		 */
		public function make_login_url() {
			$url = wp_login_url();
			$separator = $this->contains( '?', $url ) ? '&' : '?';
			$url .= $separator . 'action=wpdcsso_login';
			return $url;
		}

		/**
		 * Create logout URL
		 *
		 * @return string
		 */
		public function make_logout_url() {
			$url = wp_login_url();
			$separator = $this->contains( '?', $url ) ? '&' : '?';
			$url .= $separator . 'action=wpdcsso_logout';
			return $url;
		}

		/**
		 * Check if string contains content
		 *
		 * @param string $str String to search for
		 * @param string $content Content to search in
		 * @param bool $ignorecase Whether to ignore case
		 * @return bool
		 */
		public function contains( $str, $content, $ignorecase = true ) {
			if ( $ignorecase ) {
				$str = strtolower( $str );
				$content = strtolower( $content );
			}
			return ( strpos( $content, $str ) !== false );
		}

		/**
		 * Plugin activation
		 */
		public static function activate() {
			add_option( 'wpdcsso_dc_url' );
			add_option( 'wpdcsso_email_username', 'email' );
			add_option( 'wpdcsso_api_url', 'https://api.digitalchalk.com/api/v5' );
			add_option( 'wpdcsso_shared_secret' );
			add_option( 'wpdcsso_shared_token' );
			add_option( 'wpdcsso_single_sign_out', 'yes' );
		}

		/**
		 * Plugin deactivation
		 */
		public static function deactivate() {
			delete_option( 'wpdcsso_dc_url' );
			delete_option( 'wpdcsso_email_username' );
			delete_option( 'wpdcsso_api_url' );
			delete_option( 'wpdcsso_shared_secret' );
			delete_option( 'wpdcsso_shared_token' );
			delete_option( 'wpdcsso_single_sign_out' );
		}
	}

	/**
	 * Profile update handler
	 *
	 * @param int $user_id User ID
	 */
	function wpdcsso_profile_update( $user_id ) {
		$base_url = get_option( 'wpdcsso_api_url' );
		$token = get_option( 'wpdcsso_shared_token' );
		
		if ( empty( $base_url ) || empty( $token ) ) {
			return;
		}
		
		$user_info = get_userdata( $user_id );
		if ( ! $user_info ) {
			return;
		}
		
		$user_email = $user_info->user_email;
		
		// Check if user exists in DigitalChalk
		$response = wp_remote_get( 
			$base_url . '/users?email=' . urlencode( $user_email ),
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $token,
					'Content-Type' => 'application/json',
				),
				'timeout' => 30,
			)
		);
		
		if ( is_wp_error( $response ) ) {
			return;
		}
		
		$body = wp_remote_retrieve_body( $response );
		$result = json_decode( $body );
		
		// Check if email ID matches with DigitalChalk account
		if ( ! empty( $result->results ) ) {
			wpdcsso_save_user_field( $user_id, $result->results[0]->id );
		}
	}

	/**
	 * Save user field data to DigitalChalk
	 *
	 * @param int $user_id WordPress user ID
	 * @param int $dc_user_id DigitalChalk user ID
	 */
	function wpdcsso_save_user_field( $user_id, $dc_user_id ) {
		$base_url = get_option( 'wpdcsso_api_url' );
		$token = get_option( 'wpdcsso_shared_token' );
		
		if ( empty( $token ) || empty( $base_url ) ) {
			return;
		}
		
		// Get user fields from DigitalChalk
		$response = wp_remote_get(
			$base_url . '/userfields',
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $token,
					'Content-Type' => 'application/json',
				),
				'timeout' => 30,
			)
		);
		
		if ( is_wp_error( $response ) ) {
			return;
		}
		
		$body = wp_remote_retrieve_body( $response );
		$result = json_decode( $body, true );
		
		if ( empty( $result['results'] ) ) {
			return;
		}
		
		$field_mapping = array();
		foreach ( $result['results'] as $field ) {
			switch ( $field['name'] ) {
				case 'Phone Number':
					$field_mapping[ $field['id'] ] = get_user_meta( $user_id, 'billing_phone', true );
					break;
				case 'Street 1':
					$field_mapping[ $field['id'] ] = get_user_meta( $user_id, 'billing_address_1', true );
					break;
				case 'Street 2':
					$field_mapping[ $field['id'] ] = get_user_meta( $user_id, 'billing_address_2', true );
					break;
				case 'State/Province':
					$field_mapping[ $field['id'] ] = get_user_meta( $user_id, 'billing_state', true );
					break;
				case 'Postal Code':
					$field_mapping[ $field['id'] ] = get_user_meta( $user_id, 'billing_postcode', true );
					break;
				case 'City':
					$field_mapping[ $field['id'] ] = get_user_meta( $user_id, 'billing_city', true );
					break;
			}
		}
		
		// Update user profile in DigitalChalk
		$update_response = wp_remote_request(
			$base_url . '/users/' . intval( $dc_user_id ) . '/userfieldvalues',
			array(
				'method' => 'PUT',
				'headers' => array(
					'Authorization' => 'Bearer ' . $token,
					'Content-Type' => 'application/json',
					'Accept' => 'application/json',
				),
				'body' => wp_json_encode( $field_mapping ),
				'timeout' => 30,
			)
		);
		
		if ( is_wp_error( $update_response ) ) {
			error_log( 'WPDCSSO: Failed to update user fields - ' . $update_response->get_error_message() );
		}
	}

	/**
	 * Require fields script for admin
	 */
	function wpdcsso_require_fields_script() {
		?>
		<script type='text/javascript'>
			(function($){
				$('#submit').on('click', function(e){
					if ($('#billing_phone').val().length <= 6) {
						window.alert('<?php echo esc_js( __( 'Please enter valid phone number before saving.', 'wpdcsso' ) ); ?>');
						e.preventDefault();
					} 
					else if(!$('#billing_email').val()) {
						window.alert('<?php echo esc_js( __( 'Please enter your email before saving.', 'wpdcsso' ) ); ?>');
						e.preventDefault();
					}
				});
			})(jQuery);
		</script>
		<?php
	}

	// Hook the functions
	add_action( 'profile_update', 'wpdcsso_profile_update', 10, 1 );
	add_action( 'admin_footer', 'wpdcsso_require_fields_script' );
}

?>
