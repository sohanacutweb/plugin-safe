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

define('LICENSE_SECRET_KEY', '5aa64b2f18b502.71166222'); 
// This is the URL where API query request will be sent to. This should be the URL of the site where you have installed the main license manager plugin. Get this value from the integration help page.
/** Must add '/' end of url like: http://theprotectorplugin.com/ 
*/
define('LICENSE_SERVER_URL', 'http://localhost/wordpress/');
define('YOUR_ITEM_REFERENCE', 'Plugin Safe');

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
function wpsecure_admin_menu() {
	add_options_page('WP Content Search Block', 'WP Content Search Block', 8, 'wp-csb-directory', 'wpcsb_submenu');
	
	 //add_menu_page( 'WP Content Search Block', 'WP Content Search Block', 'manage_options', 'wp-csb-directory', 'wpcsb_submenu'); 
}
add_action('admin_menu', 'wpsecure_admin_menu');

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
?>