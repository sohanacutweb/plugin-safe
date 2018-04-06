<?php
echo '<div class="wrap">';
    echo '<h2>License Management</h2>';
	// 
	 // Check Licence Key Status
    /*** License activate button was clicked ***/
    if (isset($_REQUEST['activate_license'])) {
        $license_key = $_REQUEST['plugin_safe_license_key'];

        // API query parameters
        $api_params = array(
            'slm_action' => 'slm_activate',
            'secret_key' => LICENSE_SECRET_KEY,
            'license_key' => $license_key,
            'registered_domain' => $_SERVER['SERVER_NAME'],
            'item_reference' => urlencode(YOUR_ITEM_PLUGIN_REFERENCE),
        );

        // Send query to the license manager server
        $query = esc_url_raw(add_query_arg($api_params, LICENSE_PLUGIN_SERVER_URL));
        $response = wp_remote_get($query, array('timeout' => 20, 'sslverify' => false));

        // Check for error in the response
        if (is_wp_error($response)){
            echo '<div style="width:100%;color:red;">Unexpected Error! The query returned with an error. If you need help with activating plugin please send us email to support@jpteach.me</div>';
        }

        //var_dump($response);//uncomment it if you want to look at the full response
        
        // License data.
        $license_data = json_decode(wp_remote_retrieve_body($response));
		
        if($license_data->result == 'success'){//Success was returned for the license activation
            
            //Uncomment the followng line to see the message that returned from the license server
            echo '<div style="width:100%;color:green;">The following message was returned from the server: '.$license_data->message.'</div>';
            
            //Save the license key in the options table
            update_option('plugin_safe_license_key', $license_key); 
			update_option('ps_license_key_status','active');
        }
        else{
            //Show error to the user. Probably entered incorrect license key.
            
            //Uncomment the followng line to see the message that returned from the license server
            echo '<div style="width:100%;color:red;">The following message was returned from the server: '.$license_data->message. ' If you need help with activating plugin please send us email to support@jpteach.me </div>';
        }

    }
    /*** End of license activation ***/
    
    /*** License activate button was clicked ***/
    if (isset($_REQUEST['deactivate_license'])) {
        $license_key = $_REQUEST['plugin_safe_license_key'];

        // API query parameters
        $api_params = array(
            'slm_action' => 'slm_deactivate',
            'secret_key' => LICENSE_SECRET_KEY,
            'license_key' => $license_key,
            'registered_domain' => $_SERVER['SERVER_NAME'],
            'item_reference' => urlencode(YOUR_ITEM_PLUGIN_REFERENCE),
        );

        // Send query to the license manager server
        $query = esc_url_raw(add_query_arg($api_params, LICENSE_PLUGIN_SERVER_URL));
        $response = wp_remote_get($query, array('timeout' => 20, 'sslverify' => false));

        // Check for error in the response
        if (is_wp_error($response)){
            echo '<div style="width:100%;color:red;">Unexpected Error! The query returned with an error. If you need help with activating plugin please send us email to support@jpteach.me</div>';
        }

        //var_dump($response);//uncomment it if you want to look at the full response
        
        // License data.
        $license_data = json_decode(wp_remote_retrieve_body($response));
	
        // TODO - Do something with it.
        //var_dump($license_data);//uncomment it to look at the data
        
        if($license_data->result == 'success'){//Success was returned for the license activation
            
            //Uncomment the followng line to see the message that returned from the license server
            echo '<div style="width:100%;color:green;">The following message was returned from the server: '.$license_data->message.'</div>';
            
            //Remove the licensse key from the options table. It will need to be activated again.
            update_option('plugin_safe_license_key', '');
			update_option('wpcsb_license_key_status','inactive');
        }
        else{
            //Show error to the user. Probably entered incorrect license key.
            
            //Uncomment the followng line to see the message that returned from the license server
            echo '<div style="width:100%;color:red;">The following message was returned from the server: '.$license_data->message.' If you need help with activating plugin please send us email to support@jpteach.me </div>';
        }
        
    }
    /*** End of sample license deactivation ***/
    
    ?>
    <p>Please enter the license key for this product to activate it. You were given a license key when you purchased this item.</p>
    <form action="" method="post">
		<?php wp_nonce_field( 'wp-csb-directory', 'plugin_safe_license_key' ); ?>
        <table class="form-table">
            <tr>
                <th style="width:100px;"><label for="plugin_safe_license_key">License Key</label></th>
                <td ><input class="regular-text" type="text" id="plugin_safe_license_key" name="plugin_safe_license_key" required  value="<?php echo get_option('plugin_safe_license_key'); ?>" ></td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" name="activate_license" value="Activate" class="button-primary" />
        </p>
    </form>
    <?php
    
    echo '</div>';