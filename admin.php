<?php
register_activation_hook(__FILE__, 'pti_cool_snow_set_options');
register_deactivation_hook(__FILE__, 'pti_cool_snow_unset_options');

function pti_cool_snow_set_options() {
	add_option('detect_winter', '1');
	add_option('flakes_quantity', '30');
	add_option('flakes_normal_speed', '30');
	add_option('flakes_storm_speed', '300');
}

function pti_cool_snow_unset_options() {
	delete_option('detect_winter');
	delete_option('flakes_quantity');
	delete_option('flakes_normal_speed');
	delete_option('flakes_storm_speed');
}

add_action ('admin_menu', 'pti_cool_snow_modify_plugin');

function pti_cool_snow_modify_plugin() {
	add_options_page('Configure snow speed, flakes and other', 'Snow Configuration', 'manage_options', __FILE__, 'pti_cool_snow_modify_admin');
}

function pti_cool_snow_modify_admin() {
	if (isset($_POST['flakes_submit'])) {
		$detect_winter = $_POST['detect_winter'] ? 1 : 0;
		$ok1 = update_option('detect_winter', (int)$detect_winter);
		$ok2 = update_option('flakes_quantity', (int)trim($_POST['flakes_quantity']));
		$ok3 = update_option('flakes_normal_speed', (int)trim($_POST['flakes_normal_speed']));
		$ok4 = update_option('flakes_storm_speed', (int)trim($_POST['flakes_storm_speed']));
//		if ($ok1 && $ok2 && $ok3 && $ok4) { ?>
			<div id="message" class="updated fade" align="center"><p><strong>Configuration saved</strong></p></div>
		<?php // } else { ?>
			<!--div id="message" class="error fade" align="center"><p><strong>Failed to save configuration</strong></p></div-->
		<?php // }
	}
?>
	<div class="wrap">
		<h2>Snow Configuration</h2>
		<form method="post" action="">
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="detect_winter">Execute only in winter</label></th>
					<td><input type="checkbox" id="detect_winter" name="detect_winter" <?php if (get_option('detect_winter')) echo 'checked="checked"'; ?> /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="flakes_quantity">On page flakes quantity</label></th>
					<td><input type="text" id="flakes_quantity" name="flakes_quantity" value="<?php echo get_option('flakes_quantity'); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="flakes_normal_speed">Flakes speed</label></th>
					<td><input type="text" id="flakes_normal_speed" name="flakes_normal_speed" value="<?php echo get_option('flakes_normal_speed'); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="flakes_storm_speed">Storm speed</label></th>
					<td><input type="text" id="flakes_storm_speed" name="flakes_storm_speed" value="<?php echo get_option('flakes_storm_speed'); ?>" /></td>
				</tr>
				<br />
				<tr valign="top">
					<th scope="row"><label for="flakes_presets">Speed presets</label></th>
					<td>
						<button class="button-secondary" onClick="jQuery('#flakes_normal_speed').val('10');jQuery('#flakes_storm_speed').val('100');return false;">Slow</button>
						<button class="button-secondary" onClick="jQuery('#flakes_normal_speed').val('40');jQuery('#flakes_storm_speed').val('400');return false;">Realistic</button>
						<button class="button-secondary" onClick="jQuery('#flakes_normal_speed').val('80');jQuery('#flakes_storm_speed').val('800');return false;">Fast</button>
					</td>
				</tr>
				<tr valign="top">
					<td colspan="2">
						<input type="submit" name="flakes_submit" class="button-primary" />
						<button class="button-secondary" onClick="jQuery('#detect_winter').attr('checked','checked');jQuery('#flakes_quantity').val('30');jQuery('#flakes_normal_speed').val('30');jQuery('#flakes_storm_speed').val('300');return false;">Reset to dafaults</button>
					</td>
				</tr>
			</table>
		</form>
	</div>
<?php }
?>