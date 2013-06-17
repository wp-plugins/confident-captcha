<?php
/*
Plugin Name: Confident CAPTCHA
Plugin URI: https://www.confidenttechnologies.com
Description: Integrates Confident CAPTCHA anti-spam solutions with wordpress
Version: 2.5.1
Author: Confident Technologies Inc.
Email: support@confidenttechnologies.com
Author URI: http://www.confidenttechnologies.com/contact
*/

// this is the 'driver' file that instantiates the objects and registers every hook

define('ALLOW_INCLUDE', true);

require_once('confidentCaptcha.php');

$confidentCaptcha = new confidentCaptcha('confidentCaptcha_options');

?>
