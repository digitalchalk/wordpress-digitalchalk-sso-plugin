<?php
if (!class_exists("WPDCSSO")) {
    class wpdcsso
    {
        public function WPDCSSO()
        {
            $this->__construct();
        }

        public function __construct()
        {
            add_action('init', array($this, 'init_action'));
            if (is_admin()) {
                add_action('admin_menu', array($this, 'init_admin_menu'));
            }
        }

        public function init_action()
        {
            global $current_user;
            $get = $_GET;
            $action = $get["action"];
            $errorCode = $get["errorcode"];
            $current_user = wp_get_current_user();
            if (isset($action) && !isset($errorCode)) {

                if ($action == 'wpdcsso_login') {

                    if (get_option('wpdcsso_dc_url')) {

                        if (is_user_logged_in()) {
                            $this->do_sso_post();
                            die;

                        } else {
                            wp_redirect(wp_login_url($this->make_wp_sso_login_url()));
                            die;
                        }
                    }

                } else if ($_REQUEST['action'] == 'wpdcsso_logout') {
                    if (get_option('wpdcsso_single_sign_out') == 'yes') {
                        if (is_user_logged_in()) {
                            wp_logout();
                            wp_redirect(home_url());
                        }
                    } else {
                        wp_redirect(home_url());
                    }
                }
            }
        }

        public function make_wp_sso_login_url()
        {
            $url = wp_login_url();
            if ($this->contains('?', $url)) {
                $url .= '&';
            } else {
                $url .= '?';
            }
            $url .= 'action=wpdcsso_login';
            return $url;
        }

        public function do_sso_post()
        {

            global $current_user;
            $current_user = wp_get_current_user();
            $timestamp = time();
            $to_hash = $timestamp . '|' . get_option("wpdcsso_shared_secret") . "|" . $current_user->user_email;
            $hash = MD5($to_hash);

            // render page to execute form
            echo '
            <html>
            <script type="text/javascript">
                window.onload = function () {
                    document.forms["ssoform"].submit();
                }
            </script>

            <body>
            <form name="ssoform" id="ssoform" action="'.get_option("wpdcsso_dc_url").'" method="POST">
            <input type="hidden" name="action" value="auth"/>
            <input type="hidden" name="timestamp" value="'.$timestamp .'"/>
            <input type="hidden" name="hash" value="'.$hash.'"/>
            <input type="hidden" name="email" value="'.$current_user->user_email.'"/>';
            
            if ($current_user->user_firstname) {
                echo '<input type="hidden" name="firstname" value="'.$current_user->user_firstname.'"/>';
            }
            
            if ($current_user->user_lastname) {
                echo '<input type="hidden" name="lastname" value="'.$current_user->user_lastname.'"/>';
            }
            
            if (get_option('wpdcsso_email_username') == 'username') {
                echo '<input type="hidden" name="username" value="'.$current_user->user_login.'"/>';
            }

            echo '</form></body></html>';
        }

        public function append_debug_content($content)
        {

            $content .= 'This is the append_debug_content';

            return $content;

        }

        public function init_admin_menu()
        {

            add_options_page('DigitalChalk SSO Options', 'DigitalChalk SSO', 'manage_options', 'wpdcsso_options', array($this, 'display_settings_page'));

        }

        public function display_settings_page()
        {
            ?>

            <div class="wrap">
                <?php screen_icon('options-general');?>
                <h2><?php esc_html_e('DigitalChalk SSO Options', 'wpdcsso');?></h2>
                <form action="options.php" method="post">
                <?php wp_nonce_field('update-options');?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><label for="wpdcsso_login_url"><?php _e('Login URL', 'wpdcsso');?></label></th>
                        <td>
                            <p><?php echo $this->make_login_url(); ?></p>
                            <p class="description"><?php _e('Copy this value into Login URL in your DigitalChalk account', 'wpdcsso');?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="wpdcsso_logout_url"><?php _e('Logout URL', 'wpdcsso');?></label></th>
                        <td>
                            <p><?php echo $this->make_logout_url(); ?></p>
                            <p class="description"><?php _e('Copy this value into Logout URL in your DigitalChalk account', 'wpdcsso');?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="wpdcsso_dc_url"><?php _e('DigitalChalk SSO URL', 'wpdcsso');?></label></th>
                        <td>
                            <input name="wpdcsso_dc_url" type="text" id="wpdcsso_dc_url" value="<?php echo get_option('wpdcsso_dc_url'); ?>" class="large-text" />
                            <p class="description"><?php _e('The DC SSO URL provided to you by DigitalChalk.', 'wpdcsso');?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="wpdcsso_api_url"><?php _e('DigitalChalk API URL', 'wpdcsso');?></label></th>
                        <td>
                            <input name="wpdcsso_api_url" type="text" id="wpdcsso_api_url" value="<?php echo get_option('wpdcsso_api_url'); ?>" class="large-text" />
                            <p class="description"><?php _e('The API URL provided to you by DigitalChalk.', 'wpdcsso');?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="wpdcsso_shared_secret"><?php _e('Shared Secret', 'wpdcsso');?></label></th>
                        <td>
                            <input name="wpdcsso_shared_secret" type="text" id="wpdcsso_shared_secret" value="<?php echo get_option('wpdcsso_shared_secret'); ?>" class="medium-text" />
                            <p class="description"><?php _e('The shared secret key provided to you by DigitalChalk.', 'wpdcsso');?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="wpdcsso_shared_token"><?php _e('API token', 'wpdcsso');?></label></th>
                        <td>
                            <input name="wpdcsso_shared_token" type="text" id="wpdcsso_shared_token" value="<?php echo get_option('wpdcsso_shared_token'); ?>" class="large-text" />
                            <p class="description"><?php _e('The OAuth2 token provided to you by DigitalChalk.', 'wpdcsso');?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="wpdcsso_email_username"><?php _e('User Identifier', 'wpdcsso');?></label></th>
                        <td>
                            <p>
                                <input type="radio" name="wpdcsso_email_username" id="wpdcsso_email_username_email" value="email" <?php if (get_option('wpdcsso_email_username') == 'email') {echo 'CHECKED';}?>/> Email Address
                            </p>
                            <p>
                                <input type="radio" name="wpdcsso_email_username" id="wpdcsso_email_username_username" value="username" <?php if (get_option('wpdcsso_email_username') == 'username') {echo 'CHECKED';}?>/> Username
                            </p>
                            <p class="description"><?php _e('Does the user login to DigitalChalk with their email address or username?', 'wpdcsso');?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="wpdcsso_single_sign_out"><?php _e('Single Sign Out?', 'wpdcsso');?></label></th>
                        <td>
                            <p>
                                <input type="radio" name="wpdcsso_single_sign_out" id="wpdcsso_single_sign_out_yes" value="yes" <?php if (get_option('wpdcsso_single_sign_out') == 'yes') {echo 'CHECKED';}?>/> Yes, log user out Wordpress when they log out of DigitalChalk
                            </p>
                            <p>
                                <input type="radio" name="wpdcsso_single_sign_out" id="wpdcsso_single_sign_out_no" value="no" <?php if (get_option('wpdcsso_single_sign_out') == 'no') {echo 'CHECKED';}?>/> No, leave user logged into Wordpress when they log out of DigitalChalk
                            </p>
                            <p class="description"><?php _e('Use single sign out?', 'wpdcsso');?></p>
                        </td>
                    </tr>
                </table>
                <p>
                <input type="submit" class="button-primary" value="<?php _e('Save Changes')?>" />
                </p>
                <input type="hidden" name="action" value="update" />
                <input type="hidden" name="page_options" value="wpdcsso_dc_url,wpdcsso_email_username,wpdcsso_api_url,wpdcsso_shared_secret,wpdcsso_shared_token,wpdcsso_single_sign_out" />
                </form>
            </div>
		<?php
        }

        public function is_login_url()
        {
            return $this->contains($_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'], wp_login_url(), true);
        }

        public function make_login_url()
        {
            $url = wp_login_url();
            if ($this->contains('?', $url)) {
                $url .= '&';
            } else {
                $url .= '?';
            }
            $url .= 'action=wpdcsso_login';
            return $url;
        }

        public function make_logout_url()
        {
            $url = wp_login_url();
            if ($this->contains('?', $url)) {
                $url .= '&';
            } else {
                $url .= '?';
            }

            $url .= 'action=wpdcsso_logout';
            return $url;
        }

        public function contains($str, $content, $ignorecase = true)
        {
            if ($ignorecase) {
                $str = strtolower($str);
                $content = strtolower($content);
            }
            return (strpos($content, $str) !== false) ? true : false;
        }

        public static function activate()
        {
            add_option('wpdcsso_dc_url');
            add_option('wpdcsso_email_username', 'email');
            add_option('wpdcsso_api_url');
            add_option('wpdcsso_shared_secret');
            add_option('wpdcsso_shared_token');
            add_option('wpdcsso_single_sign_out', 'yes');
        }

        public static function deactivate()
        {
            delete_option('wpdcsso_dc_url');
            delete_option('wpdcsso_email_username');
            delete_option('wpdcsso_api_url');
            delete_option('wpdcsso_shared_secret');
            delete_option('wpdcsso_shared_token');
            delete_option('wpdcsso_single_sign_out');
        }

    }

    // Function for get user id of update
    // add_action('personal_options_update', 'my_profile_update');
    // add_action('edit_user_profile_update', 'my_profile_update');
    add_action('profile_update', 'my_profile_update', 10, 2);
    function my_profile_update($user_id)
    {
        $baseUrl = get_option('wpdcsso_api_url'); //https://api.digitalchalk.com/dc/api/v5
        $user_info = get_userdata($user_id);
        $user_email = $user_info->user_email;
        //  Get Token
        $token = get_option('wpdcsso_shared_token');
        //  Check User Available With Email id
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $baseUrl."/users?email=" . $user_email,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer " . $token,
                "Content-Type:  application/json",
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $result = json_decode($response);

        // Check Email Id Match With Dcsso Account
        if (!empty($result->results)) {

            save_userfiled($user_id, $result->results[0]->id);
        }
    }

    function save_userfiled($user_id, $sscid)
    {
        $baseUrl = get_option('wpdcsso_api_url'); //https://api.digitalchalk.com/dc/api/v5
        //  Get Token
        $token = get_option('wpdcsso_shared_token');
        if ($token) {
            $curl2 = curl_init();

            curl_setopt_array($curl2, array(
                CURLOPT_URL => $baseUrl."/userfields",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "Authorization: Bearer " . $token,
                    "Content-Type:  application/json",
                ),
            ));

            $response = curl_exec($curl2);

            curl_close($curl2);
            $res = json_decode($response, true);
            foreach ($res['results'] as $ufild) {
                if ($ufild['name'] == "Phone Number") {
                    $phone = $ufild['id'];
                }

                if ($ufild['name'] == "Street 1") {
                    $street1 = $ufild['id'];
                }

                if ($ufild['name'] == "Street 2") {
                    $street2 = $ufild['id'];
                }

                if ($ufild['name'] == "State/Province") {
                    $state = $ufild['id'];
                }

                if ($ufild['name'] == "Postal Code") {
                    $pcode = $ufild['id'];
                }

                if ($ufild['name'] == "City") {
                    $city = $ufild['id'];
                }

            }

            $cssoid = $sscid;
            $user_info = array($phone => get_user_meta($user_id, 'billing_phone', true), $street1 => get_user_meta($user_id, 'billing_address_1', true), $street2 => get_user_meta($user_id, 'billing_address_2', true), $state => get_user_meta($user_id, 'billing_state', true), $pcode => get_user_meta($user_id, 'billing_postcode', true), $city => get_user_meta($user_id, 'billing_city', true));
            // update_user_profile($cssoid, $user_info);
            $curl3 = curl_init();

            curl_setopt_array($curl3, array(
                CURLOPT_URL => $baseUrl."/users/" . $cssoid . "/userfieldvalues",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => "PUT",
                CURLOPT_POSTFIELDS => json_encode($user_info),
                CURLOPT_HTTPHEADER => array(
                    "Authorization: Bearer " . $token,
                    "Content-Type:  application/json",
                    "Accept:  application/json",
                ),
            ));
    
            $response = curl_exec($curl3);
    
            curl_close($curl3);
            $res = json_decode($response);
        }
    }


    function require_fields_script(){
        echo "
            <script type='text/javascript'>
                (function($){
                    $('#submit').on('click',function(e){
                        
                           
                            if ($('#billing_phone').val().length <= 6) {
                                window.alert('Please enter valid phone number before saving.');
                                e.preventDefault();
                            } 
                            else if(!$('#billing_email').val()) {
                                window.alert('Please enter your email before saving.');
                                e.preventDefault();
                            }
                           
                        
                    });
                })(jQuery);
            </script>";
    }
    add_action( 'admin_footer', 'require_fields_script' );
}

?>
