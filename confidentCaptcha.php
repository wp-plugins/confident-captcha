<?php
require_once('wp-plugin.php');
if (!class_exists('confidentCaptcha')) {
    class confidentCaptcha extends WPPlugin {
        private $saved_error;
        function confidentCaptcha($options_name) {
            $args = func_get_args();
            call_user_func_array(array(&$this, "__construct"), $args);
        }
        function __construct($options_name) {
            parent::__construct($options_name);
            $this->register_default_options();
            $this->require_library();
            $this->register_actions();
            $this->register_filters();
        }
        function register_actions() {
            add_action('wp_head', array(&$this, 'register_stylesheets')); 
            add_action('admin_head', array(&$this, 'register_stylesheets'));
            register_activation_hook(WPPlugin::path_to_plugin_directory() . '/wp-confidentCaptcha.php', array(&$this, 'register_default_options')); 
            add_action('admin_init', array(&$this, 'register_settings_group'));
            if ($this->options['show_in_registration']) {
                if ($this->is_multi_blog())
                    add_action('signup_extra_fields', array(&$this, 'show_confidentCaptcha_in_registration'));
                else
                    add_action('register_form', array(&$this, 'show_confidentCaptcha_in_registration'));
            }
			if($this->options['show_in_lost_password'])
			    add_action('lostpassword_form', array(&$this, 'show_confidentCaptcha_in_registration'));
			if($this->options['show_in_login_page'])
			    add_action('login_form', array(&$this, 'show_confidentCaptcha_in_registration'));
            if ($this->options['show_in_comments']) {
                add_action('comment_form', array(&$this, 'show_confidentCaptcha_in_comments'));
                add_action('wp_head', array(&$this, 'saved_comment'), 0);
                add_action('preprocess_comment', array(&$this, 'check_comment'), 0);
                add_action('comment_post_redirect', array(&$this, 'relative_redirect'), 0, 2);
            }
            add_filter("plugin_action_links", array(&$this, 'show_settings_link'), 10, 2);
            add_action('admin_menu', array(&$this, 'add_settings_page'));
            add_action('admin_notices', array(&$this, 'missing_keys_notice'));
        }
        
        function register_filters() {
            if ($this->options['show_in_registration']) {
                if ($this->is_multi_blog()) {
                    add_filter('wpmu_validate_user_signup', array(&$this, 'validate_confidentCaptcha_response_wpmu'));
			    }
                else  {
                    add_filter('registration_errors', array(&$this, 'validate_confidentCaptcha_response'));
				}
            }
            //add_action('lostpassword_post', array(&$this, 'confidentCaptcha_check_lost_password'));
			if($this->options['show_in_lost_password'])
			    add_filter('allow_password_reset', array(&$this, 'confidentCaptcha_check_lost_password'),1);
			if($this->options['show_in_login_page'])
			    add_filter('authenticate', array(&$this, 'check_login'),40,3);
        }
        
        function load_textdomain() {
            load_plugin_textdomain('confidentCaptcha', false, 'languages');
        }
        function register_default_options() {
            if ($this->options)
               return;
            $option_defaults = array();
            $old_options = WPPlugin::retrieve_options("confidentCaptcha");
            if ($old_options) {
               $option_defaults['site_id'] = $old_options['siteid'];
               $option_defaults['customer_id'] = $old_options['customerid'];
               $option_defaults['api_username'] = $old_options['apiusername'];
               $option_defaults['api_password'] = $old_options['apipassword'];
               $option_defaults['show_in_comments'] = $old_options['cc_comments'];
               $option_defaults['show_in_registration'] = $old_options['cc_registration'];
               $option_defaults['bypass_for_registered_users'] = ($old_options['cc_bypass'] == "on") ? 1 : 0;
               $option_defaults['minimum_bypass_level'] = $old_options['cc_bypasslevel'];
               if ($option_defaults['minimum_bypass_level'] == "level_10") {
                  $option_defaults['minimum_bypass_level'] = "activate_plugins";
               }
			   $option_defaults['image_code_color'] = $old_options['cc_code_color'];
			   $option_defaults['noise_level'] = $old_options['cc_noise_level'];
			   $option_defaults['display_style'] = $old_options['cc_display_style'];
               $option_defaults['confidentCaptcha_language'] = $old_options['cc_lang'];
               $option_defaults['xhtml_compliance'] = $old_options['cc_xhtml'];
               $option_defaults['comments_tab_index'] = $old_options['cc_tabindex'];
               $option_defaults['registration_tab_index'] = 30;
			   $option_defaults['captcha_length'] = 4;
			   $option_defaults['captcha_width'] = 3;
			   $option_defaults['captcha_height'] = 3;
			   $option_defaults['captcha_logo'] = '';
			   $option_defaults['captcha_billboard'] = '';
               $option_defaults['no_response_error'] = $old_options['error_blank'];
               $option_defaults['incorrect_response_error'] = $old_options['error_incorrect'];
            }
           
            else {
               $option_defaults['site_id'] = '';
               $option_defaults['customer_id'] = '';
               $option_defaults['api_username'] = '';
               $option_defaults['api_password'] = '';
               $option_defaults['show_in_comments'] = 1;
               $option_defaults['show_in_registration'] = 1;
			   $option_defaults['show_in_lost_password'] = 1;
			   $option_defaults['show_in_login_page'] = 0;
               $option_defaults['bypass_for_registered_users'] = 1;
               $option_defaults['minimum_bypass_level'] = 'read';
			   $option_defaults['image_code_color'] = 'White';
			   $option_defaults['noise_level'] = '.10';
			   $option_defaults['display_style'] = 'flyout';
               $option_defaults['confidentCaptcha_language'] = 'en';
               $option_defaults['xhtml_compliance'] = 0;
               $option_defaults['comments_tab_index'] = 5;
               $option_defaults['registration_tab_index'] = 30;
			   $option_defaults['captcha_width'] = 3;
			   $option_defaults['captcha_height'] = 3;
			   $option_defaults['captcha_length'] = 4;
			   $option_defaults['captcha_logo'] = '';
			   $option_defaults['captcha_billboard'] = '';
               $option_defaults['no_response_error'] = '<strong>ERROR</strong>: Please solve the Confident CAPTCHA.';
               $option_defaults['incorrect_response_error'] = '<strong>ERROR</strong>: That Confident CAPTCHA response was incorrect.';
            }
            WPPlugin::add_options($this->options_name, $option_defaults);
        }
        function require_library() {
            require_once($this->path_to_plugin_directory() . '/confidentCaptchalib.php');
        }
        function register_settings_group() {
            register_setting("confidentCaptcha_options_group", 'confidentCaptcha_options', array(&$this, 'validate_options'));
        }
        function register_stylesheets() {
            $path = WPPlugin::url_to_plugin_directory() . '/confidentCaptcha.css';
            echo '<link rel="stylesheet" type="text/css" href="' . $path . '" />';
        }
		function register_js() {
		    wp_enqueue_script('jquery');
        }
        function confidentCaptcha_enabled() {
            return ($this->options['show_in_comments'] || $this->options['show_in_registration'] || $this->options['show_in_login_page'] || $this->options['show_in_lost_password']);
        }
        function keys_missing() {
            return (empty($this->options['site_id']) || empty($this->options['customer_id']) || empty($this->options['api_username']) || empty($this->options['api_password']));
        }
        function create_error_notice($message, $anchor = '') {
            $options_url = admin_url('options-general.php?page=confident-captcha/confidentCaptcha.php') . $anchor;
            $error_message = sprintf(__($message . ' <a href="http://www.confidenttechnologies.com/Get_Confident_CAPTCHA" target="_blank" title="WP-Confident CAPTCHA Options">Fix this</a>', 'confidentCaptcha'), $options_url);
            echo '<div class="error"><p><strong>' . $error_message . '</strong></p></div>';
        }
        function missing_keys_notice() {
            if ($this->confidentCaptcha_enabled() && $this->keys_missing()) {
                $this->create_error_notice('You enabled <strong>Confident CAPTCHA</strong>, but some of the Confident CAPTCHA API information seems to be missing.');
            }
        }
        function validate_dropdown($array, $key, $value) {
            if (in_array($value, $array))
                return $value;
            else
                return $this->options[$key];
        }
        function validate_options($input) {
            $validated['site_id'] = trim($input['site_id']);
            $validated['customer_id'] = trim($input['customer_id']);
            $validated['api_username'] = trim($input['api_username']);
            $validated['api_password'] = trim($input['api_password']);
            $validated['show_in_comments'] = ($input['show_in_comments'] == 1 ? 1 : 0);
			$validated['show_in_lost_password'] = ($input['show_in_lost_password'] == 1 ? 1 : 0);
			$validated['show_in_login_page'] = ($input['show_in_login_page'] == 0 ? 0 : 1 );
            $validated['bypass_for_registered_users'] = ($input['bypass_for_registered_users'] == 1 ? 1: 0);
            $capabilities = array ('read', 'edit_posts', 'publish_posts', 'moderate_comments', 'activate_plugins');
			$codeColors = array ('White', 'Red', 'Green','Maroon','Grey');
			$noiseLvls = array ('.10', '.20', '.30', '.40', '.50', '.60', '.70', '.80', '.90');
			$displayStyles = array ('lightbox', 'flyout');
            $confidentCaptcha_languages = array ('en', 'nl', 'fr', 'de', 'pt', 'ru', 'es', 'tr');
            $validated['minimum_bypass_level'] = $this->validate_dropdown($capabilities, 'minimum_bypass_level', $input['minimum_bypass_level']);
			$validated['image_code_color'] = $this->validate_dropdown($codeColors, 'image_code_color', $input['image_code_color']);
			$validated['noise_level'] = $this->validate_dropdown($noiseLvls, 'noise_level', $input['noise_level']);
			$validated['display_style'] = $this->validate_dropdown($displayStyles, 'display_style', $input['display_style']);
            $validated['comments_tab_index'] = $input['comments_tab_index'] ? $input["comments_tab_index"] : 5;
            $validated['show_in_registration'] = ($input['show_in_registration'] == 1 ? 1 : 0);
            $validated['registration_tab_index'] = $input['registration_tab_index'] ? $input["registration_tab_index"] : 30;
			$validated['captcha_width'] = $input['captcha_width'] ? $input["captcha_width"] : 3;
            $validated['captcha_height'] = $input['captcha_height'] ? $input["captcha_height"] : 3;
            $validated['captcha_length'] = $input['captcha_length'] ? $input["captcha_length"] : 4;
			$validated['captcha_logo'] = $input['captcha_logo'] ? $input["captcha_logo"] : '';
			$validated['captcha_billboard'] = $input['captcha_billboard'] ? $input["captcha_billboard"] : '';			
            $validated['no_response_error'] = $input['no_response_error'];
            $validated['incorrect_response_error'] = $input['incorrect_response_error'];
            return $validated;
        }
        function show_confidentCaptcha_in_registration($errors) {
		    echo '<script src="http://code.jquery.com/jquery-latest.min.js"
        type="text/javascript"></script>';
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on")
                $use_ssl = true;
            else
                $use_ssl = false;
            $escaped_error = htmlentities($_GET['rerror'], ENT_QUOTES);
            if ($this->is_multi_blog()) {
                $error = $errors->get_error_message('captcha');
                echo '<label for="verification">Verification:</label>';
                echo ($error ? '<p class="error">'.$error.'</p>' : '');
                echo $format . $this->get_confidentCaptcha_html($escaped_error, $use_ssl);
            }
            else {
                echo $this->get_confidentCaptcha_html($escaped_error, $use_ssl);
            }
        }
        function validate_confidentCaptcha_response($errors) {
            if (empty($_POST['confidentcaptcha_code']) || $_POST['confidentcaptcha_code'] == '') {
                $errors->add('blank_captcha', $this->options['no_response_error']);
                return $errors;
            }
            $validationData = array (
                'api_username'=>$this->options['api_username'],
                'api_password'=>$this->options['api_password'],
                'customer_id'=>$this->options['customer_id'],
                'site_id'=>$this->options['site_id'],
                'library_version'=>'20120625',
				'click_coordinates'=>$_POST['confidentcaptcha_click_coordinates'],
				'code'=>$_POST['confidentcaptcha_code'],
				'confidentCaptchaID'=>$_POST['confidentcaptcha_captcha_id']
            );
            $response = confidentCaptcha_check_answer($validationData);
            if (!$response->is_valid)
               $errors->add('captcha_wrong', $this->options['incorrect_response_error']);
            return $errors;
        }
        function validate_confidentCaptcha_response_wpmu($result) {            
            if (!$this->is_authority()) {
                if (isset($_POST['blog_id']) || isset($_POST['blogname']))
                    return $result;
                $validationData = array (
                    'api_username'=>$this->options['api_username'],
                    'api_password'=>$this->options['api_password'],
                    'customer_id'=>$this->options['customer_id'],
                    'site_id'=>$this->options['site_id'],
                    'library_version'=>'20120625',
				    'click_coordinates'=>$_POST['confidentcaptcha_click_coordinates'],
	    			'code'=>$_POST['confidentcaptcha_code'],
					'confidentCaptchaID'=>$_POST['confidentcaptcha_captcha_id']
                );
                $response = confidentCaptcha_check_answer($validationData);
                
                if (!$response->is_valid) {
                   $result['errors']->add('captcha_wrong', $this->options['incorrect_response_error']);
                   echo '<div class="error">' . $this->options['incorrect_response_error'] . '</div>';
                }
                return $result;
            }
        }
        function hash_comment($id) {
            define ("confidentCaptcha_WP_HASH_SALT", "b7e0638d85f5d7f3694f68e944136d62");
            
            if (function_exists('wp_hash'))
                return wp_hash(confidentCaptcha_WP_HASH_SALT . $id);
            else
                return md5(confidentCaptcha_WP_HASH_SALT . $this->options['site_id'] . $id);
        }
        function get_confidentCaptcha_html($confidentCaptcha_error, $use_ssl=false) {
		    $pageURL = $_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://';
            $pageURL .= $_SERVER['SERVER_PORT'] != '80' ? $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"] : $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
		    $options = array ( 
                'api_username'=>$this->options['api_username'],
                'api_password'=>$this->options['api_password'],
                'customer_id'=>$this->options['customer_id'],
                'site_id'=>$this->options['site_id'],
                'library_version'=>'20120625',
                'language'=>'en',
                'local_server_name'=>$pageURL,
                'local_server_address'=>'127.0.0.1',
                'width'=>$this->options['captcha_width'],
                'height'=>$this->options['captcha_height'],
                'captcha_length'=>$this->options['captcha_length'],
				'image_code_color'=>$this->options['image_code_color'],
                'display_style'=>$this->options['display_style'],
                'logo_name'=>$this->options['captcha_logo'],
                'billboard_name'=>$this->options['captcha_billboard'],
                'noise_level'=>$this->options['noise_level']
			);
            return confidentCaptcha_get_html($options, $confidentCaptcha_error, $use_ssl, $this->options['xhtml_compliance']);
        }

        function show_confidentCaptcha_in_comments() {
            global $user_ID, $email;
		    echo '<script src="http://code.jquery.com/jquery-latest.min.js"
        type="text/javascript"></script>';
            if (isset($this->options['bypass_for_registered_users']) && $this->options['bypass_for_registered_users'] && $this->options['minimum_bypass_level'])
                $needed_capability = $this->options['minimum_bypass_level'];

            if ((isset($needed_capability) && $needed_capability && current_user_can($needed_capability)) || !$this->options['show_in_comments'])
                return;

            else {
                if ((isset($_GET['rerror']) && $_GET['rerror'] == 'ConfidentCAPTCHAwassolvedincorrectly'))
                    echo '<p class="confidentCaptcha-error">' . $this->options['incorrect_response_error'] . "</p>";
                add_action('wp_footer', array(&$this, 'save_comment_script'));
                if ($this->options['xhtml_compliance']) {
                    $comment_string = <<<COMMENT_FORM
                        <div id="confidentCaptcha-submit-btn-area">&nbsp;</div>
COMMENT_FORM;
                }
                else {
                    $comment_string = <<<COMMENT_FORM
                        <div id="confidentCaptcha-submit-btn-area">&nbsp;</div>
                        <noscript>
                         <style type='text/css'>#submit {display:none;}</style>
                         <input name="submit" type="submit" id="submit-alt" tabindex="6" value="Submit Comment"/> 
                        </noscript>
COMMENT_FORM;
                }
                $use_ssl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on");
                $escaped_error = htmlentities($_GET['rerror'], ENT_QUOTES);
                echo $this->get_confidentCaptcha_html(isset($escaped_error) ? $escaped_error : null, $use_ssl) . $comment_string;
           }
		   return true;
        }
        function save_comment_script() {
            $javascript = <<<JS
                <script type="text/javascript">
                var sub = document.getElementById('submit');
                document.getElementById('confidentCaptcha-submit-btn-area').appendChild (sub);
                document.getElementById('submit').tabIndex = 6;
                if ( typeof _confidentCaptcha_wordpress_savedcomment != 'undefined') {
                        document.getElementById('comment').value = _confidentCaptcha_wordpress_savedcomment;
                }
                document.getElementById('confidentCaptcha_table').style.direction = 'ltr';
                </script>
JS;
            echo $javascript;
        }
        function show_captcha_for_comment() {
            global $user_ID;
            return true;
        }
        function check_comment($comment) {
            global $user_ID;
            if ($this->options['bypass_for_registered_users'] && $this->options['minimum_bypass_level'])
                $needed_capability = $this->options['minimum_bypass_level'];
            if (($needed_capability && current_user_can($needed_capability)) || !$this->options['show_in_comments'])
                return $comment;
            if ($this->show_captcha_for_comment()) {
                if ($comment['comment_type'] == '') {
	            $validationData = array (
						'api_username'=>$this->options['api_username'],
						'api_password'=>$this->options['api_password'],
						'customer_id'=>$this->options['customer_id'],
						'site_id'=>$this->options['site_id'],
						'library_version'=>'20120625',
						'click_coordinates'=>$_POST['confidentcaptcha_click_coordinates'],
						'code'=>$_POST['confidentcaptcha_code'],
						'confidentCaptchaID'=>$_POST['confidentcaptcha_captcha_id']
					);
                    $confidentCaptcha_response = confidentCaptcha_check_answer($validationData);
                    if ($confidentCaptcha_response->is_valid)
					{
                        return $comment;
				    }
                    else {
                       $this->saved_error = $confidentCaptcha_response->error;
					   add_filter('pre_comment_approved', create_function('$a', 'return \'spam\';'));
					   return $comment;
                    }
                }
            }
            return $comment;
        }
		function confidentCaptcha_check_lost_password($user) {
            if (empty($_POST['confidentcaptcha_code']) || $_POST['confidentcaptcha_code'] == '') {
               $user = new WP_Error( 'blank_captcha', __($this->options['no_response_error'], 'confidentCaptcha'));
               return $user;
            }
            $validationData = array (
				'api_username'=>$this->options['api_username'],
				'api_password'=>$this->options['api_password'],
				'customer_id'=>$this->options['customer_id'],
				'site_id'=>$this->options['site_id'],
				'library_version'=>'20120625',
				'click_coordinates'=>$_POST['confidentcaptcha_click_coordinates'],
				'code'=>$_POST['confidentcaptcha_code'],
				'confidentCaptchaID'=>$_POST['confidentcaptcha_captcha_id']
			);
            $confidentCaptcha_response = confidentCaptcha_check_answer($validationData);
			if(!$confidentCaptcha_response->is_valid)
			{
               $user = new WP_Error( 'captcha_wrong', __($this->options['incorrect_response_error'], 'confidentCaptcha'));
			   return $user;
			}
			return true;
		}
		function check_login($user, $username, $password)
		{
		    if ( is_a($user, 'WP_User') ) { return $user; }

	    	if ( empty($username) || empty($password) || isset($_POST['confidentcaptcha_code']) && empty($_POST['confidentcaptcha_code'])) {
		        $error = new WP_Error();
			    if ( empty($username) )
				    $error->add('empty_username', __('<strong>ERROR</strong>: The username field is empty.'));

			    if ( empty($password) )
				    $error->add('empty_password', __('<strong>ERROR</strong>: The password field is empty.'));

                if (isset($_POST['confidentcaptcha_code']) && empty($_POST['confidentcaptcha_code'])) {
                    $empty_captcha = ($this->options['no_response_error'] != '') ? $this->options['no_response_error'] : __('Empty CAPTCHA', 'confidentCaptcha');
                    $error->add('empty_captcha', "$empty_captcha");
                }
                if (isset($_POST['confidentcaptcha_code']) && !empty($_POST['confidentcaptcha_code'])) {
            $validationData = array (
    		    'api_username'=>$this->options['api_username'],
			    'api_password'=>$this->options['api_password'],
			    'customer_id'=>$this->options['customer_id'],
			    'site_id'=>$this->options['site_id'],
			    'library_version'=>'20120625',
			    'click_coordinates'=>$_POST['confidentcaptcha_click_coordinates'],
			    'code'=>$_POST['confidentcaptcha_code'],
			    'confidentCaptchaID'=>$_POST['confidentcaptcha_captcha_id']
		    );
            $confidentCaptcha_response = confidentCaptcha_check_answer($validationData);
		    if(!$confidentCaptcha_response->is_valid)
		    {
			    //$error = new WP_Error();
                //remove_filter('authenticate', 'check_login', 20, 3);
                $incorrect_captcha = ($this->options['incorrect_response_error'] != '') ? $this->options['incorrect_response_error'] : __('Incorrect CAPTCHA', 'confidentCaptcha');
				$error->add('captcha_error', "<strong>$incorrect_captcha</strong>");
				//return $error;
                //return new WP_Error('captcha_error', "<strong>$incorrect_captcha</strong>");
		    }
                }
                remove_filter('authenticate', 'check_login', 20, 3);
			    return $error;
			}
            $validationData = array (
    		    'api_username'=>$this->options['api_username'],
			    'api_password'=>$this->options['api_password'],
			    'customer_id'=>$this->options['customer_id'],
			    'site_id'=>$this->options['site_id'],
			    'library_version'=>'20120625',
			    'click_coordinates'=>$_POST['confidentcaptcha_click_coordinates'],
			    'code'=>$_POST['confidentcaptcha_code'],
			    'confidentCaptchaID'=>$_POST['confidentcaptcha_captcha_id']
		    );
            $confidentCaptcha_response = confidentCaptcha_check_answer($validationData);
		    if(!$confidentCaptcha_response->is_valid)
		    {
			    $error = new WP_Error();
                remove_filter('authenticate', 'check_login', 20, 3);
                $incorrect_captcha = ($this->options['incorrect_response_error'] != '') ? $this->options['incorrect_response_error'] : __('Incorrect CAPTCHA', 'confidentCaptcha');
				$error->add('captcha_error', "<strong>$incorrect_captcha</strong>");
				//return $error;
                return new WP_Error('captcha_error', "<strong>$incorrect_captcha</strong>");
		    }
            if( version_compare($wp_version,'3','>=') ) { // wp 3.0 +
                if ( is_multisite() ) {
		            if ( 1 == $userdata->spam)
			            return new WP_Error('invalid_username', __('<strong>ERROR</strong>: Your account has been marked as a spammer.'));
		            if ( !is_super_admin( $userdata->ID ) && isset($userdata->primary_blog) ) {
			            $details = get_blog_details( $userdata->primary_blog );
			            if ( is_object( $details ) && $details->spam == 1 )
				        return new WP_Error('blog_suspended', __('Site Suspended.'));
		            }
	            }
		    }
	        $userdata = apply_filters('wp_authenticate_user', $userdata, $password);
		    if ( is_wp_error($userdata) ) {
		     	return $userdata;
		    }
		    if ( !wp_check_password($password, $userdata->user_pass, $userdata->ID) ) {
			    return new WP_Error('incorrect_password', sprintf(__('<strong>ERROR</strong>: Incorrect password. <a href="%s" title="Password Lost and Found">Lost your password</a>?'), site_url('wp-login.php?action=lostpassword', 'login')));
		    }
		    $user =  new WP_User($userdata->ID);
		    return $user;
        }			
        function relative_redirect($location, $comment) {
            if ($this->saved_error != '') {
                $location = substr($location, 0, strpos($location, '#')) .
                    ((strpos($location, "?") === false) ? "?" : "&") .
                    'rcommentid=' . $comment->comment_ID .
                    '&rerror=' . $this->saved_error .
                    '&rchash=' . $this->hash_comment($comment->comment_ID) .
                    '#commentform';
            }
            return $location;
        }
        function saved_comment() {
            if (!is_single() && !is_page())
                return;
            $comment_id = $_REQUEST['rcommentid'];
            $comment_hash = $_REQUEST['rchash'];
            if (empty($comment_id) || empty($comment_hash))
               return;
            if ($comment_hash == $this->hash_comment($comment_id)) {
               $comment = get_comment($comment_id);
               $com = preg_replace('/([\\/\(\)\+\;\'])/e','\'%\'.dechex(ord(\'$1\'))', $comment->comment_content);
               $com = preg_replace('/\\r\\n/m', '\\\n', $com);
               echo "
                <script type='text/javascript'>
                var _confidentCaptcha_wordpress_savedcomment =  '" . $com  ."';
                _confidentCaptcha_wordpress_savedcomment = unescape(_confidentCaptcha_wordpress_savedcomment);
                </script>
                ";
                wp_delete_comment($comment->comment_ID);
            }
        }
        function blog_domain() {
            $uri = parse_url(get_option('siteurl'));
            return $uri['host'];
        }
        function show_settings_link($links, $file) {
            if ($file == plugin_basename($this->path_to_plugin_directory() . '/wp-confidentCaptcha.php')) {
               $settings_title = __('Settings for this Plugin', 'confidentCaptcha');
               $settings = __('Settings', 'confidentCaptcha');
               $settings_link = '<a href="options-general.php?page=confident-captcha/confidentCaptcha.php" title="' . $settings_title . '">' . $settings . '</a>';
               array_unshift($links, $settings_link);
            }
            return $links;
        }
        function add_settings_page() {
            if ($this->environment == Environment::WordPressMU && $this->is_authority())
                add_submenu_page('wpmu-admin.php', 'wp-confidentCaptcha', 'wp-confidentCaptcha', 'manage_options', __FILE__, array(&$this, 'show_settings_page'));
            add_options_page('wp-confidentCaptcha', 'wp-confidentCaptcha', 'manage_options', __FILE__, array(&$this, 'show_settings_page'));
        }
        function show_settings_page() {
            include("settings.php");
        }
        function build_dropdown($name, $keyvalue, $checked_value) {
            echo '<select name="' . $name . '" id="' . $name . '">' . "\n";
            foreach ($keyvalue as $key => $value) {
                $checked = ($value == $checked_value) ? ' selected="selected" ' : '';
                echo '\t <option value="' . $value . '"' . $checked . ">$key</option> \n";
                $checked = NULL;
            }
            echo "</select> \n";
        }
        function capabilities_dropdown() {
            $capabilities = array (
                __('all registered users', 'confidentCaptcha') => 'read',
                __('edit posts', 'confidentCaptcha') => 'edit_posts',
                __('publish posts', 'confidentCaptcha') => 'publish_posts',
                __('moderate comments', 'confidentCaptcha') => 'moderate_comments',
                __('activate plugins', 'confidentCaptcha') => 'activate_plugins'
            );
            $this->build_dropdown('confidentCaptcha_options[minimum_bypass_level]', $capabilities, $this->options['minimum_bypass_level']);
        }
        function icc_dropdown() {
            $codeColor = array (
                __('White', 'confidentCaptcha') => 'White',
                __('Red', 'confidentCaptcha') => 'Red',
                __('Green', 'confidentCaptcha') => 'Green',
                __('Maroon', 'confidentCaptcha') => 'Maroon',
                __('Grey', 'confidentCaptcha') => 'Grey'
            );
            
            $this->build_dropdown('confidentCaptcha_options[image_code_color]', $codeColor, $this->options['image_code_color']);
        }
        function noiseLevel_dropdown() {
            $noiseLevel = array (
                __('10%', 'confidentCaptcha') => '.10',
                __('20%', 'confidentCaptcha') => '.20',
                __('30%', 'confidentCaptcha') => '.30',
                __('40%', 'confidentCaptcha') => '.40',
                __('50%', 'confidentCaptcha') => '.50',
                __('60%', 'confidentCaptcha') => '.60',
                __('70%', 'confidentCaptcha') => '.70',
                __('80%', 'confidentCaptcha') => '.80',
                __('90%', 'confidentCaptcha') => '.90'
            );
            $this->build_dropdown('confidentCaptcha_options[noise_level]', $noiseLevel, $this->options['noise_level']);
        }
        function displayStyle_dropdown() {
            $displayStyle = array (
                __('lightbox', 'confidentCaptcha') => 'lightbox',
                __('flyout', 'confidentCaptcha') => 'flyout'
            );
            $this->build_dropdown('confidentCaptcha_options[display_style]', $displayStyle, $this->options['display_style']);
        }
    }
}
?>
