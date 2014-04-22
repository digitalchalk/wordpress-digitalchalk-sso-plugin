<?php
if (!class_exists("WPDCSSO")) {
	class wpdcsso {

		function WPDCSSO() {
			$this->__construct();			
		}
		
		function __construct() {
			add_action('init', array($this, 'init_action'));
			if(is_admin()) {
				add_action('admin_menu', array($this, 'init_admin_menu'));
			}
		}
		
		function init_action() {
			global $current_user;
			wp_get_current_user();
			
			if($this->is_login_url() && isset($_REQUEST['action']) && !isset($_REQUEST['errorcode'])) {
				if($_REQUEST['action'] == 'wpdcsso_login') {
					if(get_option('wpdcsso_dc_url')) {
						if(is_user_logged_in()) {
							$this->do_sso_post();
							exit;
						} else {
							wp_redirect(wp_login_url($this->make_wp_sso_login_url()));
							exit;
						}
					}
				} else if($_REQUEST['action'] == 'wpdcsso_logout') {
					if(get_option('wpdcsso_single_sign_out') == 'yes') {
						if(is_user_logged_in()) {
							wp_logout();
							wp_redirect(home_url());
						}
					} else {
						wp_redirect(home_url());
					}
				}
			}
		}
		
		function make_wp_sso_login_url() {
			$url = wp_login_url();
			if($this->contains('?', $url)) {
				$url .= '&';
			} else {
				$url .= '?';
			}
			$url .= 'action=wpdcsso_login';
			return $url;
		}
		
		function do_sso_post() {
			global $current_user;
			wp_get_current_user();

			$timestamp = time();
			$to_hash = $timestamp . '|' . get_option("wpdcsso_shared_secret") . "|" . $current_user->user_email;
			$hash = MD5($to_hash);
			?>
<html>
<script type="text/javascript">
window.onload=function() {	
	document.forms['ssoform'].submit();
}
</script>
<body>
<form name="ssoform" id="ssoform" action="<?php echo get_option('wpdcsso_dc_url'); ?>" method="POST">
<input type="hidden" name="timestamp" value="<?php echo $timestamp ?>"/>
<input type="hidden" name="hash" value="<?php echo $hash; ?>"/>
<input type="hidden" name="email" value="<?php echo $current_user->user_email?>":/>
<?php
	if($current_user->user_firstname) {
?>
<input type="hidden" name="firstname" value="<?php echo $current_user->user_firstname?>":/>
<?php
	}
	if($current_user->user_lastname) {
?>
<input type="hidden" name="lastname" value="<?php echo $current_user->user_lastname?>":/>
<?php
	}
?>
<?php
	if(get_option('wpdcsso_email_username') == 'username') {
?>
<input type="hidden" name="username" value="<?php echo $current_user->user_login?>":/>
<?php
}
?>
</form>
</body>
</html>		
			<?php
		}
		
		function append_debug_content($content) {
			$content .= 'This is the append_debug_content';
			
			return $content;
		}
		
		function init_admin_menu() {
			add_options_page('DigitalChalk SSO Options', 'DigitalChalk SSO','manage_options','wpdcsso_options', array($this, 'display_settings_page'));
		}
		
		function display_settings_page() {
		?>
<div class="wrap">
    <?php screen_icon( 'options-general' ); ?>
    <h2><?php esc_html_e( 'DigitalChalk SSO Options', 'wpdcsso' ); ?></h2>

    <form action="options.php" method="post">
	<?php wp_nonce_field('update-options'); ?>	
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><label for="wpdcsso_login_url"><?php _e( 'Login URL', 'wpdcsso' ); ?></label></th>
			<td>
				<p><?php echo $this->make_login_url(); ?></p>
				<p class="description"><?php _e( 'Copy this value into Login URL in your DigitalChalk account', 'wpdcsso' ); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="wpdcsso_logout_url"><?php _e( 'Logout URL', 'wpdcsso' ); ?></label></th>
			<td>
				<p><?php echo $this->make_logout_url(); ?></p>
				<p class="description"><?php _e( 'Copy this value into Logout URL in your DigitalChalk account', 'wpdcsso' ); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="wpdcsso_dc_url"><?php _e( 'DigitalChalk SSO URL', 'wpdcsso' ); ?></label></th>
			<td>
				<input name="wpdcsso_dc_url" type="text" id="wpdcsso_dc_url" value="<?php echo get_option('wpdcsso_dc_url');?>" class="large-text" />
				<p class="description"><?php _e( 'The DC SSO URL provided to you by DigitalChalk.', 'wpdcsso' ); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="wpdcsso_shared_secret"><?php _e( 'Shared Secret', 'wpdcsso' ); ?></label></th>
			<td>
				<input name="wpdcsso_shared_secret" type="text" id="wpdcsso_shared_secret" value="<?php echo get_option('wpdcsso_shared_secret');?>" class="medium-text" />
				<p class="description"><?php _e( 'The shared secret key provided to you by DigitalChalk.', 'wpdcsso' ); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="wpdcsso_email_username"><?php _e( 'User Identifier', 'wpdcsso' ); ?></label></th>
			<td>
				<p>
					<input type="radio" name="wpdcsso_email_username" id="wpdcsso_email_username_email" value="email" <?php if(get_option('wpdcsso_email_username') == 'email') { echo 'CHECKED';}?>/> Email Address					
				</p>
				<p>
					<input type="radio" name="wpdcsso_email_username" id="wpdcsso_email_username_username" value="username" <?php if(get_option('wpdcsso_email_username') == 'username') { echo 'CHECKED';}?>/> Username
				</p>
				<p class="description"><?php _e( 'Does the user login to DigitalChalk with their email address or username?', 'wpdcsso' ); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="wpdcsso_single_sign_out"><?php _e( 'Single Sign Out?', 'wpdcsso' ); ?></label></th>
			<td>
				<p>
					<input type="radio" name="wpdcsso_single_sign_out" id="wpdcsso_single_sign_out_yes" value="yes" <?php if(get_option('wpdcsso_single_sign_out') == 'yes') { echo 'CHECKED';}?>/> Yes, log user out Wordpress when they log out of DigitalChalk					
				</p>
				<p>
					<input type="radio" name="wpdcsso_single_sign_out" id="wpdcsso_single_sign_out_no" value="no" <?php if(get_option('wpdcsso_single_sign_out') == 'no') { echo 'CHECKED';}?>/> No, leave user logged into Wordpress when they log out of DigitalChalk
				</p>
				<p class="description"><?php _e( 'Use single sign out?', 'wpdcsso' ); ?></p>
			</td>
		</tr>
	</table>
	<p>
	<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
	</p>
	<input type="hidden" name="action" value="update" />	
	<input type="hidden" name="page_options" value="wpdcsso_dc_url,wpdcsso_email_username,wpdcsso_shared_secret,wpdcsso_single_sign_out" />
	</form>
</div>
		<?php
		}
		
		
		function is_login_url() {
			return $this->contains($_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'], wp_login_url(), true);
		}
		
		function make_login_url() {
			$url = wp_login_url();
			if($this->contains('?', $url)) {
				$url .= '&';
			} else {
				$url .= '?';
			}
			$url .= 'action=wpdcsso_login';
			return $url;
		}

		function make_logout_url() {
			$url = wp_login_url();
			if($this->contains('?', $url)) {
				$url .= '&';
			} else {
				$url .= '?';
			}
			$url .= 'action=wpdcsso_logout';
			return $url;
		}

		
		function contains($str, $content, $ignorecase=true){
			if ($ignorecase){
				$str = strtolower($str);
				$content = strtolower($content);
			}  
			return (strpos($content,$str)!==false) ? true : false;
		}
		
		
		static function activate() {
			add_option('wpdcsso_dc_url');
			add_option('wpdcsso_email_username', 'email');
			add_option('wpdcsso_shared_secret');
			add_option('wpdcsso_single_sign_out', 'yes');
		}
		
		static function deactivate() {
			delete_option('wpdcsso_dc_url');
			delete_option('wpdcsso_email_username');
			delete_option('wpdcsso_shared_secret');
			delete_option('wpdcsso_single_sign_out');
		}
		
	}
}
?>