<?php
/**
 * Code used when the plugin is removed (not just deactivated but actively deleted through the WordPress Admin).
 */
if( !defined( 'ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') ){
	exit();
} 
delete_option( 'wpcsb_tre' );
$filename = ABSPATH.'/wp-content/.htaccess';
unlink($filename);
?>
