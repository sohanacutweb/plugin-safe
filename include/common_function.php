<?php
 // Check This Plugin Already Active or Not
		$optionValue = get_option('plugin_safe_license_key');
		if(!isset($optionValue) || ($optionValue =='')){
			require_once WPCSB_PLUGIN_URL . '/include/license_key_activation.php';
		}else{
			
		/**
			* Check Key Status
		*/

		$apiurl = LICENSE_PLUGIN_SERVER_URL;
		$fields = array(
		'slm_action' => 'slm_check',
		'secret_key' => LICENSE_SECRET_KEY_SAFE,
		'registered_domain' => $_SERVER['SERVER_NAME'],
		'license_key' => $optionValue,
		);
		
		foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
			rtrim($fields_string, '&');
			$ch = curl_init();
			//set the url, number of POST vars, POST data
			curl_setopt($ch,CURLOPT_URL, $apiurl);
			curl_setopt($ch,CURLOPT_POST, count($fields));
			curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			//execute post
			$result = curl_exec($ch);
			//close connection
			curl_close($ch);
			$arrayData = json_decode($result);	
			if($arrayData->status=='blocked' || $arrayData->status=='expired' || $arrayData->status=='blocked' || $arrayData->status=='pending'){
				remove_plugin_feature();
				define('KEY_STATUS',$arrayData->status);
				require_once WPCSB_PLUGIN_URL . '/include/license_key_activation_status.php';
				update_option('ps_license_key_status','inactive');
			}elseif($arrayData->domainstatus=='removed'){
				remove_plugin_feature();
				require_once WPCSB_PLUGIN_URL . '/include/license_key_domain_status.php';
				update_option('ps_license_key_status','removed');
			} else{
			
			
			
		$msg = "";
		// Check form submission and update options
		if ('wpsecure_submit' == $_POST['wpsecure_submit']) {

			update_option('wpcsb_tre', $_POST['wpcsb_tre']);
			
			$wpcsb_tre = get_option('wpcsb_tre');
			
			$msg = wpsecure_write_htaccess($wpcsb_tre);
		}
	$wpcsb_tre = get_option('wpcsb_tre');	
?>
<div class="wrap" id="sm_div">
    <h2>WP Content disable directory from search engine bot</h2>
<?php
if ($msg) {
	?>
	<div id="message" class="error"><p><strong><?php echo $msg; ?></strong></p>
	</div>
<?php
	}	
?>
    <div style="clear:both";></div> 
</div>

<div id="poststuff" class="metabox-holder has-right-sidebar"> 

<div class="has-sidebar sm-padded" > 
					
		<div id="post-body-content" class="has-sidebar-content"> 

		<div class="meta-box-sortabless"> 
										
		<div id="sm_rebuild" class="postbox">
			<h3 class="hndle">
				<span>Safely disable directory browsing</span>
			</h3>
			<div class="inside">
					<form name="form1" method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>&amp;updated=true">
						<input type="hidden" name="wpsecure_submit" value="wpsecure_submit" />
						<ul>
							<li>
							<label for="wpcsb_tre">
								<input name="wpcsb_tre" type="checkbox" id="wpcsb_tre" value="1" <?php echo $wpcsb_tre?'checked="checked"':''; ?> />
								Disable Directory Browsing of the directory: wp-content/ (Recommended)
							</label>
							</li>
						</ul>
					   <p class="submit"> <input type="submit" value="Save &amp; Write" class="sm_button"/></p>
					</form>
					</div>
				</div>
			</div>
		</div>
</div>
</div>
<?php 
	}
} ?>
<?php
function remove_plugin_feature(){
	delete_option( 'wpcsb_tre' );
	$filename = ABSPATH.'/wp-content/.htaccess';
	unlink($filename);
}

/**
 *Cronjob Register
 *	date: 16_03_2018
 * @added by : Acutweb
 */

add_action( 'licensecronjob', 'delete_all_post_revisions_plugin_safe' );

// This function will run once the 'licensecronjob' is called
function delete_all_post_revisions_plugin_safe() {
	// OUR CODE will here
	// Run CURL for Update Status
	$LiceseoptionValue = get_option('plugin_safe_license_key');
	// Check License Key Status
	if(isset($LiceseoptionValue) and !empty($LiceseoptionValue)){
		$apiurl = LICENSE_PLUGIN_SERVER_URL;
		$fields = array(
		'slm_action' => 'slm_check',
		'secret_key' => LICENSE_SECRET_KEY_SAFE,
		'registered_domain' => $_SERVER['SERVER_NAME'],
		'license_key' => $LiceseoptionValue,
		);
		foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
		rtrim($fields_string, '&');
			$ch = curl_init();
			//set the url, number of POST vars, POST data
			curl_setopt($ch,CURLOPT_URL, $apiurl);
			curl_setopt($ch,CURLOPT_POST, count($fields));
			curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			//execute post
			$result = curl_exec($ch);
			
			//close connection
			curl_close($ch);
			$arrayData12 = json_decode($result);
			if( ($arrayData12->status=='active') and ($arrayData12->domainstatus=='active')){
				update_option('ps_license_key_status','active');
			} else{
				update_option('ps_license_key_status','inactive');
			}
		} else{
			update_option('ps_license_key_status','notactive');
		}
}

add_action( 'init', 'register_daily_revision_delete_event2');
function register_daily_revision_delete_event2() {
	// Make sure this event hasn't been scheduled
	if( !wp_next_scheduled( 'licensecronjob' ) ) {
		// Schedule the event
		wp_schedule_event( time(), 'hourly', 'licensecronjob' );
	} 
}

?>