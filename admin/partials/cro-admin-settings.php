<?php
/**
 * Admin settings page
 *
 * @package CRO_Toolkit
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Handle save
if ( isset( $_POST['cro_save_settings'] ) && wp_verify_nonce( $_POST['cro_nonce'], 'cro_save_settings' ) ) {
	update_option( 'cro_enable_analytics', isset( $_POST['enable_analytics'] ) ? 1 : 0 );
	update_option( 'cro_remove_data_on_uninstall', ! empty( $_POST['remove_data_on_uninstall'] ) ? 'yes' : 'no' );
	cro_settings()->set( 'general', 'debug_mode', ! empty( $_POST['debug_mode'] ) );
	cro_settings()->set( 'general', 'blocks_debug_mode', ! empty( $_POST['blocks_debug_mode'] ) );

	// Save Brand Styles
	cro_settings()->set( 'styles', 'primary_color', sanitize_hex_color( $_POST['primary_color'] ?? '#333333' ) ?: '#333333' );
	cro_settings()->set( 'styles', 'secondary_color', sanitize_hex_color( $_POST['secondary_color'] ?? '#555555' ) ?: '#555555' );
	cro_settings()->set( 'styles', 'button_radius', absint( $_POST['button_radius'] ?? 8 ) );
	cro_settings()->set( 'styles', 'border_radius', absint( $_POST['button_radius'] ?? 8 ) );
	cro_settings()->set( 'styles', 'spacing', absint( $_POST['spacing'] ?? 8 ) );
	cro_settings()->set( 'styles', 'font_size_scale', (float) ( $_POST['font_size_scale'] ?? 1 ) );
	cro_settings()->set( 'styles', 'font_family', sanitize_text_field( $_POST['font_family'] ?? 'inherit' ) );
	cro_settings()->set( 'styles', 'animation_speed', sanitize_text_field( $_POST['animation_speed'] ?? 'normal' ) );
	
	echo '<div class="cro-ui-notice cro-ui-toast-placeholder" role="status"><p>' . esc_html__( 'Settings saved.', 'cro-toolkit' ) . '</p></div>';
}
?>

<div class="cro-card">
	<header class="cro-card__header"><h2><?php esc_html_e( 'General', 'cro-toolkit' ); ?></h2></header>
	<div class="cro-card__body">
		<form method="post" id="cro-settings-form">
			<?php wp_nonce_field( 'cro_save_settings', 'cro_nonce' ); ?>

			<div class="cro-field">
				<div class="cro-field__control">
					<label>
						<input type="checkbox" id="enable_analytics" name="enable_analytics" value="1" <?php checked( get_option( 'cro_enable_analytics', true ), 1 ); ?> />
						<?php esc_html_e( 'Enable analytics tracking', 'cro-toolkit' ); ?>
					</label>
				</div>
			</div>

			<div class="cro-field">
				<label class="cro-field__label"><?php esc_html_e( 'Debug Mode', 'cro-toolkit' ); ?></label>
				<div class="cro-field__control">
					<label>
						<input type="checkbox" name="debug_mode" value="1" <?php checked( cro_settings()->get( 'general', 'debug_mode' ) ); ?> />
						<?php esc_html_e( 'Enable debug mode (admin only)', 'cro-toolkit' ); ?>
					</label>
					<p class="cro-help"><?php esc_html_e( 'Shows why campaigns did or didn\'t trigger. Only visible to admins.', 'cro-toolkit' ); ?></p>
				</div>
			</div>

			<div class="cro-field">
				<label class="cro-field__label"><?php esc_html_e( 'Blocks debug', 'cro-toolkit' ); ?></label>
				<div class="cro-field__control">
					<label>
						<input type="checkbox" name="blocks_debug_mode" value="1" <?php checked( cro_settings()->get( 'general', 'blocks_debug_mode' ) ); ?> />
						<?php esc_html_e( 'Enable Blocks debug mode', 'cro-toolkit' ); ?>
					</label>
					<p class="cro-help"><?php esc_html_e( 'Shows a fixed badge on Cart/Checkout block pages and logs settings to the console so you can confirm the extension is loaded.', 'cro-toolkit' ); ?></p>
				</div>
			</div>

			<div class="cro-field">
				<label class="cro-field__label"><?php esc_html_e( 'Uninstall', 'cro-toolkit' ); ?></label>
				<div class="cro-field__control">
					<label>
						<input type="checkbox" name="remove_data_on_uninstall" value="1" <?php checked( get_option( 'cro_remove_data_on_uninstall', 'no' ), 'yes' ); ?> />
						<?php esc_html_e( 'Remove all data when plugin is deleted', 'cro-toolkit' ); ?>
					</label>
					<p class="cro-help"><?php esc_html_e( 'If checked, deleting the plugin will remove campaigns, analytics, A/B tests, options, and transients. Leave unchecked to keep data.', 'cro-toolkit' ); ?></p>
				</div>
			</div>

			<?php submit_button( __( 'Save Settings', 'cro-toolkit' ) ); ?>
		</form>
	</div>
</div>

<div class="cro-card">
	<header class="cro-card__header"><h2><?php esc_html_e( 'Brand Styles', 'cro-toolkit' ); ?></h2></header>
	<div class="cro-card__body">
		<p class="cro-section-desc"><?php esc_html_e( 'Global styles for popups, shipping bar, sticky cart, and trust badges. Override per campaign in the campaign editor.', 'cro-toolkit' ); ?></p>
		<form method="post">
			<?php wp_nonce_field( 'cro_save_settings', 'cro_nonce' ); ?>
			<input type="hidden" name="cro_save_settings" value="1" />

			<div class="cro-field">
				<label class="cro-field__label" for="cro-primary-color"><?php esc_html_e( 'Primary Color', 'cro-toolkit' ); ?></label>
				<div class="cro-field__control">
					<input type="text" id="cro-primary-color" name="primary_color" value="<?php echo esc_attr( cro_settings()->get( 'styles', 'primary_color', '#333333' ) ); ?>" class="cro-color-picker" />
					<p class="cro-help"><?php esc_html_e( 'Buttons and primary accents.', 'cro-toolkit' ); ?></p>
				</div>
			</div>

			<div class="cro-field">
				<label class="cro-field__label" for="cro-secondary-color"><?php esc_html_e( 'Secondary Color', 'cro-toolkit' ); ?></label>
				<div class="cro-field__control">
					<input type="text" id="cro-secondary-color" name="secondary_color" value="<?php echo esc_attr( cro_settings()->get( 'styles', 'secondary_color', '#555555' ) ); ?>" class="cro-color-picker" />
					<p class="cro-help"><?php esc_html_e( 'Secondary text and accents.', 'cro-toolkit' ); ?></p>
				</div>
			</div>

			<div class="cro-field">
				<label class="cro-field__label" for="cro-button-radius"><?php esc_html_e( 'Button Radius', 'cro-toolkit' ); ?></label>
				<div class="cro-field__control">
					<input type="number" id="cro-button-radius" name="button_radius" value="<?php echo esc_attr( (string) ( cro_settings()->get( 'styles', 'button_radius', 8 ) ?: cro_settings()->get( 'styles', 'border_radius', 8 ) ) ); ?>" min="0" max="30" class="small-text" /> px
					<p class="cro-help"><?php esc_html_e( 'Border radius for buttons and pill elements.', 'cro-toolkit' ); ?></p>
				</div>
			</div>

			<div class="cro-field">
				<label class="cro-field__label" for="cro-spacing"><?php esc_html_e( 'Spacing', 'cro-toolkit' ); ?></label>
				<div class="cro-field__control">
					<input type="number" id="cro-spacing" name="spacing" value="<?php echo esc_attr( (string) ( cro_settings()->get( 'styles', 'spacing', 8 ) ) ); ?>" min="2" max="32" class="small-text" /> px
					<p class="cro-help"><?php esc_html_e( 'Base spacing (padding, gaps) for CRO elements.', 'cro-toolkit' ); ?></p>
				</div>
			</div>

			<div class="cro-field">
				<label class="cro-field__label" for="cro-font-size-scale"><?php esc_html_e( 'Font Size Scale', 'cro-toolkit' ); ?></label>
				<div class="cro-field__control">
					<select id="cro-font-size-scale" name="font_size_scale" class="cro-selectwoo" data-placeholder="<?php esc_attr_e( 'Normal (1×)', 'cro-toolkit' ); ?>">
						<option value="0.875" <?php selected( (string) ( cro_settings()->get( 'styles', 'font_size_scale', 1 ) ), '0.875' ); ?>><?php esc_html_e( 'Small (0.875×)', 'cro-toolkit' ); ?></option>
						<option value="1" <?php selected( (string) ( cro_settings()->get( 'styles', 'font_size_scale', 1 ) ), '1' ); ?>><?php esc_html_e( 'Normal (1×)', 'cro-toolkit' ); ?></option>
						<option value="1.125" <?php selected( (string) ( cro_settings()->get( 'styles', 'font_size_scale', 1 ) ), '1.125' ); ?>><?php esc_html_e( 'Large (1.125×)', 'cro-toolkit' ); ?></option>
						<option value="1.25" <?php selected( (string) ( cro_settings()->get( 'styles', 'font_size_scale', 1 ) ), '1.25' ); ?>><?php esc_html_e( 'Extra large (1.25×)', 'cro-toolkit' ); ?></option>
					</select>
					<p class="cro-help"><?php esc_html_e( 'Relative text size across CRO elements.', 'cro-toolkit' ); ?></p>
				</div>
			</div>

			<div class="cro-field">
				<label class="cro-field__label" for="cro-font-family"><?php esc_html_e( 'Font Family', 'cro-toolkit' ); ?></label>
				<div class="cro-field__control">
					<select id="cro-font-family" name="font_family" class="cro-selectwoo" data-placeholder="<?php esc_attr_e( 'Inherit from theme', 'cro-toolkit' ); ?>">
						<option value="inherit" <?php selected( cro_settings()->get( 'styles', 'font_family', 'inherit' ), 'inherit' ); ?>><?php esc_html_e( 'Inherit from theme', 'cro-toolkit' ); ?></option>
						<option value="system" <?php selected( cro_settings()->get( 'styles', 'font_family' ), 'system' ); ?>><?php esc_html_e( 'System fonts', 'cro-toolkit' ); ?></option>
						<option value="arial" <?php selected( cro_settings()->get( 'styles', 'font_family' ), 'arial' ); ?>>Arial</option>
						<option value="georgia" <?php selected( cro_settings()->get( 'styles', 'font_family' ), 'georgia' ); ?>>Georgia</option>
					</select>
				</div>
			</div>

			<div class="cro-field">
				<label class="cro-field__label" for="cro-animation-speed"><?php esc_html_e( 'Animation Speed', 'cro-toolkit' ); ?></label>
				<div class="cro-field__control">
					<select id="cro-animation-speed" name="animation_speed" class="cro-selectwoo" data-placeholder="<?php esc_attr_e( 'Normal (300ms)', 'cro-toolkit' ); ?>">
						<option value="fast" <?php selected( cro_settings()->get( 'styles', 'animation_speed', 'normal' ), 'fast' ); ?>><?php esc_html_e( 'Fast (150ms)', 'cro-toolkit' ); ?></option>
						<option value="normal" <?php selected( cro_settings()->get( 'styles', 'animation_speed', 'normal' ), 'normal' ); ?>><?php esc_html_e( 'Normal (300ms)', 'cro-toolkit' ); ?></option>
						<option value="slow" <?php selected( cro_settings()->get( 'styles', 'animation_speed', 'normal' ), 'slow' ); ?>><?php esc_html_e( 'Slow (500ms)', 'cro-toolkit' ); ?></option>
						<option value="none" <?php selected( cro_settings()->get( 'styles', 'animation_speed', 'normal' ), 'none' ); ?>><?php esc_html_e( 'No animations', 'cro-toolkit' ); ?></option>
					</select>
				</div>
			</div>

			<?php submit_button( __( 'Save Brand Styles', 'cro-toolkit' ) ); ?>
		</form>
	</div>
</div>

<div class="cro-card">
	<header class="cro-card__header"><h2><?php esc_html_e( 'Onboarding', 'cro-toolkit' ); ?></h2></header>
	<div class="cro-card__body">
		<div class="cro-field">
			<label class="cro-field__label"><?php esc_html_e( 'Restart onboarding', 'cro-toolkit' ); ?></label>
			<div class="cro-field__control">
				<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=cro-settings&action=cro_restart_onboarding' ), 'cro_restart_onboarding' ) ); ?>" class="button"><?php esc_html_e( 'Restart onboarding', 'cro-toolkit' ); ?></a>
				<p class="cro-help"><?php esc_html_e( 'Show the setup checklist again after activation.', 'cro-toolkit' ); ?></p>
			</div>
		</div>
	</div>
</div>

<div class="cro-card">
	<header class="cro-card__header"><h2><?php esc_html_e( 'Import / Export', 'cro-toolkit' ); ?></h2></header>
	<div class="cro-card__body">
		<div class="cro-field">
			<label class="cro-field__label"><?php esc_html_e( 'Export', 'cro-toolkit' ); ?></label>
			<div class="cro-field__control">
				<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=cro-settings&action=cro_export' ), 'cro_export' ) ); ?>" class="button"><?php esc_html_e( 'Export Campaigns & Settings', 'cro-toolkit' ); ?></a>
				<p class="cro-help"><?php esc_html_e( 'Download a JSON file with all campaigns and settings.', 'cro-toolkit' ); ?></p>
			</div>
		</div>
		<div class="cro-field">
			<label class="cro-field__label"><?php esc_html_e( 'Import', 'cro-toolkit' ); ?></label>
			<div class="cro-field__control">
				<form method="post" enctype="multipart/form-data">
					<?php wp_nonce_field( 'cro_import', 'cro_import_nonce' ); ?>
					<input type="file" name="import_file" accept=".json" />
					<p class="cro-mt-2">
						<label>
							<input type="checkbox" name="import_settings" value="1" />
							<?php esc_html_e( 'Also import settings (will overwrite current settings)', 'cro-toolkit' ); ?>
						</label>
					</p>
					<button type="submit" name="cro_import" class="button"><?php esc_html_e( 'Import', 'cro-toolkit' ); ?></button>
				</form>
			</div>
		</div>
	</div>
</div>
