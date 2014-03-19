<h1>Settings file included:</h1>
<div class="wrap">
   <a name="confidentCaptcha"></a>
   <h2><?php _e('Confident CAPTCHA Options', 'confidentCaptcha'); ?></h2>
   <p><?php _e('Confident CAPTCHA is an image based CAPTCHA solution that stops spam and is user friendly.', 'confidentCaptcha'); ?></p>
   
   <form method="post" action="options.php">
      <?php settings_fields('confidentCaptcha_options_group'); ?>

      <h3><?php _e('Authentication', 'confidentCaptcha'); ?></h3>
      <p><?php _e('These keys are required before you are able to do anything else.', 'confidentCaptcha'); ?> <?php _e('You can get the keys', 'confidentCaptcha'); ?> <a href="http://www.confidenttechnologies.com/content/get-confident-captcha-today" target="_blank" title="<?php _e('Get your confidentCaptcha API Keys', 'confidentCaptcha'); ?>"><?php _e('here', 'confidentCaptcha'); ?></a>.</p>
      
      <table class="form-table">
         <tr valign="top">
            <th scope="row"><?php _e('Customer ID', 'confidentCaptcha'); ?></th>
            <td>
               <input type="text" name="confidentCaptcha_options[customer_id]" size="40" value="<?php echo $this->options['customer_id']; ?>" />
            </td>
         </tr>
         <tr valign="top">
            <th scope="row"><?php _e('Site ID', 'confidentCaptcha'); ?></th>
            <td>
               <input type="text" name="confidentCaptcha_options[site_id]" size="40" value="<?php echo $this->options['site_id']; ?>" />
            </td>
         </tr>
         <tr valign="top">
            <th scope="row"><?php _e('API Username', 'confidentCaptcha'); ?></th>
            <td>
               <input type="text" name="confidentCaptcha_options[api_username]" size="40" value="<?php echo $this->options['api_username']; ?>" />
            </td>
         </tr>
         <tr valign="top">
            <th scope="row"><?php _e('API Password', 'confidentCaptcha'); ?></th>
            <td>
               <input type="text" name="confidentCaptcha_options[api_password]" size="40" value="<?php echo $this->options['api_password']; ?>" />
            </td>
         </tr>
      </table>
      
      <h3><?php _e('Comment Options', 'confidentCaptcha'); ?></h3>
      <table class="form-table">
         <tr valign="top">
            <th scope="row"><?php _e('Activation', 'confidentCaptcha'); ?></th>
            <td>
               <input type="checkbox" id ="confidentCaptcha_options[show_in_comments]" name="confidentCaptcha_options[show_in_comments]" value="1" <?php checked('1', $this->options['show_in_comments']); ?> />
               <label for="confidentCaptcha_options[show_in_comments]"><?php _e('Enable for comments form', 'confidentCaptcha'); ?></label>
            </td>
         </tr>
         
         <tr valign="top">
            <th scope="row"><?php _e('Target', 'confidentCaptcha'); ?></th>
            <td>
               <input type="checkbox" id="confidentCaptcha_options[bypass_for_registered_users]" name="confidentCaptcha_options[bypass_for_registered_users]" value="1" <?php checked('1', $this->options['bypass_for_registered_users']); ?> />
               <label for="confidentCaptcha_options[bypass_for_registered_users]"><?php _e('Hide for Registered Users who can', 'confidentCaptcha'); ?></label>
               <?php $this->capabilities_dropdown(); ?>
            </td>
         </tr>

         <tr valign="top">
            <th scope="row"><?php _e('Tab Index', 'confidentCaptcha'); ?></th>
            <td>
               <input type="text" name="confidentCaptcha_options[comments_tab_index]" size="10" value="<?php echo $this->options['comments_tab_index']; ?>" />
            </td>
         </tr>
      </table>
      
      <h3><?php _e('Registration Options', 'confidentCaptcha'); ?></h3>
      <table class="form-table">
         <tr valign="top">
            <th scope="row"><?php _e('Activation', 'confidentCaptcha'); ?></th>
            <td>
               <input type="checkbox" id ="confidentCaptcha_options[show_in_registration]" name="confidentCaptcha_options[show_in_registration]" value="1" <?php checked('1', $this->options['show_in_registration']); ?> />
               <label for="confidentCaptcha_options[show_in_registration]"><?php _e('Enable for registration form', 'confidentCaptcha'); ?></label>
            </td>
         </tr>
         
         <tr valign="top">
            <th scope="row"><?php _e('Tab Index', 'confidentCaptcha'); ?></th>
            <td>
               <input type="text" name="confidentCaptcha_options[registration_tab_index]" size="10" value="<?php echo $this->options['registration_tab_index']; ?>" />
            </td>
         </tr>
         <tr valign="top">
            <th scope="row"><?php _e('Login page', 'confidentCaptcha'); ?></th>
            <td>
               <input type="checkbox" id ="confidentCaptcha_options[show_in_login_page]" name="confidentCaptcha_options[show_in_login_page]" value="1" <?php checked('1', $this->options['show_in_login_page']); ?> />
               <label for="confidentCaptcha_options[show_in_login_page]"><?php _e('Enable for Login', 'confidentCaptcha'); ?></label>
            </td>
         </tr>
         <tr valign="top">
            <th scope="row"><?php _e('Contact Form 7', 'confidentCaptcha'); ?></th>
            <td>
               <input type="checkbox" id ="confidentCaptcha_options[show_in_cf7]" name="confidentCaptcha_options[show_in_cf7]" value="1" <?php checked('1', $this->options['show_in_cf7']); ?> />
               <label for="confidentCaptcha_options[show_in_cf7]"><?php _e('Enable for Contact Form 7', 'confidentCaptcha'); ?></label>
            </td>
         </tr>
         <tr valign="top">
            <th scope="row"><?php _e('Lost Password', 'confidentCaptcha'); ?></th>
            <td>
               <input type="checkbox" id ="confidentCaptcha_options[show_in_lost_password]" name="confidentCaptcha_options[show_in_lost_password]" value="1" <?php checked('1', $this->options['show_in_lost_password']); ?> />
               <label for="confidentCaptcha_options[show_in_lost_password]"><?php _e('Enable for Lost Password', 'confidentCaptcha'); ?></label>
            </td>
         </tr>	 
      </table>
      
      <h3><?php _e('General Options', 'confidentCaptcha'); ?></h3>
      <table class="form-table">
          <tr valign="top">
              <th scope="row"><?php _e('Captcha Color', 'confidentCaptcha'); ?></th>
              <td>
                  <?php $this->cc_dropdown(); ?>
              </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e('Width', 'confidentCaptcha'); ?></th>
            <td>
               <input type="text" name="confidentCaptcha_options[width]" size="4" value="<?php echo $this->options['width']; ?>" />
            </td>
         </tr>
         <tr valign="top">
            <th scope="row"><?php _e('Height', 'confidentCaptcha'); ?></th>
            <td>
               <input type="text" name="confidentCaptcha_options[height]" size="4" value="<?php echo $this->options['height']; ?>" />
            </td>
         </tr>
         <tr valign="top">
            <th scope="row"><?php _e('Captcha Length', 'confidentCaptcha'); ?></th>
            <td>
               <input type="text" name="confidentCaptcha_options[captcha_length]" size="4" value="<?php echo $this->options['captcha_length']; ?>" />
            </td>
         </tr>
         <tr valign="top">
		    <th scope="row"><?php _e('Image Code Color', 'confidentCaptcha'); ?></th>
			<td>
			   <?php $this->icc_dropdown(); ?>
			</td>
         </tr>
		 <tr valign="top">
		    <th scope="row"><?php _e('Noise Level', 'confidentCaptcha'); ?></th>
		    <td>
			   <?php $this->noiseLevel_dropdown(); ?>
			</td>
         </tr>
		 <tr valign="top">
		    <th scope="row"><?php _e('Display Style', 'confidentCaptcha'); ?></th>
		    <td>
			   <?php $this->displayStyle_dropdown(); ?>
			</td>
         </tr>
         <tr valign="top">
             <th scope="row"><?php _e('Show Letters', 'confidentCaptcha'); ?></th>
             <td>
                 <?php $this->show_letters_dropdown(); ?>
             </td>
         </tr>
	     <tr valign="top">
            <th scope="row"><?php _e('Logo Name', 'confidentCaptcha'); ?></th>
            <td>
               <input type="text" name="confidentCaptcha_options[captcha_logo]" size="15" value="<?php echo $this->options['captcha_logo']; ?>" />
            </td>
         </tr>
		 <tr valign="top">
		    <th scope="row"><?php _e('Billboard Name', 'confidentCaptcha'); ?></th>
		    <td>
			   <input type="text" id="confidentCaptcha_options[captcha_billboard]" name="confidentCaptcha_options[captcha_billboard]" size="15" value="<? echo $this->options['captcha_billboard']; ?>" />
			</td>
         </tr>
      </table>

      <h3><?php _e('Advanced Options', 'confidentCaptcha'); ?></h3>
      <table class="form-table">
          <tr valign="top">
              <th scope="row"><?php _e('Failure Policy', 'confidentCaptcha'); ?></th>
              <td>
                  <?php $this->fp_dropdown(); ?>
              </td>
          </tr>
          <tr valign="top">
              <th scope="row"><?php _e('Ajax Verify', 'confidentCaptcha'); ?></th>
              <td>
                  <?php $this->ajax_verify_dropdown(); ?>
              </td>
          </tr>
          <tr valign="top">
              <th scope="row"><?php _e('Max Tries', 'confidentCaptcha'); ?></th>
              <td>
                  <input type="text" name="confidentCaptcha_options[max_tries]" size="4" value="<?php echo $this->options['max_tries']; ?>" />
              </td>
          </tr>
      </table>

      <h3><?php _e('Messages', 'confidentCaptcha'); ?></h3>
      <table class="form-table">
         <tr valign="top">
            <th scope="row"><?php _e('CAPTCHA Title text:', 'confidentCaptcha'); ?></th>
            <td>
               <input type="text" name="confidentCaptcha_options[captcha_description]" size="70" value="<?php echo $this->options['captcha_description']; ?>" /><br/>
               <label for="confidentCaptcha_options[captcha_description]"><?php _e('Enter a text description to display above CAPTCHA solution (Default: none)', 'confidentCaptcha'); ?></label>
            </td>
         </tr>
         <tr valign="top">
            <th scope="row"><?php _e('Confident CAPTCHA Ignored', 'confidentCaptcha'); ?></th>
            <td>
               <input type="text" name="confidentCaptcha_options[no_response_error]" size="70" value="<?php echo $this->options['no_response_error']; ?>" />
            </td>
         </tr>
         <tr valign="top">
            <th scope="row"><?php _e('Incorrect Guess', 'confidentCaptcha'); ?></th>
            <td>
               <input type="text" name="confidentCaptcha_options[incorrect_response_error]" size="70" value="<?php echo $this->options['incorrect_response_error']; ?>" />
            </td>
         </tr>
      </table>

      <p class="submit"><input type="submit" class="button-primary" title="<?php _e('Save Confident CAPTCHA Options') ?>" value="<?php _e('Save Confident CAPTCHA Changes') ?> &raquo;" /></p>
   </form>
   
   <?php do_settings_sections('confidentCaptcha_options_page'); ?>
</div>
