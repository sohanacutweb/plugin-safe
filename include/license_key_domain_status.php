<?php
echo '<div class="wrap">';
    echo '<h2>License Management</h2>';
    ?>
    <p>Now your domain has been removed for license key: <?php echo get_option('plugin_safe_license_key'); ?> .  If you need help with activating plugin help or refund please send us email to support@jpteach.me.</p>	<div>Active Licence Key</div>
    <form action="" method="post">
        <table class="form-table">
            <tr>
                <th style="width:150px;"><label for="plugin_safe_license_key">License Key</label></th>
                <td ><input class="regular-text" readonly type="text" id="plugin_safe_license_key" name="plugin_safe_license_key" required  value="<?php echo get_option('plugin_safe_license_key'); ?>" ></td>
            </tr>
			<tr>
				<th style="width:150px;">License Key Status : </th>
				<td><?php echo ucwords($arrayData->status); ?> </td>
			</tr>
			<tr>
				<th style="width:150px;">Domain Status : </th>
				<td><?php echo ucwords($arrayData->domainstatus); ?> </td>
			</tr>
        </table>
    </form>	<hr>	<div class="active_form">	<?php require_once WPCSB_PLUGIN_URL . '/include/license_key_re_activation.php'; ?>	</div>
    <?php
    
    echo '</div>';