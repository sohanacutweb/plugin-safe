<?php
function wp_csb_deactivation(){
	delete_option( 'wpcsb_tre' );
	$filename = ABSPATH.'/wp-content/.htaccess';
	unlink($filename);
}
?>