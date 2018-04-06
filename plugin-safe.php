<?php
/*
Plugin Name: Plugin Safe
Description: If you don’t hide your plugins, Google can see which plugins you are using on your site. This can leave your site vulnerable before Google. There are some plugins that are used in PBN sites that are only used for PBN sites such as 404 to home type plugins for example (just one example of many). This plugin solves this sever potential footprint by hiding every plugin.
Version: 1.0.0
Author: Acutweb
Author URI: http://acutweb.com
*/

if(!defined('ABSPATH'))
{
	exit;
}

define('LICENSE_SECRET_KEY_SAFE', '5aa64b2f18b502.71166222'); 
// This is the URL where API query request will be sent to. This should be the URL of the site where you have installed the main license manager plugin. Get this value from the integration help page.
/** Must add '/' end of url like: http://theprotectorplugin.com/ 
*/
define('LICENSE_PLUGIN_SERVER_URL', 'http://localhost/wordpress/');
define('YOUR_ITEM_PLUGIN_REFERENCE', 'Plugin Safe');

$siteUrl = $_SERVER['HTTP_HOST'];

$siteUrl = str_replace('http://','',$siteUrl);
if (!defined('WPCSB_PLUGIN_URL')) {
    define('WPCSB_PLUGIN_URL', plugin_dir_path( __FILE__ ));
}

define( 'WPCSB_BASENAME', plugin_basename( __FILE__ ) );

register_deactivation_hook( __FILE__, 'wp_csb_deactivation' );
include("include/deactivation.php");


add_option('wpcsb_tre', false);

$wpcsb_tre = get_option('wpcsb_tre');

/**
 * This function makes sure Sociable is able to load the different language files from
 * the i18n subfolder of the Sociable directory
 **/
function wpsecure_init_locale(){
	$WPCSB_PLUGIN_URL;
	load_plugin_textdomain('wp-csb-directory', false, 'i18n');
}
add_filter('init', 'wpsecure_init_locale');

/**
 * Add the WpSecure menu to the Settings menu
 */
 
 add_action('admin_menu', 'wpsecure_admin_menu');
 
function wpsecure_admin_menu() {
	add_menu_page('WP Content Search Block', 'WP Content Search Block', 'manage_options', 'wp-csb-directory', 'wpcsb_submenu', 'dashicons-lock');
	
}

function wpsecure_write_htaccess($tre){
	global $siteUrl;
	$filename = ABSPATH.'/wp-content/.htaccess';
	/* 1. Disable Directory Browsing  - wpcsb */
	
	$ht3 = '# Disable directory browsing - wpcsb'."\r\n";
	$ht3 .= 'RewriteEngine On'."\r\n";
	$ht3 .= 'RewriteCond %{HTTP_USER_AGENT} (googlebot|bingbot) [NC]'."\r\n";
	$ht3 .= 'RewriteRule .* - [R=403,L]'."\r\n";
	$ht3 .= 'deny from all'."\r\n";
	$ht3 .= 'Deny from all'."\r\n";
	$ht3 .= '<Files ~ ".(xml|css|jpe?g|png|gif|js)$">'."\r\n";
	$ht3 .= 'Allow from all'."\r\n";
	$ht3 .= ' </Files>'."\r\n";
	
	$wpsecure_msg = '';
	if (file_exists($filename)) {
		if (is_writable($filename)) {
			
			$stringafileht = file_get_contents($filename);
			if (preg_match("/\bwpcsb\b/i", $stringafileht)){ 
			$tre = false; 
			}

			$fp = fopen($filename, 'a');
			
			if ($tre) fwrite($fp, $ht3."\r\n");
			
			fclose($fp);
		} else { $wpsecure_msg = "The file $filename is not writable"; }
	} else {
		
		// This is the case where file doesn't exist
		
		$fp = fopen($filename, 'w');
		if ($tre) fwrite($fp, $ht3."\r\n");
		fclose($fp);
	}
	return $wpsecure_msg;
}

function wpcsb_submenu() {
	include("include/common_function.php");
	}
/**
 * Plugin Update Checker
 */
$api_url = 'http://acutweb.com/plugin-safe-api/index.php/';
$plugin_slug = basename(dirname(__FILE__));

// Take over the update check
add_filter('pre_set_site_transient_update_plugins', 'check_safe_plugin_update');

function check_safe_plugin_update($checked_data) {
	global $api_url, $plugin_slug;
	//Comment out these two lines during testing.
	if (empty($checked_data->checked))
		return $checked_data;
	
	$args = array(
		'slug' => $plugin_slug,
		'version' => $checked_data->checked[$plugin_slug .'/'. $plugin_slug .'.php'],
	);
	$request_string = array(
			'body' => array(
				'action' => 'basic_check', 
				'request' => serialize($args),
				'api-key' => md5(get_bloginfo('url'))
			),
			'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
		);
	
	// Start checking for an update
	$raw_response = wp_remote_post($api_url, $request_string);
	if (!is_wp_error($raw_response) && ($raw_response['response']['code'] == 200))
		$response = unserialize($raw_response['body']);
	
	if (is_object($response) && !empty($response)) // Feed the update data into WP updater
		$checked_data->response[$plugin_slug .'/'. $plugin_slug .'.php'] = $response;
	
	return $checked_data;
}

// Take over the Plugin info screen
add_filter('plugins_api', 'plugin_safe_api_call', 10, 3);

function plugin_safe_api_call($def, $action, $args) {
	global $plugin_slug, $api_url;
	
	if ($args->slug != $plugin_slug)
		return false;
	
	// Get the current version
	$plugin_info = get_site_transient('update_plugins');
	$current_version = $plugin_info->checked[$plugin_slug .'/'. $plugin_slug .'.php'];
	$args->version = $current_version;
	
	$request_string = array(
			'body' => array(
				'action' => $action, 
				'request' => serialize($args),
				'api-key' => md5(get_bloginfo('url'))
			),
			'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
		);
	
	$request = wp_remote_post($api_url, $request_string);
	if (is_wp_error($request)) {
		$res = new WP_Error('plugins_api_failed', __('An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>'), $request->get_error_message());
	} else {
		$res = unserialize($request['body']);
		if ($res === false)
			$res = new WP_Error('plugins_api_failed', __('An unknown error occurred'), $request['body']);
	}
	return $res;
}
?>